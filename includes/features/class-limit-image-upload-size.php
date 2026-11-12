<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Feature_Limit_Image_Upload_Size implements MRMK_ST_Feature_Interface {
	public static function get_metadata() {
		return array(
			'slug'            => 'limit_image_upload_size',
			'name'            => 'Limit Image Upload Size',
			'description'     => 'Sets a maximum file size for image uploads. Other file types are not limited by this feature.',
			'category'        => 'General',
			'default_enabled' => false,
			'settings'        => array(
				'max_image_upload_size_kb' => array(
					'label'       => 'Maximum image size (KB)',
					'type'        => 'number',
					'min'         => 1,
					'max'         => intdiv( PHP_INT_MAX, 1024 ),
					'default'     => 100,
					'description' => 'Images larger than this are rejected. Videos and other file types are unaffected.',
				),
			),
		);
	}

	public static function boot() {
		add_filter( 'wp_handle_upload_prefilter', array( __CLASS__, 'limit_image_upload' ) );
		add_filter( 'wp_handle_sideload_prefilter', array( __CLASS__, 'limit_image_upload' ) );
	}

	public static function limit_image_upload( $file ) {
		if (
			! is_array( $file ) ||
			! empty( $file['error'] ) ||
			empty( $file['tmp_name'] ) ||
			empty( $file['name'] ) ||
			! isset( $file['size'] ) ||
			! is_numeric( $file['size'] )
		) {
			return $file;
		}

		$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return $file;
		}

		$limit_kb = self::get_maximum_image_size_kb();
		if ( (int) $file['size'] <= $limit_kb * 1024 ) {
			return $file;
		}

		$file['error'] = sprintf(
			/* translators: %s: Maximum image upload size in kilobytes. */
			__( 'This image exceeds the maximum upload size of %s KB.', 'mrmk-st' ),
			number_format_i18n( $limit_kb )
		);

		return $file;
	}

	private static function get_maximum_image_size_kb() {
		$options = get_option( MRMK_ST_Plugin::OPTION_NAME, array() );
		if (
			! is_array( $options ) ||
			! isset( $options['max_image_upload_size_kb'] ) ||
			! is_scalar( $options['max_image_upload_size_kb'] )
		) {
			return 100;
		}

		return max( 1, min( intdiv( PHP_INT_MAX, 1024 ), absint( $options['max_image_upload_size_kb'] ) ) );
	}
}
