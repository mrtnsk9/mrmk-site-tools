<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_Generator_Meta implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'remove_wp_head_generator',
			'name'            => 'Remove WP generator meta tag',
			'description'     => 'Removes "generator" meta tag from site header to prevent WP version leak.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		remove_action('wp_head', 'wp_generator');
	}
}
