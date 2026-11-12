<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Utility_Media_Audit implements MRMK_ST_Utility_Interface, MRMK_ST_Utility_Report_Interface {
	const THRESHOLD_BYTES = 512 * 1024;
	const REPORT_PAGE_SLUG = 'mrmk-site-tools-media-audit';
	const RESULTS_PER_PAGE = 100;

	public static function is_available(): bool {
		return true;
	}

	public static function get_metadata() {
		return array(
			'slug'            => 'media_audit',
			'name'            => __( 'Media audit', 'mrmk-st' ),
			'description'     => __( 'Reports files larger than 512 KB and summarizes media library and uploads folder storage. This audit does not change or delete files.', 'mrmk-st' ),
			'button_label'    => __( 'Run media audit', 'mrmk-st' ),
			'confirmation'    => __( 'Scan the uploads folder? No files will be changed.', 'mrmk-st' ),
			'success_message' => __( 'Media audit completed.', 'mrmk-st' ),
			'failure_message' => __( 'The media audit report could not be prepared. Please try again.', 'mrmk-st' ),
		);
	}

	public static function get_report_page_slug(): string {
		return self::REPORT_PAGE_SLUG;
	}

	public static function run(): bool {
		$user_id = get_current_user_id();
		if ( 0 === $user_id ) {
			return false;
		}

		$transient_key = self::get_transient_key( $user_id );
		delete_transient( $transient_key );

		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
			return false;
		}

		$uploads_report = self::scan_uploads_directory( $uploads['basedir'] );
		$media_count = self::get_media_count();
		if ( false === $uploads_report || false === $media_count ) {
			return false;
		}

		$uploads_report['large_files'] = self::map_files_to_media_items( $uploads_report['large_files'] );
		$free_bytes = function_exists( 'disk_free_space' )
			? disk_free_space( $uploads['basedir'] )
			: false;
		usort(
			$uploads_report['large_files'],
			static function ( $left, $right ) {
				$size_order = $right['bytes'] <=> $left['bytes'];
				return 0 !== $size_order ? $size_order : strnatcasecmp( $left['path'], $right['path'] );
			}
		);

		$report = array_merge(
			$uploads_report,
			array(
				'media_count'  => $media_count,
				'free_bytes'   => $free_bytes,
				'completed_at' => time(),
			)
		);
		return set_transient( $transient_key, $report, HOUR_IN_SECONDS );
	}

	public static function render_report() {
		$report = get_transient( self::get_transient_key( get_current_user_id() ) );
		$report_available = is_array( $report ) &&
			array_key_exists( 'free_bytes', $report ) &&
			( false === $report['free_bytes'] || is_numeric( $report['free_bytes'] ) ) &&
			isset(
				$report['media_count'],
				$report['completed_at'],
				$report['file_count'],
				$report['total_bytes'],
				$report['unreadable_count'],
				$report['symlink_count'],
				$report['large_files']
			) &&
			is_array( $report['large_files'] );
		$threshold_label = size_format( self::THRESHOLD_BYTES );
		$settings_page_url = add_query_arg(
			'page',
			'mrmk-site-tools',
			admin_url( 'options-general.php' )
		);
		$current_page = isset( $_GET['audit-page'] ) && is_string( $_GET['audit-page'] )
			? max( 1, absint( wp_unslash( $_GET['audit-page'] ) ) )
			: 1;
		$large_files = $report_available ? $report['large_files'] : array();
		$total_pages = max( 1, (int) ceil( count( $large_files ) / self::RESULTS_PER_PAGE ) );
		$current_page = min( $current_page, $total_pages );
		$first_result = 0 < count( $large_files ) ? ( $current_page - 1 ) * self::RESULTS_PER_PAGE + 1 : 0;
		$visible_files = array_slice( $large_files, ( $current_page - 1 ) * self::RESULTS_PER_PAGE, self::RESULTS_PER_PAGE );
		$previous_url = add_query_arg(
			array(
				'page'       => self::REPORT_PAGE_SLUG,
				'audit-page' => max( 1, $current_page - 1 ),
			),
			admin_url( 'admin.php' )
		);
		$next_url = add_query_arg(
			array(
				'page'       => self::REPORT_PAGE_SLUG,
				'audit-page' => min( $total_pages, $current_page + 1 ),
			),
			admin_url( 'admin.php' )
		);

		require MRMK_ST_PLUGIN_DIR . 'includes/admin/utilities/views/media-audit-report.php';
	}

	private static function get_media_count() {
		$statuses = wp_count_posts( 'attachment' );
		if ( ! is_object( $statuses ) ) {
			return false;
		}

		$media_count = 0;
		foreach ( get_object_vars( $statuses ) as $status => $count ) {
			if ( 'trash' !== $status ) {
				$media_count += absint( $count );
			}
		}

		return $media_count;
	}

	private static function map_files_to_media_items( $large_files ) {
		if ( empty( $large_files ) ) {
			return $large_files;
		}

		$unmatched_paths = array_fill_keys(
			array_map(
				static function ( $file ) {
					return self::normalize_relative_path( $file['path'] );
				},
				$large_files
			),
			true
		);
		$media_links = array();
		$offset = 0;
		$batch_size = 200;

		do {
			$attachment_ids = get_posts(
				array(
					'post_type'              => 'attachment',
					'post_status'            => array( 'inherit', 'private' ),
					'posts_per_page'         => $batch_size,
					'offset'                 => $offset,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false,
				)
			);

			foreach ( $attachment_ids as $attachment_id ) {
				$attachment_id = absint( $attachment_id );
				if ( 0 === $attachment_id ) {
					continue;
				}

				$attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
				if ( is_string( $attached_file ) && '' !== $attached_file ) {
					self::add_media_file_link( $media_links, $unmatched_paths, $attached_file, $attachment_id );
				}

				$metadata = wp_get_attachment_metadata( $attachment_id );
				if ( ! is_array( $metadata ) || empty( $metadata['file'] ) || ! is_string( $metadata['file'] ) ) {
					continue;
				}

				$metadata_file = self::normalize_relative_path( $metadata['file'] );
				self::add_media_file_link( $media_links, $unmatched_paths, $metadata_file, $attachment_id );
				$metadata_directory = dirname( $metadata_file );
				if ( '.' === $metadata_directory ) {
					$metadata_directory = '';
				}

				if ( ! empty( $metadata['original_image'] ) && is_string( $metadata['original_image'] ) ) {
					$original_image = '' === $metadata_directory
						? $metadata['original_image']
						: $metadata_directory . '/' . $metadata['original_image'];
					self::add_media_file_link( $media_links, $unmatched_paths, $original_image, $attachment_id );
				}

				if ( empty( $metadata['sizes'] ) || ! is_array( $metadata['sizes'] ) ) {
					continue;
				}

				foreach ( $metadata['sizes'] as $size ) {
					if ( ! is_array( $size ) || empty( $size['file'] ) || ! is_string( $size['file'] ) ) {
						continue;
					}

					$size_file = '' === $metadata_directory
						? $size['file']
						: $metadata_directory . '/' . $size['file'];
					self::add_media_file_link( $media_links, $unmatched_paths, $size_file, $attachment_id );
				}

				if ( empty( $unmatched_paths ) ) {
					break;
				}
			}

			$batch_count = count( $attachment_ids );
			$offset += $batch_count;
		} while ( $batch_count === $batch_size && ! empty( $unmatched_paths ) );

		foreach ( $large_files as &$file ) {
			$normalized_path = self::normalize_relative_path( $file['path'] );
			$file['attachment_id'] = isset( $media_links[ $normalized_path ] )
				? $media_links[ $normalized_path ]
				: 0;
		}
		unset( $file );

		return $large_files;
	}

	private static function add_media_file_link( &$media_links, &$unmatched_paths, $path, $attachment_id ) {
		$path = self::normalize_relative_path( $path );
		if ( isset( $unmatched_paths[ $path ] ) ) {
			$media_links[ $path ] = $attachment_id;
			unset( $unmatched_paths[ $path ] );
		}
	}

	private static function normalize_relative_path( $path ) {
		return ltrim( str_replace( '\\', '/', $path ), '/' );
	}

	private static function scan_uploads_directory( $uploads_directory ) {
		if ( ! is_dir( $uploads_directory ) || ! is_readable( $uploads_directory ) ) {
			return false;
		}

		$base_directory = rtrim( $uploads_directory, DIRECTORY_SEPARATOR );
		if ( '' === $base_directory ) {
			$base_directory = DIRECTORY_SEPARATOR;
		}

		$report = array(
			'file_count'       => 0,
			'total_bytes'      => 0,
			'unreadable_count' => 0,
			'symlink_count'    => 0,
			'large_files'      => array(),
		);
		$directories = array( $base_directory );

		while ( ! empty( $directories ) ) {
			$directory = array_pop( $directories );
			if ( ! is_readable( $directory ) ) {
				++$report['unreadable_count'];
				continue;
			}

			$entries = scandir( $directory );
			if ( false === $entries ) {
				++$report['unreadable_count'];
				continue;
			}

			foreach ( $entries as $entry ) {
				if ( '.' === $entry || '..' === $entry ) {
					continue;
				}

				$file_path = $directory . DIRECTORY_SEPARATOR . $entry;
				if ( is_link( $file_path ) ) {
					++$report['symlink_count'];
					continue;
				}

				if ( is_dir( $file_path ) ) {
					$directories[] = $file_path;
					continue;
				}

				if ( ! is_file( $file_path ) ) {
					continue;
				}

				++$report['file_count'];
				if ( ! is_readable( $file_path ) ) {
					++$report['unreadable_count'];
					continue;
				}

				$file_size = filesize( $file_path );
				if ( false === $file_size ) {
					++$report['unreadable_count'];
					continue;
				}

				$report['total_bytes'] += $file_size;
				if ( $file_size > self::THRESHOLD_BYTES ) {
					$relative_path = ltrim( substr( $file_path, strlen( $base_directory ) ), DIRECTORY_SEPARATOR );
					$report['large_files'][] = array(
						'path'          => $relative_path,
						'bytes'         => $file_size,
						'attachment_id' => 0,
					);
				}
			}
		}

		return $report;
	}

	private static function get_transient_key( $user_id ) {
		return 'mrmk_st_media_audit_v4_' . absint( $user_id );
	}
}
