<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_WP_Shortlink implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'remove_wp_shortlink',
			'name'            => 'Remove WP Shortlink',
			'description'     => 'Removes the WordPress shortlink from the document head and HTTP Link header.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}
}
