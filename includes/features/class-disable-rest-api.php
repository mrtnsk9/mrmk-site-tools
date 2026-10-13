<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_REST implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_rest_api',
			'name'            => 'Disable REST API',
			'description'     => 'Disables REST API for unauthorized users. Removes api link from head. Returns 401 error.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'rest_authentication_errors', function( $result ) {
			if ( true === $result || is_wp_error( $result ) ) {
				return $result;
			}

			if ( ! is_user_logged_in() ) {
				return new WP_Error(
					'rest_not_logged_in',
					__( 'You are not currently logged in.' ),
					array( 'status' => 401 )
				);
			}

			return $result;
		});

		// Remove rest api link https://example.site/wp-json/ from head
		remove_action( 'wp_head', 'rest_output_link_wp_head');
	}
}
