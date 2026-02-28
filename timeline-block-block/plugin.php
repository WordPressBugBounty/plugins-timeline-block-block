<?php

/**
 * Plugin Name: Timeline Block
 * Description: Display timeline content on your site. 
 * Version: 1.3.7
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: timeline-block
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'tlgb_fs' ) ) {
    tlgb_fs()->set_basename( false, __FILE__ );
} else {
    // Constant
    define( 'TLGB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.3.7' ) );
    define( 'TLGB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'TLGB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'TLGB_HAS_FREE', 'timeline-block-block/plugin.php' === plugin_basename( __FILE__ ) );
    define( 'TLGB_HAS_PRO', 'timeline-block-block-pro/plugin.php' === plugin_basename( __FILE__ ) );
    // define('DISALLOW_FILE_EDIT', true);
    if ( !function_exists( 'tlgb_fs' ) ) {
        // ... Freemius integration snippet ...
        function tlgb_fs() {
            global $tlgb_fs;
            if ( !isset( $tlgb_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
                if ( TLGB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( TLGB_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $tlgbConfig = [
                    'id'                  => '17342',
                    'slug'                => 'timeline-block-block',
                    'premium_slug'        => 'timeline-block-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_624005a9d0c56ff46db6602f5f730',
                    'is_premium'          => false,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => [
                        'slug'       => 'edit.php?post_type=timeline_block',
                        'first-path' => "edit.php?post_type=timeline_block&page=tlgb-dashboard#/welcome",
                        'support'    => false,
                    ],
                ];
                $tlgb_fs = ( TLGB_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $tlgbConfig ) : fs_lite_dynamic_init( $tlgbConfig ) );
            }
            return $tlgb_fs;
        }

        // Init Freemius.
        tlgb_fs();
        // Signal that SDK was initiated.
        do_action( 'tlgb_fs_loaded' );
    }
    // Initialized The Plugin
    require_once TLGB_DIR_PATH . 'includes/class-tlgb-main.php';
    new TLGBTimeline();
}