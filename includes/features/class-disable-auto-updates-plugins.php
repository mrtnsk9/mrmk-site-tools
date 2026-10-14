<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Plugin_Auto_Updates implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_auto_plugin_updates',
			'name'            => 'Disable Auto updates - Plugins',
			'description'     => 'Disables automatic updates for all plugins.',
			'category'        => 'Updates',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'auto_update_plugin', '__return_false' );
	}
}
