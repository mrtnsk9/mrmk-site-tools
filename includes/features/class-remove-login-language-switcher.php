<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_Login_Language_Switcher implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'remove_login_language_switcher',
			'name'            => 'Remove Login Language Switcher',
			'description'     => 'Removes the language dropdown from the WordPress login page.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'login_display_language_dropdown', '__return_false' );
	}
}
