<?php
/**
 * Site footer.
 */

defined( 'ABSPATH' ) || exit;

$tw_business = tidewell_theme_business();
$tw_main_tag = tidewell_is_elementor_page() ? 'main' : 'div';
?>
</<?php echo esc_html( $tw_main_tag ); ?>>

<footer class="tw-footer">
	<div class="tw-footer__inner">
		<div class="tw-footer__brand">
			<a class="tw-logo tw-logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo tidewell_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?>
				<span class="tw-logo__text">Tidewell<span class="tw-logo__sub">Cleaning Co.</span></span>
			</a>
			<p>Eco-friendly home and office cleaning for Portland and nearby cities since <?php echo esc_html( (string) ( $tw_business['founded'] ?? '' ) ); ?>.</p>
			<?php if ( shortcode_exists( 'tidewell_contact' ) ) : ?>
				<?php echo do_shortcode( '[tidewell_contact]' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( function_exists( 'tidewell_services' ) ) : ?>
			<div class="tw-footer__col">
				<h2 class="tw-footer__title">Services</h2>
				<ul>
					<?php foreach ( tidewell_services() as $tw_key => $tw_service ) : ?>
						<li><a href="<?php echo esc_url( tidewell_service_url( $tw_key ) ); ?>"><?php echo esc_html( $tw_service['title'] ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( tidewell_theme_page_url( 'pricing', '/pricing/' ) ); ?>">Pricing &amp; packages</a></li>
				</ul>
			</div>
		<?php endif; ?>

		<div class="tw-footer__col">
			<h2 class="tw-footer__title">Company</h2>
			<?php
			wp_nav_menu( [
				'theme_location' => 'menu-2',
				'container'      => false,
				'fallback_cb'    => false,
				'depth'          => 1,
			] );
			?>
		</div>

		<?php if ( shortcode_exists( 'tidewell_hours' ) ) : ?>
			<div class="tw-footer__col">
				<h2 class="tw-footer__title">Office hours</h2>
				<?php echo do_shortcode( '[tidewell_hours]' ); ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="tw-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) . ' ' . rtrim( $tw_business['name'], '.' ) ); ?>. Demo site built with WordPress, Elementor and WooCommerce.</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
