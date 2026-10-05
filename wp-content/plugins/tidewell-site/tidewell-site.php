<?php
/**
 * Plugin Name: Tidewell Site
 * Description: Site-specific features for the Tidewell Cleaning Co. demo: portfolio, local SEO schema, performance switches, business settings and demo content setup.
 * Version: 1.0.0
 * Author: Robin
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: tidewell
 */

defined( 'ABSPATH' ) || exit;

define( 'TIDEWELL_VERSION', '1.0.0' );
define( 'TIDEWELL_DIR', plugin_dir_path( __FILE__ ) );
define( 'TIDEWELL_URL', plugin_dir_url( __FILE__ ) );

require TIDEWELL_DIR . 'includes/data.php';
require TIDEWELL_DIR . 'includes/performance.php';
require TIDEWELL_DIR . 'includes/portfolio.php';
require TIDEWELL_DIR . 'includes/shortcodes.php';
require TIDEWELL_DIR . 'includes/schema.php';
require TIDEWELL_DIR . 'includes/demo.php';
require TIDEWELL_DIR . 'setup/setup.php';
require TIDEWELL_DIR . 'setup/elementor.php';
require TIDEWELL_DIR . 'setup/pages.php';

if ( is_admin() ) {
	require TIDEWELL_DIR . 'includes/admin.php';
}

register_activation_hook( __FILE__, function () {
	tidewell_register_portfolio();
	flush_rewrite_rules();
} );
