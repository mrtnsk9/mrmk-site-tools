<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Restrict_Login_To_Administrators implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'restrict_login_to_administrators',
			'name'            => 'Restrict Login to Administrators',
			'description'     => 'Rejects logins from users without the administrator role and logs out their existing sessions.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'authenticate', array( __CLASS__, 'restrict_login' ), 1000, 3 );
		add_action( 'init', array( __CLASS__, 'logout_non_administrators' ), 1 );
	}

	public static function restrict_login( $user, $username, $password ) {
		if ( ! $user instanceof WP_User || self::is_administrator( $user ) ) {
			return $user;
		}

		return new WP_Error(
			'mrmk_st_administrator_login_required',
			__( 'Only administrators can log in.', 'mrmk-st' )
		);
	}

	public static function logout_non_administrators() {
		if ( ( defined( 'WP_CLI' ) && WP_CLI ) || ! is_user_logged_in() ) {
			return;
		}

		if ( ! self::is_administrator( wp_get_current_user() ) ) {
			wp_logout();
		}
	}

	private static function is_administrator( $user ) {
		return in_array( 'administrator', (array) $user->roles, true ) || is_super_admin( $user->ID );
	}
}
