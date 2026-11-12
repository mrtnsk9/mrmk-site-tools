<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MRMK_ST_Utility_Report_Interface {
	public static function get_report_page_slug(): string;

	public static function render_report();
}
