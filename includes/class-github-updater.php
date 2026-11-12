<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Connects this plugin to stable releases published on GitHub.
 */
class MRMK_ST_GitHub_Updater {
	const REPOSITORY       = 'mrtnsk9/mrmk-site-tools';
	const PLUGIN_SLUG      = 'mrmk-site-tools';
	const PACKAGE_FILENAME = 'mrmk-site-tools.zip';
	const RELEASE_CACHE    = 'mrmk_st_github_release_v1';

	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'add_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 20, 3 );
	}

	public function add_update( $transient ) {
		$plugin_basename = plugin_basename( MRMK_ST_PLUGIN_FILE );
		if ( ! is_object( $transient ) || empty( $transient->checked[ $plugin_basename ] ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release || ! version_compare( MRMK_ST_PLUGIN_VERSION, $release['version'], '<' ) ) {
			return $transient;
		}

		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = array();
		}

		$transient->response[ $plugin_basename ] = (object) array(
			'slug'        => self::PLUGIN_SLUG,
			'plugin'      => $plugin_basename,
			'new_version' => $release['version'],
			'url'         => $release['url'],
			'package'     => $release['package'],
		);

		return $transient;
	}

	public function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || ! is_object( $args ) || empty( $args->slug ) || self::PLUGIN_SLUG !== $args->slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => 'mrmk Site Tools',
			'slug'          => self::PLUGIN_SLUG,
			'version'       => $release['version'],
			'author'        => '<a href="https://github.com/mrtnsk9/">mrtnsk9</a>',
			'homepage'      => 'https://github.com/' . self::REPOSITORY,
			'requires'      => '7.0',
			'tested'        => '7.1.4',
			'requires_php'  => '8.3',
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => esc_html__( 'A collection of practical WordPress site tools.', 'mrmk-st' ),
				'changelog'   => '' !== $release['body']
					? wp_kses_post( nl2br( esc_html( $release['body'] ) ) )
					: esc_html__( 'See the GitHub release for details.', 'mrmk-st' ),
			),
		);
	}

	private function get_latest_release() {
		$cached_release = get_site_transient( self::RELEASE_CACHE );
		if ( is_array( $cached_release ) ) {
			return ! empty( $cached_release['available'] ) ? $cached_release : false;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::REPOSITORY . '/releases/latest',
			array(
				'timeout'     => 8,
				'redirect'    => 2,
				'headers'     => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'mrmk-site-tools/' . MRMK_ST_PLUGIN_VERSION . '; ' . home_url( '/' ),
				),
				'sslverify'   => true,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_site_transient( self::RELEASE_CACHE, array( 'available' => false ), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) ) {
			set_site_transient( self::RELEASE_CACHE, array( 'available' => false ), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$tag = isset( $data['tag_name'] ) && is_string( $data['tag_name'] ) ? $data['tag_name'] : '';
		if ( ! preg_match( '/^v(\d+\.\d+\.\d+)$/', $tag, $matches ) ) {
			set_site_transient( self::RELEASE_CACHE, array( 'available' => false ), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$release_url = isset( $data['html_url'] ) && is_string( $data['html_url'] ) ? esc_url_raw( $data['html_url'] ) : '';
		if ( ! $this->is_github_url( $release_url ) ) {
			set_site_transient( self::RELEASE_CACHE, array( 'available' => false ), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$package_url = '';
		if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				if (
					is_array( $asset ) &&
					isset( $asset['name'], $asset['browser_download_url'] ) &&
					self::PACKAGE_FILENAME === $asset['name'] &&
					is_string( $asset['browser_download_url'] )
				) {
					$package_url = esc_url_raw( $asset['browser_download_url'] );
					break;
				}
			}
		}

		if ( ! $this->is_github_url( $package_url ) ) {
			set_site_transient( self::RELEASE_CACHE, array( 'available' => false ), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$release = array(
			'available' => true,
			'version'   => $matches[1],
			'url'       => $release_url,
			'package'   => $package_url,
			'body'      => isset( $data['body'] ) && is_string( $data['body'] ) ? $data['body'] : '',
		);

		set_site_transient( self::RELEASE_CACHE, $release, 6 * HOUR_IN_SECONDS );
		return $release;
	}

	private function is_github_url( $url ) {
		return is_string( $url ) &&
			'https' === wp_parse_url( $url, PHP_URL_SCHEME ) &&
			'github.com' === wp_parse_url( $url, PHP_URL_HOST );
	}
}
