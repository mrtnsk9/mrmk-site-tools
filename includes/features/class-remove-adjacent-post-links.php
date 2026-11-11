<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_Adjacent_Post_Links implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'remove_adjacent_post_links',
			'name'            => 'Remove Adjacent-Post Links',
			'description'     => 'Removes previous and next post relation links from the document head.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		self::remove_adjacent_post_links();
		add_action( 'wp', array( __CLASS__, 'remove_adjacent_post_links' ), PHP_INT_MAX );
	}

	public static function remove_adjacent_post_links() {
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	}
}
