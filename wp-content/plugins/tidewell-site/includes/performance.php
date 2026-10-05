<?php
/**
 * Performance switches.
 *
 * "baseline"  – how many sites look before a maintenance pass: original JPEGs at full size,
 *               Google Fonts from the CDN, Font Awesome icon font, every plugin's CSS/JS on every page.
 * "optimized" – WebP subsizes with srcset, self-hosted variable font, inline SVG icons, leaner Elementor
 *               markup, plugin assets only where they are used, prioritised hero image.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_perf_mode() {
	return 'baseline' === get_option( 'tidewell_perf_mode', 'optimized' ) ? 'baseline' : 'optimized';
}

function tidewell_is_optimized() {
	return 'optimized' === tidewell_perf_mode();
}

function tidewell_image_size( $context ) {
	if ( ! tidewell_is_optimized() ) {
		return 'full';
	}
	return [
		'card'     => 'medium_large',
		'content'  => 'medium_large',
		'hero'     => 'tw_wide',
		'lightbox' => 'tw_wide',
	][ $context ] ?? 'medium_large';
}

add_action( 'after_setup_theme', function () {
	add_image_size( 'tw_wide', 960, 0 );
} );

// Generate WebP subsizes for uploaded JPEG/PNG images; originals are kept untouched.
add_filter( 'image_editor_output_format', function ( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

add_filter( 'wp_editor_set_quality', function ( $quality, $mime ) {
	return 'image/webp' === $mime ? 78 : $quality;
}, 10, 2 );

/**
 * Switch the site between modes. Elementor experiments and fonts are stored as options;
 * pages are rebuilt so image widgets pick the matching image size.
 */
function tidewell_apply_perf_mode( $mode, $rebuild = true ) {
	$mode      = 'baseline' === $mode ? 'baseline' : 'optimized';
	$optimized = 'optimized' === $mode;
	update_option( 'tidewell_perf_mode', $mode );
	update_option( 'elementor_experiment-e_font_icon_svg', $optimized ? 'active' : 'inactive' );
	update_option( 'elementor_experiment-e_optimized_markup', $optimized ? 'active' : 'inactive' );
	update_option( 'elementor_google_font', $optimized ? '0' : '1' );
	update_option( 'elementor_font_display', 'swap' );
	if ( $rebuild && function_exists( 'tidewell_build_pages' ) ) {
		tidewell_build_pages();
	}
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

function tidewell_page_uses( $keys ) {
	if ( ! is_page() ) {
		return false;
	}
	$id = get_queried_object_id();
	foreach ( (array) $keys as $key ) {
		if ( (int) get_option( 'tidewell_page_' . $key ) === $id ) {
			return true;
		}
	}
	return false;
}

function tidewell_is_woo_context() {
	return ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) )
		|| tidewell_page_uses( 'pricing' );
}

add_action( 'init', function () {
	if ( is_admin() || ! tidewell_is_optimized() ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_generator' );
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! tidewell_is_optimized() ) {
		return;
	}

	if ( ! tidewell_is_woo_context() ) {
		foreach ( [ 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style', 'brands-styles' ] as $handle ) {
			wp_dequeue_style( $handle );
		}
		foreach ( [ 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'sourcebuster-js', 'wc-order-attribution' ] as $handle ) {
			wp_dequeue_script( $handle );
		}
	}

	if ( ! is_user_logged_in() ) {
		wp_dequeue_style( 'dashicons' );
	}

	// Elementor pages are not built from blocks.
	if ( is_page() && ! tidewell_is_woo_context() && class_exists( '\Elementor\Plugin' )
		&& \Elementor\Plugin::$instance->documents->get( get_queried_object_id() )?->is_built_with_elementor() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 100 );

// Contact Form 7 assets only on pages that show a form.
add_filter( 'wpcf7_load_js', fn( $load ) => tidewell_is_optimized() ? tidewell_page_uses( [ 'book', 'contact' ] ) : $load );
add_filter( 'wpcf7_load_css', fn( $load ) => tidewell_is_optimized() ? tidewell_page_uses( [ 'book', 'contact' ] ) : $load );

// The first image on the home page is the LCP element: load it eagerly with high priority.
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( ! tidewell_is_optimized() || ! is_front_page() ) {
		return $attr;
	}
	$hero = (int) ( get_option( 'tidewell_images', [] )['hero.jpg'] ?? 0 );
	if ( $hero && $attachment->ID === $hero ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
		$attr['decoding']      = 'async';
	}
	return $attr;
}, 10, 2 );
