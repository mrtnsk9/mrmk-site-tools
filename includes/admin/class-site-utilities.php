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
		add_action( 'admin_menu', array( $this, 'register_report_pages' ) );
	}

	public function register_report_pages() {
		foreach ( $this->utilities as $utility ) {
			$utility_class = $utility['class'];
			if ( ! is_subclass_of( $utility_class, 'MRMK_ST_Utility_Report_Interface' ) ) {
				continue;
			}

			// A null parent keeps report pages directly accessible without adding menu items.
			$page_hook = add_submenu_page(
				null,
				$utility['name'],
				$utility['name'],
				'manage_options',
				call_user_func( array( $utility_class, 'get_report_page_slug' ) ),
				array( $this, 'render_report_page' )
			);
			if ( $page_hook ) {
				add_action( 'load-' . $page_hook, array( $this, 'set_report_page_title' ) );
			}
		}
	}

	public function set_report_page_title() {
		global $title;

		$page_slug = isset( $_GET['page'] ) && is_string( $_GET['page'] )
			? sanitize_key( wp_unslash( $_GET['page'] ) )
			: '';

		foreach ( $this->utilities as $utility ) {
			$utility_class = $utility['class'];
			if (
				is_subclass_of( $utility_class, 'MRMK_ST_Utility_Report_Interface' ) &&
				$page_slug === call_user_func( array( $utility_class, 'get_report_page_slug' ) )
			) {
				$title = $utility['name'];
				return;
			}
		}
	}

	public function render_report_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You are not allowed to view utility reports.', 'mrmk-st' ),
				'',
				array( 'response' => 403 )
			);
		}

		$page_slug = isset( $_GET['page'] ) && is_string( $_GET['page'] )
			? sanitize_key( wp_unslash( $_GET['page'] ) )
			: '';

		foreach ( $this->utilities as $utility ) {
			$utility_class = $utility['class'];
			if (
				is_subclass_of( $utility_class, 'MRMK_ST_Utility_Report_Interface' ) &&
				$page_slug === call_user_func( array( $utility_class, 'get_report_page_slug' ) )
			) {
				call_user_func( array( $utility_class, 'render_report' ) );
				return;
			}
		}

		wp_die(
			esc_html__( 'The requested utility report is not available.', 'mrmk-st' ),
			'',
			array( 'response' => 404 )
		);
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
		$utility_class = $this->utilities[ $slug ]['class'];
		$report_page = $completed && is_subclass_of( $utility_class, 'MRMK_ST_Utility_Report_Interface' );
		$redirect_args = array();

		if ( $completed ) {
			$redirect_args['page'] = $report_page
				? call_user_func( array( $utility_class, 'get_report_page_slug' ) )
				: 'mrmk-site-tools';
			$redirect_args['utility-completed'] = $slug;
		} else {
			$redirect_args['page'] = 'mrmk-site-tools';
			$redirect_args['utility-failed'] = $slug;
		}

		$redirect_url = add_query_arg(
			$redirect_args,
			admin_url( $report_page ? 'admin.php' : 'options-general.php' )
		);
		$redirect_anchor = $report_page ? '#mrmk-st-utility-report' : '#mrmk-st-utilities';
		wp_safe_redirect( $redirect_url . $redirect_anchor );
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
