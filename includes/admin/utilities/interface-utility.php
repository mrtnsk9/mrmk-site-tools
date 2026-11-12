<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MRMK_ST_Utility_Interface {
	public static function is_available(): bool;

	public static function get_metadata();

	public static function run(): bool;
}
