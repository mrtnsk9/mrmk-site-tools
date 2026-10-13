<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRMK_ST_PLUGIN_DIR . 'includes/features/interface-feature.php';

class MRMK_ST_Feature_Registry {
	
	// Find features
	public static function all() {
		static $features = null;

		if ( null !== $features ) {
			return $features;
		}

		$features = array();
		$feature_files = array_merge(
			(array) glob( MRMK_ST_PLUGIN_DIR . 'includes/features/class-*.php' ),
			(array) glob( MRMK_ST_PLUGIN_DIR . 'includes/features/*/class-*.php' )
		);
		sort( $feature_files, SORT_STRING );

		foreach ( $feature_files as $feature_file ) {
			require_once $feature_file;
		}

		foreach ( get_declared_classes() as $class ) {
			if ( ! is_subclass_of( $class, 'MRMK_ST_Feature_Interface' ) ) {
				continue;
			}

			$metadata = call_user_func( array( $class, 'get_metadata' ) );
			if ( ! is_array( $metadata ) || empty( $metadata['slug'] ) || empty( $metadata['name'] ) ) {
				continue;
			}

			$slug = sanitize_key( $metadata['slug'] );
			if ( '' === $slug || isset( $features[ $slug ] ) ) {
				continue;
			}

			$metadata['slug'] = $slug;
			$metadata['description'] = isset( $metadata['description'] ) ? $metadata['description'] : '';
			$metadata['category'] = isset( $metadata['category'] ) ? $metadata['category'] : __( 'General', 'mrmk-st' );
			$metadata['default_enabled'] = ! empty( $metadata['default_enabled'] );
			$metadata['class'] = $class;

			$features[ $slug ] = $metadata;
		}

		return $features;
	}

	public static function defaults() {
		$defaults = array();

		foreach ( self::all() as $slug => $feature ) {
			$defaults[ $slug ] = $feature['default_enabled'] ? 1 : 0;
		}

		return $defaults;
	}
}
