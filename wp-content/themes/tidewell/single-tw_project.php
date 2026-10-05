<?php
/**
 * Single portfolio project.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$tw_data  = function_exists( 'tidewell_project_data' ) ? tidewell_project_data( get_the_ID() ) : [];
	$tw_types = wp_list_pluck( $tw_data['types'] ?? [], 'name' );
	$tw_size  = function_exists( 'tidewell_image_size' ) ? tidewell_image_size( 'lightbox' ) : 'large';
	?>
	<article id="content" <?php post_class( 'tw-article tw-project' ); ?>>
		<div class="tw-container">
			<nav class="tw-breadcrumb" aria-label="Breadcrumb">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
					<li><a href="<?php echo esc_url( tidewell_theme_page_url( 'portfolio', '/portfolio/' ) ); ?>">Portfolio</a></li>
					<li aria-current="page"><?php the_title(); ?></li>
				</ol>
			</nav>

			<header class="tw-article__header">
				<?php if ( $tw_types ) : ?>
					<p class="tw-eyebrow-text"><?php echo esc_html( implode( ', ', $tw_types ) ); ?></p>
				<?php endif; ?>
				<h1 class="tw-article__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="tw-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<dl class="tw-facts">
					<?php if ( ! empty( $tw_data['location'] ) ) : ?>
						<div><dt>Location</dt><dd><?php echo esc_html( $tw_data['location'] ); ?></dd></div>
					<?php endif; ?>
					<?php if ( ! empty( $tw_data['service'] ) ) : ?>
						<div><dt>Service</dt><dd><a href="<?php echo esc_url( $tw_data['service_url'] ); ?>"><?php echo esc_html( $tw_data['service'] ); ?></a></dd></div>
					<?php endif; ?>
					<?php if ( ! empty( $tw_data['duration'] ) ) : ?>
						<div><dt>Time on site</dt><dd><?php echo esc_html( $tw_data['duration'] ); ?></dd></div>
					<?php endif; ?>
				</dl>
			</header>

			<?php
			if ( ! empty( $tw_data['before_id'] ) && function_exists( 'tidewell_before_after' ) ) {
				echo tidewell_before_after( $tw_data['before_id'], $tw_data['after_id'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in function
			} elseif ( has_post_thumbnail() ) {
				echo '<figure class="tw-article__media">' . get_the_post_thumbnail( null, $tw_size, [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 1100px) 1040px, 100vw' ] ) . '</figure>';
			}
			?>

			<div class="tw-article__content entry-content">
				<h2>The project</h2>
				<?php the_content(); ?>
			</div>

			<?php if ( ! empty( $tw_data['gallery_ids'] ) ) : ?>
				<section class="tw-gallery" aria-labelledby="tw-gallery-title">
					<h2 id="tw-gallery-title">More photos</h2>
					<div class="tw-gallery__grid">
						<?php foreach ( $tw_data['gallery_ids'] as $tw_id ) : ?>
							<figure><?php echo wp_get_attachment_image( $tw_id, function_exists( 'tidewell_image_size' ) ? tidewell_image_size( 'card' ) : 'medium_large', false, [ 'sizes' => '(min-width: 800px) 50vw, 100vw' ] ); ?></figure>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<aside class="tw-callout">
				<div>
					<h2>Need <?php echo esc_html( ! empty( $tw_data['service'] ) ? 'a ' . strtolower( $tw_data['service'] ) : 'a hand' ); ?>?</h2>
					<p>Tell us about your space and we will confirm your booking within one business day.</p>
				</div>
				<a class="tw-btn tw-btn--accent" href="<?php echo esc_url( tidewell_theme_page_url( 'book', '/book/' ) ); ?>">Book a cleaning</a>
			</aside>

			<nav class="tw-post-nav" aria-label="More projects">
				<?php
				previous_post_link( '<div class="tw-post-nav__prev">%link</div>', '<span>Previous project</span>%title' );
				next_post_link( '<div class="tw-post-nav__next">%link</div>', '<span>Next project</span>%title' );
				?>
			</nav>
		</div>
	</article>
	<?php
endwhile;

get_footer();
