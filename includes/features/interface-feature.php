<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MRMK_ST_Feature_Interface {
	// Return this feature's settings-page metadata.
	public static function get_metadata();

	// Start this feature. It may register hooks or initialize other behavior.
	public static function boot();
}
