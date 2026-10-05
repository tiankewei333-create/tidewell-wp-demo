<?php
/**
 * Demo-only behaviour, controlled by the tidewell_demo_mode option.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_is_demo() {
	return 'yes' === get_option( 'tidewell_demo_mode', 'no' );
}

// Playground has no mail server: report success so form submissions complete. Flamingo still stores every submission.
add_filter( 'pre_wp_mail', function ( $return ) {
	return tidewell_is_demo() ? true : $return;
} );
