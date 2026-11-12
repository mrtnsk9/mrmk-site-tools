<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Translation_Auto_Updates implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_auto_translation_updates',
			'name'            => 'Disable Auto updates - Translations',
			'description'     => 'Disables automatic updates for WordPress translation files. Translation updates can still be installed manually.',
			'category'        => 'Updates',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'auto_update_translation', '__return_false' );
		add_filter( 'async_update_translation', '__return_false' );
	}
}
