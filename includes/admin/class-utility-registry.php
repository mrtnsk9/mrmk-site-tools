<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MRMK_ST_PLUGIN_DIR . 'includes/admin/utilities/interface-utility.php';

class MRMK_ST_Utility_Registry {
	public static function all() {
		static $utilities = null;

		if ( null !== $utilities ) {
			return $utilities;
		}

		$utilities = array();
		$utility_files = (array) glob( MRMK_ST_PLUGIN_DIR . 'includes/admin/utilities/class-*.php' );
		sort( $utility_files, SORT_STRING );

		$declared_classes = get_declared_classes();
		foreach ( $utility_files as $utility_file ) {
			require_once $utility_file;
		}

		$utility_classes = array_diff( get_declared_classes(), $declared_classes );
		foreach ( $utility_classes as $class ) {
			if ( ! is_subclass_of( $class, 'MRMK_ST_Utility_Interface' ) ) {
				continue;
			}

			if ( ! call_user_func( array( $class, 'is_available' ) ) ) {
				continue;
			}

			$metadata = call_user_func( array( $class, 'get_metadata' ) );
			$required_fields = array( 'slug', 'name', 'description', 'button_label', 'confirmation', 'success_message', 'failure_message' );
			$valid_metadata = is_array( $metadata );
			if ( $valid_metadata ) {
				foreach ( $required_fields as $field ) {
					if ( ! isset( $metadata[ $field ] ) || ! is_string( $metadata[ $field ] ) || '' === trim( $metadata[ $field ] ) ) {
						$valid_metadata = false;
						break;
					}
				}
			}

			if ( ! $valid_metadata || ! is_callable( array( $class, 'run' ) ) ) {
				trigger_error(
					sprintf( 'The site utility "%s" has invalid metadata or no callable run() method.', $class ),
					E_USER_WARNING
				);
				continue;
			}

			$slug = sanitize_key( $metadata['slug'] );
			if ( '' === $slug ) {
				trigger_error(
					sprintf( 'The site utility "%s" has an invalid slug.', $class ),
					E_USER_WARNING
				);
				continue;
			}

			if ( isset( $utilities[ $slug ] ) ) {
				trigger_error(
					sprintf( 'The site utility slug "%s" is already registered.', $slug ),
					E_USER_WARNING
				);
				continue;
			}

			$metadata['slug']  = $slug;
			$metadata['class'] = $class;
			$utilities[ $slug ] = $metadata;
		}

		return $utilities;
	}
}
