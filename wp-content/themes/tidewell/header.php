<?php
/**
 * Site header.
 */

defined( 'ABSPATH' ) || exit;

$tw_business = tidewell_theme_business();
$tw_main_tag = tidewell_is_elementor_page() ? 'main' : 'div';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#tw-main">Skip to content</a>

<?php if ( function_exists( 'tidewell_is_demo' ) && tidewell_is_demo() ) : ?>
	<div class="tw-demo-bar">
		<p>Portfolio demo: Tidewell Cleaning Co. is a fictional business. <a href="https://github.com/tiankewei333-create/tidewell-wp-demo">See how it was built</a></p>
	</div>
<?php endif; ?>

<header class="tw-header">
	<div class="tw-header__inner">
		<a class="tw-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php echo tidewell_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?>
			<span class="tw-logo__text">Tidewell<span class="tw-logo__sub">Cleaning Co.</span></span>
		</a>

		<button class="tw-nav-toggle" type="button" aria-expanded="false" aria-controls="tw-nav">
			<span class="tw-nav-toggle__icon" aria-hidden="true"></span>
			<span class="screen-reader-text">Menu</span>
		</button>

		<nav id="tw-nav" class="tw-nav" aria-label="Main">
			<?php
			wp_nav_menu( [
				'theme_location' => 'menu-1',
				'container'      => false,
				'menu_class'     => 'tw-menu',
				'fallback_cb'    => false,
				'depth'          => 2,
			] );
			?>
			<div class="tw-nav__cta">
				<?php if ( ! empty( $tw_business['phone'] ) ) : ?>
					<a class="tw-header-phone" href="tel:<?php echo esc_attr( $tw_business['phone_e164'] ); ?>"><?php echo esc_html( $tw_business['phone'] ); ?></a>
				<?php endif; ?>
				<a class="tw-btn tw-btn--accent" href="<?php echo esc_url( tidewell_theme_page_url( 'book', '/book/' ) ); ?>">Book a cleaning</a>
			</div>
		</nav>
	</div>
</header>

<<?php echo esc_html( $tw_main_tag ); ?> id="tw-main" class="tw-page" tabindex="-1">
