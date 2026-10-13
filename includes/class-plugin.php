<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRMK_ST_PLUGIN_DIR . 'includes/class-feature-registry.php';
require_once MRMK_ST_PLUGIN_DIR . 'includes/admin/class-settings-page.php';

class MRMK_ST_Plugin {
	const OPTION_NAME = 'mrmk_st_enabled_features';

	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	private function __construct() {
		if ( is_admin() ) {
			new MRMK_ST_Settings_Page();
			add_filter(
				'plugin_action_links_' . plugin_basename( MRMK_ST_PLUGIN_FILE ),
				array( $this, 'add_settings_link' )
			);
		}

		$this->load_enabled_features();
	}

	// Load feature modules that are enabled in the plugin settings or by default.
	public function load_enabled_features() {
		$features = MRMK_ST_Feature_Registry::all();

		foreach ( $features as $slug => $feature ) {
			if ( ! $this->is_feature_enabled( $slug, $feature ) ) {
				continue;
			}

			if ( is_callable( array( $feature['class'], 'boot' ) ) ) {
				call_user_func( array( $feature['class'], 'boot' ) );
			}
		}
	}

	public function is_feature_enabled( $slug, $feature ) {
		$settings = get_option( self::OPTION_NAME, array() );

		if ( is_array( $settings ) && array_key_exists( $slug, $settings ) ) {
			return ! empty( $settings[ $slug ] );
		}

        // Maybe on by default
		return ! empty( $feature['default_enabled'] );
	}

	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'options-general.php?page=mrmk-site-tools' ) ),
			'Settings'
		);

		array_unshift( $links, $settings_link );
		return $links;
	}
}
