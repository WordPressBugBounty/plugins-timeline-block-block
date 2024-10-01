<?php
/**
 * Plugin Name: Timeline Block 
 * Description: Display timeline content on your site. 
 * Version: 1.0.7
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: timeline-block
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

// Constant
define( 'TLGB_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.7' );
define( 'TLGB_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'TLGB_DIR_PATH', plugin_dir_path( __FILE__ ) );

require_once TLGB_DIR_PATH . 'inc/block.php';