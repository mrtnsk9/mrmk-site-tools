<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Browser_Check implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_browser_happy_check',
			'name'            => 'Disable Browser check in Dashboard',
			'description'     => 'Disables "Browse Happy" check. This helps to speed up Dashboard loading by blocking browser checker HTTP request.',
			'category'        => 'Dashboard',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		if ( !is_admin() ) return;

		add_filter( 'pre_site_transient_browser_' . md5( $_SERVER['HTTP_USER_AGENT'] ?? '' ), '__return_empty_array' );
	}
}
