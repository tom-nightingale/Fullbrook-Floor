<?php

/**
Plugin Name: Multi Step for Contact Form 7 (Lite)
Plugin URI: https://ninjateam.org/contact-form-7-multi-step/
Description: Break your long form into user-friendly steps.
Version: 2.6.2
Author: NinjaTeam
Author URI: http://ninjateam.org
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if (!defined('WPCF7_AUTOP')) {
// define('WPCF7_AUTOP', false);
// }
define( 'CF7MLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CF7MLS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CF7MLS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CF7MLS_NTA_VERSION', '2.6.2' );

// language load text domain
require_once CF7MLS_PLUGIN_DIR . '/inc/I18n.php';
// CF7DB
require_once CF7MLS_PLUGIN_DIR . '/inc/cf7db.php';
// admin
require_once CF7MLS_PLUGIN_DIR . '/inc/admin/init.php';
require_once CF7MLS_PLUGIN_DIR . '/inc/admin/settings.php';
require_once CF7MLS_PLUGIN_DIR . '/inc/admin/review.php';
require_once CF7MLS_PLUGIN_DIR . '/inc/admin/dashboard-widget.php';
// frontend
require_once CF7MLS_PLUGIN_DIR . '/inc/frontend/init.php';
require_once CF7MLS_PLUGIN_DIR . '/inc/frontend/validation.php';

