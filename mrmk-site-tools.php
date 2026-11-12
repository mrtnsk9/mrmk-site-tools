<?php
/*
Plugin Name: mrmk Site Tools
Plugin URI: https://github.com/mrtnsk9/mrmk-site-tools
Description: A collection of practical WordPress site tools.
Version: 1.0.0
Requires at least: 7.0
Tested up to: 7.1.4
Requires PHP: 8.3
Text Domain: mrmk-st
Author: mrtnsk9
Author URI:  https://github.com/mrtnsk9/
License: GPLv3 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MRMK_ST_PLUGIN_VERSION', '1.0.0' );
define( 'MRMK_ST_PLUGIN_FILE', __FILE__ );
define( 'MRMK_ST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once MRMK_ST_PLUGIN_DIR . 'includes/class-plugin.php';

add_action( 'init', array( 'MRMK_ST_Plugin', 'instance' ), 0 );
