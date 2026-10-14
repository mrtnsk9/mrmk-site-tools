<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_PHP_Check implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_php_version_check',
			'name'            => 'Disable PHP version check in Dashboard',
			'description'     => 'Disables server PHP version check in Dashboard by blocking HTTP request. Improves load time.',
			'category'        => 'Dashboard',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'current_screen', array(__CLASS__, 'add_filter_in_dash_screen'));
	}

	public static function add_filter_in_dash_screen() {
		if ( !is_admin() ) return;

		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->id ) {
			add_filter( 'pre_site_transient_php_check_' . md5( PHP_VERSION ), '__return_empty_array' );
		}
	}
}
