<?php
/**
 * Tidewell child theme.
 */

defined( 'ABSPATH' ) || exit;

define( 'TIDEWELL_THEME_VERSION', '1.0.0' );

function tidewell_theme_optimized() {
	return function_exists( 'tidewell_is_optimized' ) ? tidewell_is_optimized() : true;
}

function tidewell_theme_business() {
	return function_exists( 'tidewell_business' ) ? tidewell_business() : [
		'name'       => get_bloginfo( 'name' ),
		'tagline'    => get_bloginfo( 'description' ),
		'phone'      => '',
		'phone_e164' => '',
		'email'      => get_option( 'admin_email' ),
	];
}

function tidewell_theme_page_url( $key, $fallback = '/' ) {
	$id = (int) get_option( 'tidewell_page_' . $key );
	return $id ? get_permalink( $id ) : home_url( $fallback );
}

function tidewell_is_elementor_page() {
	if ( ! is_singular() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$document = \Elementor\Plugin::$instance->documents->get( get_queried_object_id() );
	return $document && $document->is_built_with_elementor();
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'hello-elementor-header-footer' );

	$uri  = get_stylesheet_directory_uri();
	$deps = [ 'hello-elementor', 'hello-elementor-theme-style' ];
	// Print after Elementor's frontend CSS and kit styles so equal-specificity rules resolve in our favour.
	if ( wp_style_is( 'elementor-frontend', 'enqueued' ) || tidewell_is_elementor_page() ) {
		$deps[] = 'elementor-frontend';
	}
	$dir = get_stylesheet_directory();
	wp_enqueue_style( 'tidewell', $uri . '/assets/css/site.css', $deps, (string) filemtime( $dir . '/assets/css/site.css' ) );
	wp_enqueue_script( 'tidewell', $uri . '/assets/js/site.js', [], (string) filemtime( $dir . '/assets/js/site.js' ), [ 'strategy' => 'defer', 'in_footer' => true ] );

	if ( tidewell_theme_optimized() ) {
		$font = $uri . '/assets/fonts/inter-latin-var.woff2';
		wp_add_inline_style( 'tidewell', "@font-face{font-family:'Inter';font-style:normal;font-weight:100 900;font-display:swap;src:url('{$font}') format('woff2');unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}" );
	} else {
		wp_enqueue_style( 'tidewell-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', [], null );
	}
}, 20 );

// WordPress marks the posts page as "current_page_parent" on every non-page singular view, so
// project pages would highlight Blog. Point them at the Portfolio page instead.
add_filter( 'nav_menu_css_class', function ( $classes, $item ) {
	if ( ! is_singular( 'tw_project' ) ) {
		return $classes;
	}
	$object_id = (int) $item->object_id;
	if ( 'page' === $item->object && $object_id === (int) get_option( 'page_for_posts' ) ) {
		$classes = array_diff( $classes, [ 'current_page_parent' ] );
	}
	if ( 'page' === $item->object && $object_id === (int) get_option( 'tidewell_page_portfolio' ) ) {
		$classes[] = 'current-menu-ancestor';
	}
	return $classes;
}, 10, 2 );

add_action( 'wp_head', function () {
	if ( tidewell_theme_optimized() ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_stylesheet_directory_uri() . '/assets/fonts/inter-latin-var.woff2' ) );
	}
}, 2 );

function tidewell_logo_svg() {
	return '<svg class="tw-logo__mark" width="36" height="36" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><rect width="36" height="36" rx="10" fill="#0F766E"/><path d="M7 21.5c3.2 0 3.2-2.5 6.4-2.5s3.2 2.5 6.4 2.5 3.2-2.5 6.4-2.5 3.2 2.5 3.8 2.5" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/><path d="M7 26.5c3.2 0 3.2-2.5 6.4-2.5s3.2 2.5 6.4 2.5 3.2-2.5 6.4-2.5 3.2 2.5 3.8 2.5" fill="none" stroke="#99F6E4" stroke-width="2.4" stroke-linecap="round"/><circle cx="24.5" cy="11.5" r="3.5" fill="#F59E0B"/></svg>';
}

function tidewell_post_card( $post = null, $heading = 'h2' ) {
	$post = get_post( $post );
	$cats = get_the_category( $post->ID );
	?>
	<li class="tw-post-card">
		<a class="tw-post-card__media" href="<?php the_permalink( $post ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $post, function_exists( 'tidewell_image_size' ) ? tidewell_image_size( 'card' ) : 'medium_large', [ 'alt' => '', 'sizes' => '(min-width: 1024px) 380px, 100vw' ] ); ?>
		</a>
		<div class="tw-post-card__body">
			<p class="tw-post-card__meta"><?php echo esc_html( $cats ? $cats[0]->name : 'Blog' ); ?> · <time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time></p>
			<<?php echo esc_html( $heading ); ?> class="tw-post-card__title"><a href="<?php the_permalink( $post ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></<?php echo esc_html( $heading ); ?>>
			<p class="tw-post-card__excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
		</div>
	</li>
	<?php
}
