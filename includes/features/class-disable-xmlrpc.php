<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_XMLRPC implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_xmlrpc',
			'name'            => 'Disable XML-RPC',
			'description'     => 'Disables XML-RPC api. Also removes RSD link from head because it uses XML-RPC.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', '__return_empty_array' );
		
		// Hard exit, just in case
		add_filter('wp_xmlrpc_server_class', function ( $class ) {
			header('HTTP/1.1 403 Forbidden');
			exit('403 Forbidden');
		});

		add_filter( 'wp_headers', function( $headers ) {
			unset( $headers['X-Pingback'] );
			return $headers;
		}, 11 );

		// Remov RSD link from head
		remove_action( 'wp_head', 'rsd_link' );
	}
}
