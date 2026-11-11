<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Font_Library implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_font_library',
			'name'            => 'Disable WP Font Library',
			'description'     => 'Disables the Font Library interface and REST API, and stops its saved font faces from loading. Existing font data remains stored.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'register_post_type_args', array( __CLASS__, 'disable_font_post_type_apis' ), 10, 2 );
		add_filter( 'rest_endpoints', array( __CLASS__, 'remove_font_library_endpoints' ) );
		add_filter( 'wp_theme_json_data_user', array( __CLASS__, 'disable_user_font_faces' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_font_library_menu' ), 99 );
		add_action( 'admin_init', array( __CLASS__, 'block_font_library_admin_page' ), 0 );
	}

	public static function disable_font_post_type_apis( $args, $post_type ) {
		if ( in_array( $post_type, array( 'wp_font_family', 'wp_font_face' ), true ) ) {
			$args['show_ui']      = false;
			$args['show_in_rest'] = false;
		}

		return $args;
	}

	public static function remove_font_library_endpoints( $endpoints ) {
		foreach ( array_keys( $endpoints ) as $route ) {
			if (
				0 === strpos( $route, '/wp/v2/font-collections' ) ||
				0 === strpos( $route, '/wp/v2/font-families' )
			) {
				unset( $endpoints[ $route ] );
			}
		}

		return $endpoints;
	}

	public static function disable_user_font_faces( $theme_json ) {
		if ( ! $theme_json instanceof WP_Theme_JSON_Data ) {
			return $theme_json;
		}

		$data = $theme_json->get_data();
		if ( empty( $data['settings']['typography']['fontFamilies'] ) ) {
			return $theme_json;
		}

		self::remove_font_faces( $data['settings']['typography']['fontFamilies'] );

		return new WP_Theme_JSON_Data( $data, 'custom' );
	}

	public static function remove_font_library_menu() {
		remove_submenu_page( 'themes.php', 'font-library.php' );
	}

	public static function block_font_library_admin_page() {
		$page   = isset( $_GET['page'] ) && is_string( $_GET['page'] ) ? $_GET['page'] : '';
		$script = isset( $_SERVER['PHP_SELF'] ) && is_string( $_SERVER['PHP_SELF'] ) ? basename( $_SERVER['PHP_SELF'] ) : '';

		if (
			! in_array( $page, array( 'font-library', 'font-library-wp-admin' ), true ) &&
			'font-library.php' !== $script
		) {
			return;
		}

		wp_die(
			esc_html__( 'The WordPress Font Library has been disabled.', 'mrmk-st' ),
			'',
			array( 'response' => 404 )
		);
	}

	private static function remove_font_faces( &$definitions ) {
		foreach ( $definitions as &$definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

			unset( $definition['fontFace'] );
			self::remove_font_faces( $definition );
		}

		unset( $definition );
	}
}
