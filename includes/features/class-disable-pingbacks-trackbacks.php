<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Pingbacks_Trackbacks implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_pingbacks_trackbacks',
			'name'            => 'Disable Pingbacks and Trackbacks',
			'description'     => 'Blocks incoming and outgoing pingbacks and trackbacks. Existing ping comments are kept.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'pings_open', '__return_false', 100, 2 );
		add_filter( 'xmlrpc_methods', array( __CLASS__, 'remove_pingback_xmlrpc_method' ) );
		add_filter( 'pre_ping', array( __CLASS__, 'prevent_outgoing_pingbacks' ), 10, 3 );
		add_filter( 'trackback_url', array( __CLASS__, 'remove_trackback_url' ), 100 );
		add_filter( 'bloginfo_url', array( __CLASS__, 'remove_pingback_url' ), 100, 2 );
		add_filter( 'wp_headers', array( __CLASS__, 'remove_pingback_header' ), 100, 2 );

		remove_action( 'do_all_pings', 'do_all_pingbacks', 10 );
		remove_action( 'do_all_pings', 'do_all_trackbacks', 10 );
	}

	public static function remove_pingback_xmlrpc_method( $methods ) {
		unset( $methods['pingback.ping'] );
		return $methods;
	}

	public static function prevent_outgoing_pingbacks( &$post_links, &$pung, $post_id ) {
		$post_links = array();
	}

	public static function remove_trackback_url( $url ) {
		return '';
	}

	public static function remove_pingback_url( $url, $show ) {
		return 'pingback_url' === $show ? '' : $url;
	}

	public static function remove_pingback_header( $headers, $wp ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
}
