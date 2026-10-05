<?php
/**
 * Blog index (the page assigned as "Posts page").
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div id="content" class="tw-blog">
	<section class="tw-blog__hero">
		<div class="tw-container">
			<p class="tw-eyebrow-text">Blog</p>
			<h1 class="tw-article__title">Cleaning tips from our team</h1>
			<p class="tw-article__lead">Checklists, product advice and behind-the-scenes notes from Portland's eco-friendly cleaners.</p>
		</div>
	</section>

	<div class="tw-container tw-blog__list">
		<?php if ( have_posts() ) : ?>
			<ul class="tw-posts" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					tidewell_post_card( null, 'h2' );
				endwhile;
				?>
			</ul>
			<?php the_posts_pagination( [ 'mid_size' => 1 ] ); ?>
		<?php else : ?>
			<p>No articles yet. Check back soon.</p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
