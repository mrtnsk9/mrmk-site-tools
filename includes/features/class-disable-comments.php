<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Comments implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_comments',
			'name'            => 'Disable Comments',
			'description'     => 'Hides existing comments, blocks new comments, and disables comment feeds. Comments remain stored and accessible in the admin area.',
			'category'        => 'General',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'comments_open', '__return_false', 100, 2 );
		add_filter( 'comments_pre_query', array( __CLASS__, 'hide_comment_queries' ), 100, 2 );
		add_filter( 'comments_array', array( __CLASS__, 'hide_comments' ), 100, 2 );
		add_filter( 'get_comments_number', array( __CLASS__, 'hide_comment_count' ), 100, 2 );
		add_filter( 'feed_links_show_comments_feed', '__return_false', 100 );
		add_filter( 'feed_links_extra_show_post_comments_feed', '__return_false', 100 );
		add_filter( 'wp_headers', array( __CLASS__, 'set_comment_feed_content_type' ), 100, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_comment_feed' ), 1 );
	}

	public static function hide_comment_queries( $comments, $query ) {
		return self::is_public_frontend_request() ? array() : $comments;
	}

	public static function hide_comments( $comments, $post_id ) {
		return self::is_public_frontend_request() ? array() : $comments;
	}

	public static function hide_comment_count( $count, $post_id ) {
		return self::is_public_frontend_request() ? 0 : $count;
	}

	public static function set_comment_feed_content_type( $headers, $wp ) {
		if ( is_comment_feed() ) {
			$headers['Content-Type'] = get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' );
		}

		return $headers;
	}

	public static function disable_comment_feed() {
		if ( ! is_comment_feed() ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		$wp_query->is_feed = false;
		status_header( 404 );
		nocache_headers();
	}

	private static function is_public_frontend_request() {
		return ! is_admin() && ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST );
	}
}
