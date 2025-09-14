<?php
/**
 * Plugin Name:       WP Table Booking Manager
 * Description:       Booking tables with capacity management, business hours, notifications, and custom fields.
 * Version:           1.1.0
 * Author:            Your Name
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       wptbm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPTBM_VERSION', '1.1.0' );
define( 'WPTBM_PLUGIN_FILE', __FILE__ );
define( 'WPTBM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPTBM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-plugin.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-cpt.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-admin.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-settings.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-shortcodes.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-ajax.php';
require_once WPTBM_PLUGIN_DIR . 'includes/class-wptbm-notifications.php';

register_activation_hook( __FILE__, function() {
	// Ensure CPTs are registered before flushing.
	WPTBM_CPT::register_post_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

add_action( 'plugins_loaded', function() {
	WPTBM_Plugin::instance();
} );