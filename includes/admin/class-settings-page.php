<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Settings_Page {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_settings_page() {
		add_options_page(
			__( 'mrmk Site Tools', 'mrmk-st' ),
			__( 'mrmk Site Tools', 'mrmk-st' ),
			'manage_options',
			'mrmk-site-tools',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'mrmk_st_settings',
			MRMK_ST_Plugin::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_features' ),
				'default'           => MRMK_ST_Feature_Registry::defaults(),
			)
		);
	}

	public function sanitize_features( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		foreach ( MRMK_ST_Feature_Registry::all() as $slug => $feature ) {
			$clean[ $slug ] = empty( $input[ $slug ] ) ? 0 : 1;

			if ( empty( $feature['settings'] ) || ! is_array( $feature['settings'] ) ) {
				continue;
			}

			foreach ( $feature['settings'] as $setting_name => $setting ) {
				if ( ! is_array( $setting ) || ! isset( $setting['type'] ) || 'number' !== $setting['type'] ) {
					continue;
				}

				$min     = isset( $setting['min'] ) ? absint( $setting['min'] ) : 0;
				$max     = isset( $setting['max'] ) ? absint( $setting['max'] ) : PHP_INT_MAX;
				$default = isset( $setting['default'] ) ? absint( $setting['default'] ) : $min;
				$value   = isset( $input[ $setting_name ] ) && is_scalar( $input[ $setting_name ] )
					? absint( $input[ $setting_name ] )
					: $default;

				$clean[ $setting_name ] = max( $min, min( $max, $value ) );
			}
		}

		return $clean;
	}

	public function enqueue_assets( $hook_suffix ) {
		if ( 'settings_page_mrmk-site-tools' !== $hook_suffix ) {
			return;
		}

		$plugin_url = plugin_dir_url( MRMK_ST_PLUGIN_FILE );
		wp_enqueue_style(
			'mrmk-st-admin',
			$plugin_url . 'assets/css/admin.css',
			array(),
			MRMK_ST_PLUGIN_VERSION
		);
		wp_enqueue_script(
			'mrmk-st-admin',
			$plugin_url . 'assets/js/admin.js',
			array(),
			MRMK_ST_PLUGIN_VERSION,
			true
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$features = MRMK_ST_Feature_Registry::all();
		$plugin   = MRMK_ST_Plugin::instance();

		require MRMK_ST_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
	}
}
