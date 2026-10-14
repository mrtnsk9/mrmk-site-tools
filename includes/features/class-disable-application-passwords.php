<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_App_Passwords implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_app_passwords',
			'name'            => 'Disable Application Passwords',
			'description'     => 'Disables WP "Application Passwords" feature. These can be created in user account.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'wp_is_application_passwords_available', '__return_false', 999 );
	}
}
