<?php
/**
 * Single blog post.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$tw_cats = get_the_category();
	?>
	<article id="content" <?php post_class( 'tw-article' ); ?>>
		<div class="tw-container tw-container--narrow">
			<nav class="tw-breadcrumb" aria-label="Breadcrumb">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
					<li><a href="<?php echo esc_url( tidewell_theme_page_url( 'blog', '/blog/' ) ); ?>">Blog</a></li>
					<li aria-current="page"><?php the_title(); ?></li>
				</ol>
			</nav>

			<header class="tw-article__header">
				<p class="tw-eyebrow-text">
					<?php echo esc_html( $tw_cats ? $tw_cats[0]->name : 'Blog' ); ?> ·
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
				<h1 class="tw-article__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="tw-article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="tw-article__media">
					<?php the_post_thumbnail( function_exists( 'tidewell_image_size' ) ? tidewell_image_size( 'lightbox' ) : 'large', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 800px) 760px, 100vw' ] ); ?>
				</figure>
			<?php endif; ?>

			<div class="tw-article__content entry-content">
				<?php the_content(); ?>
			</div>

			<aside class="tw-callout">
				<div>
					<h2>Short on time?</h2>
					<p>Let our Portland team handle the cleaning while you enjoy your weekend.</p>
				</div>
				<a class="tw-btn tw-btn--accent" href="<?php echo esc_url( tidewell_theme_page_url( 'book', '/book/' ) ); ?>">Book a cleaning</a>
			</aside>

			<nav class="tw-post-nav" aria-label="More articles">
				<?php
				previous_post_link( '<div class="tw-post-nav__prev">%link</div>', '<span>Previous article</span>%title' );
				next_post_link( '<div class="tw-post-nav__next">%link</div>', '<span>Next article</span>%title' );
				?>
			</nav>
		</div>
	</article>
	<?php
endwhile;

get_footer();
