<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Emojis implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_emojis',
			'name'            => 'Disable WP Emoji support',
			'description'     => 'Prevents WP emoji script, style loading. Helps to improve performance.',
			'category'        => 'Performance',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		// Front end
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// TinyMCE emoji plugin
		add_filter( 'tiny_mce_plugins', function ( $plugins ) {
			if ( is_array( $plugins ) ) {
				return array_diff( $plugins, array( 'wpemoji' ) );
			} else {
				return array();
			}
		} );

		// Embeds
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );

		// Feeds
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

		// Emails
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Admin env
		add_action( 'admin_init', array( __CLASS__, 'on_admin_init' ) );
	}

	public static function on_admin_init() {
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
}
