<?php
/// @cond private
/**
 * Plugin Name:     WPX Cron Manager Light
 * Plugin URI:      https://wpxtre.me
 * Description:     Manage registered cron event
 * Version:         1.0.6
 * Author:          wpXtreme, Inc.
 * Author URI:      https://wpxtre.me
 * Text Domain:     wpx-cron-manager
 * Domain Path:     localization
 *
 * WPX PHP Min: 5.2.4
 * WPX WP Min: 3.9
 * WPX MySQL Min: 5.0
 * WPX wpXtreme Min: 1.4.0
 *
 */
/// @endcond

// Avoid directly access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// wpXtreme kickstart logic
require_once( trailingslashit( dirname( __FILE__ ) ) . 'wp_kickstart.php' );

// Engage this WPX plugin - If this is an extension of another WPX plugin,
// just add the main class name of the WPX plugin to extend as fourth parameter string
wpxtreme_wp_kickstart( __FILE__, 'wpx-cron-manager_000056', 'WPXCronManager', 'wpx-cronmanager.php' );
