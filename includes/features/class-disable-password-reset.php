<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Disable_Password_Reset implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'disable_password_reset',
			'name'            => 'Disable Password Reset',
			'description'     => 'Hide password recovery links and block the public password reset flow.',
			'category'        => 'Security',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_filter( 'allow_password_reset', '__return_false' );
		add_filter( 'lost_password_html_link', '__return_false' );
		add_action( 'login_init', array( __CLASS__, 'redirect_reset_requests' ) );
	}

	public static function redirect_reset_requests() {
		// Redirect password recovery and reset forms to the login screen
		$action = '';

		if ( isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ) {
			$action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
		}

		$blocked_actions = [
			'lostpassword',
			'retrievepassword',
			'rp',
			'resetpass',
		];

		if ( in_array( $action, $blocked_actions, true ) ) {
			wp_safe_redirect( wp_login_url(), 302 );
			exit;
		}
	}
}
