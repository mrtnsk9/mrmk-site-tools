<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Feed_Links implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_feed_links',
			'name'            => 'Disable Feed Links',
			'description'     => 'Removes feed discovery links and returns a 404 for feed URLs.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		add_filter( 'wp_headers', array( __CLASS__, 'set_feed_content_type' ), 100, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_feed' ), 1 );
	}

	public static function set_feed_content_type( $headers, $wp ) {
		if ( is_feed() ) {
			$headers['Content-Type'] = get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' );
		}

		return $headers;
	}

	public static function disable_feed() {
		if ( ! is_feed() ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		$wp_query->is_feed = false;
		status_header( 404 );
		nocache_headers();
	}
}
