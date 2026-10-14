<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Theme_Auto_Updates implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_auto_theme_updates',
			'name'            => 'Disable Auto updates - Themes',
			'description'     => 'Disables automatic updates for all themes.',
			'category'        => 'Updates',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'auto_update_theme', '__return_false' );
	}
}
