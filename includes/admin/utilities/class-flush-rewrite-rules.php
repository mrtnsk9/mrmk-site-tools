<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MRMK_ST_Utility_Flush_Rewrite_Rules implements MRMK_ST_Utility_Interface {
	public static function is_available(): bool {
		return true;
	}

	public static function get_metadata() {
		return array(
			'slug'            => 'flush_rewrite_rules',
			'name'            => __( 'Flush rewrite rules', 'mrmk-st' ),
			'description'     => __( 'Refreshes WordPress rewrite rules without rewriting .htaccess or other server configuration.', 'mrmk-st' ),
			'button_label'    => __( 'Flush rewrite rules', 'mrmk-st' ),
			'confirmation'    => __( 'Refresh WordPress rewrite rules now? This will not update .htaccess or server configuration.', 'mrmk-st' ),
			'success_message' => __( 'WordPress rewrite rules were refreshed.', 'mrmk-st' ),
			'failure_message' => __( 'WordPress rewrite rules could not be refreshed.', 'mrmk-st' ),
		);
	}

	public static function run(): bool {
		flush_rewrite_rules( false );

		return true;
	}
}
