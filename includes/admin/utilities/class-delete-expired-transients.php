<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Utility_Delete_Expired_Transients implements MRMK_ST_Utility_Interface {
	public static function is_available(): bool {
		return true;
	}

	public static function get_metadata() {
		return array(
			'slug'            => 'delete_expired_transients',
			'name'            => __( 'Delete expired transients', 'mrmk-st' ),
			'description'     => __( 'Removes expired transients stored in the database. Active and non-expiring transients are kept, and the external object cache is not flushed.', 'mrmk-st' ),
			'button_label'    => __( 'Delete expired transients', 'mrmk-st' ),
			'confirmation'    => __( 'Delete expired transients only? Active transients will be kept.', 'mrmk-st' ),
			'success_message' => __( 'Expired transient cleanup ran. Active transients and external object-cache entries were not flushed.', 'mrmk-st' ),
			'failure_message' => __( 'Expired transient cleanup could not be completed.', 'mrmk-st' ),
		);
	}

	public static function run(): bool {
		delete_expired_transients( true );

		return true;
	}
}
