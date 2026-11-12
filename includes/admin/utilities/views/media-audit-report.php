<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="mrmk-st-utility-report" class="wrap mrmk-st-media-audit-report">
	<h1><?php echo esc_html__( 'Media audit', 'mrmk-st' ); ?></h1>
	<p><?php echo esc_html__( 'This report scans files in the uploads folder, including generated image sizes. It does not change or delete files.', 'mrmk-st' ); ?></p>
	<p><a href="<?php echo esc_url( $settings_page_url ); ?>"><?php echo esc_html__( 'Back to mrmk Site Tools', 'mrmk-st' ); ?></a></p>

	<?php if ( ! $report_available ) : ?>
		<div class="notice notice-info inline">
			<p><?php echo esc_html__( 'No current report is available. Run Media audit from Site Utilities to generate one.', 'mrmk-st' ); ?></p>
		</div>
	<?php else : ?>
		<div class="notice notice-success inline">
			<p><?php echo esc_html__( 'Media audit completed.', 'mrmk-st' ); ?></p>
			<p>
				<?php
				printf(
					esc_html__( 'Scan finished: %s', 'mrmk-st' ),
					esc_html(
						wp_date(
							get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
							$report['completed_at']
						)
					)
				);
				?>
			</p>
		</div>

		<div class="mrmk-st-media-audit-summary">
			<div>
				<h2><?php echo esc_html__( 'Media library items', 'mrmk-st' ); ?></h2>
				<p><?php echo esc_html( number_format_i18n( $report['media_count'] ) ); ?></p>
			</div>
			<div>
				<h2><?php echo esc_html__( 'Files in uploads', 'mrmk-st' ); ?></h2>
				<p><?php echo esc_html( number_format_i18n( $report['file_count'] ) ); ?></p>
			</div>
			<div>
				<h2><?php echo esc_html__( 'Uploads folder size', 'mrmk-st' ); ?></h2>
				<p><?php echo esc_html( size_format( $report['total_bytes'] ) ); ?></p>
			</div>
			<div>
				<h2><?php echo esc_html__( 'Files over 512 KB', 'mrmk-st' ); ?></h2>
				<p><?php echo esc_html( number_format_i18n( count( $large_files ) ) ); ?></p>
			</div>
			<div>
				<h2><?php echo esc_html__( 'Free space on uploads filesystem', 'mrmk-st' ); ?></h2>
				<p>
					<?php if ( false === $report['free_bytes'] ) : ?>
						<?php echo esc_html__( 'Unavailable', 'mrmk-st' ); ?>
					<?php else : ?>
						<?php echo esc_html( size_format( $report['free_bytes'] ) ); ?>
					<?php endif; ?>
				</p>
			</div>
		</div>
		<p class="description"><?php echo esc_html__( 'Filesystem free space may differ from your hosting account quota.', 'mrmk-st' ); ?></p>

		<?php if ( 0 < $report['unreadable_count'] ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php
					printf(
						esc_html__( 'The total size may be incomplete: %s files or folders could not be read.', 'mrmk-st' ),
						esc_html( number_format_i18n( $report['unreadable_count'] ) )
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<?php if ( 0 < $report['symlink_count'] ) : ?>
			<p>
				<?php
				printf(
					esc_html__( '%s symbolic links were skipped and their targets were not scanned.', 'mrmk-st' ),
					esc_html( number_format_i18n( $report['symlink_count'] ) )
				);
				?>
			</p>
		<?php endif; ?>

		<h2><?php echo esc_html__( 'Files larger than 512 KB', 'mrmk-st' ); ?></h2>
		<?php if ( empty( $large_files ) ) : ?>
			<p><?php echo esc_html__( 'No uploads-folder files exceed 512 KB.', 'mrmk-st' ); ?></p>
		<?php else : ?>
			<p>
				<?php
				printf(
					esc_html__( 'Showing %1$s–%2$s of %3$s files.', 'mrmk-st' ),
					esc_html( number_format_i18n( $first_result ) ),
					esc_html( number_format_i18n( $first_result + count( $visible_files ) - 1 ) ),
					esc_html( number_format_i18n( count( $large_files ) ) )
				);
				?>
			</p>
			<div class="mrmk-st-media-audit-report__table">
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php echo esc_html__( 'Uploads path', 'mrmk-st' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Size', 'mrmk-st' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $visible_files as $file ) : ?>
							<tr>
								<td>
									<?php $edit_link = 0 < $file['attachment_id'] ? get_edit_post_link( $file['attachment_id'] ) : false; ?>
									<?php if ( $edit_link ) : ?>
										<a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $file['path'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $file['path'] ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( size_format( $file['bytes'], 2 ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if ( 1 < $total_pages ) : ?>
				<nav class="mrmk-st-media-audit-pagination" aria-label="<?php echo esc_attr__( 'Media audit pages', 'mrmk-st' ); ?>">
					<?php if ( 1 < $current_page ) : ?>
						<a class="button" href="<?php echo esc_url( $previous_url ); ?>"><?php echo esc_html__( 'Previous', 'mrmk-st' ); ?></a>
					<?php endif; ?>
					<span>
						<?php
						printf(
							esc_html__( 'Page %1$s of %2$s', 'mrmk-st' ),
							esc_html( number_format_i18n( $current_page ) ),
							esc_html( number_format_i18n( $total_pages ) )
						);
						?>
					</span>
					<?php if ( $current_page < $total_pages ) : ?>
						<a class="button" href="<?php echo esc_url( $next_url ); ?>"><?php echo esc_html__( 'Next', 'mrmk-st' ); ?></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
</div>
