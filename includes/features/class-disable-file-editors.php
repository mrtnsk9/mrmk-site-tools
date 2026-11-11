<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_File_Editors implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_file_editors',
			'name'            => 'Disable Theme and Plugin File Editors',
			'description'     => 'Disables access to the WordPress Theme File Editor and Plugin File Editor. Installing and updating themes and plugins remain available.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}

		add_filter( 'map_meta_cap', array( __CLASS__, 'deny_file_editor_capabilities' ), 10, 4 );
		add_action( 'admin_menu', array( __CLASS__, 'remove_file_editor_menus' ), 200 );
		add_action( 'network_admin_menu', array( __CLASS__, 'remove_file_editor_menus' ), 200 );
	}

	public static function deny_file_editor_capabilities( $caps, $cap, $user_id, $args ) {
		if ( in_array( $cap, array( 'edit_themes', 'edit_plugins' ), true ) ) {
			return array( 'do_not_allow' );
		}

		return $caps;
	}

	public static function remove_file_editor_menus() {
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
	}
}
