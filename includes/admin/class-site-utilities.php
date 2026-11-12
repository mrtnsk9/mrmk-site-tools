<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRMK_ST_PLUGIN_DIR . 'includes/admin/class-utility-registry.php';

class MRMK_ST_Site_Utilities {
	private $utilities = array();

	public function __construct() {
		$this->utilities = MRMK_ST_Utility_Registry::all();
		add_action( 'admin_post_mrmk_st_run_utility', array( $this, 'handle_action' ) );
	}

	public function handle_action() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You are not allowed to run site utilities.', 'mrmk-st' ),
				'',
				array( 'response' => 403 )
			);
		}

		$slug = isset( $_POST['utility'] ) && is_string( $_POST['utility'] )
			? sanitize_key( wp_unslash( $_POST['utility'] ) )
			: '';

		if ( '' === $slug || ! isset( $this->utilities[ $slug ] ) ) {
			wp_die(
				esc_html__( 'The requested site utility is not available.', 'mrmk-st' ),
				'',
				array( 'response' => 400 )
			);
		}

		check_admin_referer( 'mrmk_st_utility_' . $slug );
		$completed = call_user_func( array( $this->utilities[ $slug ]['class'], 'run' ) );
		$redirect_args = array(
			'page' => 'mrmk-site-tools',
		);

		if ( $completed ) {
			$redirect_args['utility-completed'] = $slug;
		} else {
			$redirect_args['utility-failed'] = $slug;
		}

		$redirect_url = add_query_arg(
			$redirect_args,
			admin_url( 'options-general.php' )
		);
		wp_safe_redirect( $redirect_url . '#mrmk-st-utilities' );
		exit;
	}

	public function render() {
		$utilities = $this->utilities;
		$completed_slug = isset( $_GET['utility-completed'] ) && is_string( $_GET['utility-completed'] )
			? sanitize_key( wp_unslash( $_GET['utility-completed'] ) )
			: '';
		$failed_slug = isset( $_GET['utility-failed'] ) && is_string( $_GET['utility-failed'] )
			? sanitize_key( wp_unslash( $_GET['utility-failed'] ) )
			: '';
		$completed_message = isset( $utilities[ $completed_slug ] )
			? $utilities[ $completed_slug ]['success_message']
			: '';
		$failed_message = isset( $utilities[ $failed_slug ] )
			? $utilities[ $failed_slug ]['failure_message']
			: '';

		require MRMK_ST_PLUGIN_DIR . 'includes/admin/views/site-utilities.php';
	}
}
