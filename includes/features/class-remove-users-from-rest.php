<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_Users_From_REST implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'limit_public_user_enumeration',
			'name'            => 'Remove Users from REST',
			'description'     => 'Blocks public access to REST API user listings and individual user endpoints. Admin user management remains available.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'block_rest_user_listing' ), 10, 3 );
		add_filter( 'rest_index', array( __CLASS__, 'remove_user_routes_from_index' ), 10, 2 );
		add_filter( 'rest_namespace_index', array( __CLASS__, 'remove_user_routes_from_index' ), 10, 2 );
	}

	public static function block_rest_user_listing( $result, $server, $request ) {
		$route = untrailingslashit( $request->get_route() );

		if (
			null !== $result ||
			'GET' !== $request->get_method() ||
			! preg_match( '#^/wp/v2/users(?:/\d+)?$#', $route ) ||
			current_user_can( 'list_users' )
		) {
			return $result;
		}

		return new WP_Error(
			'rest_user_listing_disabled',
			__( 'User endpoints are not available.', 'mrmk-st' ),
			array( 'status' => 404 )
		);
	}

	public static function remove_user_routes_from_index( $response, $request ) {
		if ( ! $response instanceof WP_REST_Response ) {
			return $response;
		}

		$data = $response->get_data();
		if ( empty( $data['routes'] ) || ! is_array( $data['routes'] ) ) {
			return $response;
		}

		foreach ( array_keys( $data['routes'] ) as $route ) {
			if ( preg_match( '#^/wp/v2/users(?:/|$)#', $route ) ) {
				unset( $data['routes'][ $route ] );
			}
		}

		$response->set_data( $data );

		return $response;
	}
}
