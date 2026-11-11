<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Set_Dashboard_As_WP_Logo_Link implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'set_dashboard_as_wp_logo_link',
			'name'            => 'Set Dashboard as WP Logo Link',
			'description'     => 'Makes the WordPress logo open the Dashboard and removes Get Involved, Support, and Feedback from its admin-bar submenu.',
			'category'        => 'Dashboard',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'wp_before_admin_bar_render', array( __CLASS__, 'customize_admin_bar' ) );
	}

	public static function customize_admin_bar() {
		global $wp_admin_bar;

		if ( ! is_object( $wp_admin_bar ) ) {
			return;
		}

		$wp_logo = $wp_admin_bar->get_node( 'wp-logo' );
		if ( $wp_logo ) {
			$wp_logo->href = self_admin_url( 'index.php' );
			$wp_admin_bar->add_node( $wp_logo );
		}

		foreach ( array( 'contribute', 'support-forums', 'feedback', 'wporg', 'documentation', 'learn' ) as $node_id ) {
			$wp_admin_bar->remove_node( $node_id );
		}
	}
}
