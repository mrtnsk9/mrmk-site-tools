<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Remove_WP_News_Dashboard_Widget implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'remove_wp_news_dash_wdg',
			'name'            => 'Remove WP News dashboard widget',
			'description'     => 'Removed "WordPress Events and News" widget from Dashboard.',
			'category'        => 'Dashboard',
			'default_enabled' => false,
		);
	}

	public static function boot() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_widget' ) );
	}

	public static function remove_widget() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}
}
