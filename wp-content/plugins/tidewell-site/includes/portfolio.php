<?php
/**
 * Portfolio: "Project" post type, "Project type" taxonomy, editor fields,
 * filterable gallery shortcode and before/after comparison.
 */

defined( 'ABSPATH' ) || exit;

const TIDEWELL_PROJECT_META = [
	'_tw_location'    => 'string',
	'_tw_service'     => 'string',
	'_tw_duration'    => 'string',
	'_tw_before_id'   => 'integer',
	'_tw_gallery_ids' => 'string',
];

add_action( 'init', 'tidewell_register_portfolio' );
function tidewell_register_portfolio() {
	register_post_type( 'tw_project', [
		'labels'        => [
			'name'               => 'Projects',
			'singular_name'      => 'Project',
			'add_new_item'       => 'Add new project',
			'edit_item'          => 'Edit project',
			'view_item'          => 'View project',
			'search_items'       => 'Search projects',
			'not_found'          => 'No projects found',
			'featured_image'     => 'Main photo (after)',
			'set_featured_image' => 'Set main photo',
			'menu_name'          => 'Portfolio',
		],
		'public'        => true,
		'has_archive'   => false,
		'rewrite'       => [ 'slug' => 'projects', 'with_front' => false ],
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 21,
		'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
		'show_in_rest'  => true,
	] );

	register_taxonomy( 'tw_project_type', 'tw_project', [
		'labels'            => [
			'name'          => 'Project types',
			'singular_name' => 'Project type',
			'add_new_item'  => 'Add project type',
		],
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'project-type', 'with_front' => false ],
	] );

	foreach ( TIDEWELL_PROJECT_META as $key => $type ) {
		register_post_meta( 'tw_project', $key, [
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => fn() => current_user_can( 'edit_posts' ),
		] );
	}
}

function tidewell_project_data( $post_id ) {
	$services = tidewell_services();
	$service  = get_post_meta( $post_id, '_tw_service', true );
	$terms    = get_the_terms( $post_id, 'tw_project_type' );
	$gallery  = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_tw_gallery_ids', true ) ) ) );

	return [
		'location'     => get_post_meta( $post_id, '_tw_location', true ),
		'duration'     => get_post_meta( $post_id, '_tw_duration', true ),
		'service_key'  => $service,
		'service'      => $services[ $service ]['title'] ?? '',
		'service_url'  => isset( $services[ $service ] ) ? tidewell_service_url( $service ) : '',
		'before_id'    => (int) get_post_meta( $post_id, '_tw_before_id', true ),
		'after_id'     => (int) get_post_thumbnail_id( $post_id ),
		'gallery_ids'  => array_values( $gallery ),
		'types'        => is_array( $terms ) ? $terms : [],
	];
}

function tidewell_service_url( $key ) {
	$page_id = (int) get_option( 'tidewell_page_service_' . $key );
	return $page_id ? get_permalink( $page_id ) : home_url( '/services/' );
}

/* ---------- Editor fields ---------- */

add_action( 'add_meta_boxes_tw_project', function () {
	add_meta_box( 'tw_project_details', 'Project details', 'tidewell_project_metabox', 'tw_project', 'normal', 'high' );
} );

function tidewell_project_metabox( $post ) {
	wp_nonce_field( 'tw_project_save', 'tw_project_nonce' );
	$d = tidewell_project_data( $post->ID );
	?>
	<div class="tw-metabox">
		<p>
			<label for="tw_location"><strong>Location</strong></label><br>
			<input type="text" class="widefat" id="tw_location" name="tw_location" value="<?php echo esc_attr( $d['location'] ); ?>" placeholder="e.g. Sellwood, Portland">
		</p>
		<p>
			<label for="tw_service"><strong>Service</strong></label><br>
			<select id="tw_service" name="tw_service">
				<option value="">—</option>
				<?php foreach ( tidewell_services() as $key => $service ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $d['service_key'], $key ); ?>><?php echo esc_html( $service['title'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="tw_duration"><strong>Duration / team</strong></label><br>
			<input type="text" class="widefat" id="tw_duration" name="tw_duration" value="<?php echo esc_attr( $d['duration'] ); ?>" placeholder="e.g. 5 hours, 2 cleaners">
		</p>
		<div class="tw-media-field" data-multiple="0">
			<p><strong>"Before" photo</strong> <span class="description">Optional. Shows a before/after slider using the main photo as "after".</span></p>
			<input type="hidden" name="tw_before_id" value="<?php echo esc_attr( $d['before_id'] ?: '' ); ?>">
			<div class="tw-media-preview"><?php echo $d['before_id'] ? wp_get_attachment_image( $d['before_id'], 'thumbnail' ) : ''; ?></div>
			<button type="button" class="button tw-media-pick">Choose photo</button>
			<button type="button" class="button-link tw-media-clear">Remove</button>
		</div>
		<div class="tw-media-field" data-multiple="1">
			<p><strong>Gallery</strong> <span class="description">Extra photos shown below the project story.</span></p>
			<input type="hidden" name="tw_gallery_ids" value="<?php echo esc_attr( implode( ',', $d['gallery_ids'] ) ); ?>">
			<div class="tw-media-preview">
				<?php foreach ( $d['gallery_ids'] as $id ) { echo wp_get_attachment_image( $id, 'thumbnail' ); } ?>
			</div>
			<button type="button" class="button tw-media-pick">Choose photos</button>
			<button type="button" class="button-link tw-media-clear">Remove all</button>
		</div>
	</div>
	<?php
}

add_action( 'save_post_tw_project', function ( $post_id ) {
	if ( ! isset( $_POST['tw_project_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tw_project_nonce'] ), 'tw_project_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$text = [ 'tw_location' => '_tw_location', 'tw_duration' => '_tw_duration' ];
	foreach ( $text as $field => $key ) {
		update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ?? '' ) ) );
	}
	$service = sanitize_key( $_POST['tw_service'] ?? '' );
	update_post_meta( $post_id, '_tw_service', array_key_exists( $service, tidewell_services() ) ? $service : '' );
	update_post_meta( $post_id, '_tw_before_id', absint( $_POST['tw_before_id'] ?? 0 ) );
	$gallery = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['tw_gallery_ids'] ?? '' ) ) ) ) );
	update_post_meta( $post_id, '_tw_gallery_ids', implode( ',', $gallery ) );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'tw_project' !== $screen->post_type || ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'tidewell-project-admin', TIDEWELL_URL . 'assets/js/project-admin.js', [ 'jquery' ], TIDEWELL_VERSION, true );
} );

add_filter( 'manage_tw_project_posts_columns', function ( $columns ) {
	return array_slice( $columns, 0, 1, true ) + [ 'tw_thumb' => 'Photo' ] + array_slice( $columns, 1, 1, true ) + [ 'tw_location' => 'Location' ] + array_slice( $columns, 2, null, true );
} );

add_action( 'manage_tw_project_posts_custom_column', function ( $column, $post_id ) {
	if ( 'tw_thumb' === $column ) {
		echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
	} elseif ( 'tw_location' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_tw_location', true ) );
	}
}, 10, 2 );

/* ---------- Front end ---------- */

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'tidewell-portfolio', TIDEWELL_URL . 'assets/css/portfolio.css', [], (string) filemtime( TIDEWELL_DIR . 'assets/css/portfolio.css' ) );
	wp_register_script( 'tidewell-portfolio', TIDEWELL_URL . 'assets/js/portfolio.js', [], (string) filemtime( TIDEWELL_DIR . 'assets/js/portfolio.js' ), [ 'strategy' => 'defer', 'in_footer' => true ] );
	if ( is_singular( 'tw_project' ) || tidewell_page_has_portfolio() ) {
		wp_enqueue_style( 'tidewell-portfolio' );
		wp_enqueue_script( 'tidewell-portfolio' );
	}
} );

// Enqueueing from inside the shortcode would print the stylesheet in the footer, after the grid has
// already been laid out unstyled, which shows up as layout shift.
function tidewell_page_has_portfolio() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( has_shortcode( $post->post_content, 'tidewell_portfolio' ) ) {
		return true;
	}
	$elementor = get_post_meta( $post->ID, '_elementor_data', true );
	return is_string( $elementor ) && str_contains( $elementor, '[tidewell_portfolio' );
}

add_shortcode( 'tidewell_portfolio', 'tidewell_portfolio_shortcode' );
function tidewell_portfolio_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'limit'   => -1,
		'filters' => 'yes',
		'type'    => '',
		'service' => '',
	], $atts, 'tidewell_portfolio' );

	wp_enqueue_style( 'tidewell-portfolio' );
	wp_enqueue_script( 'tidewell-portfolio' );

	$show_filters = 'yes' === $atts['filters'];
	$types        = get_terms( [ 'taxonomy' => 'tw_project_type', 'hide_empty' => true ] );
	$types        = is_wp_error( $types ) ? [] : $types;
	$type_slugs   = wp_list_pluck( $types, 'slug' );

	// Without JavaScript the filter links fall back to a server-side filter.
	$requested = $show_filters ? sanitize_title( wp_unslash( $_GET['project_type'] ?? '' ) ) : sanitize_title( $atts['type'] );
	$active    = in_array( $requested, $type_slugs, true ) ? $requested : '';

	$args = [
		'post_type'      => 'tw_project',
		'posts_per_page' => (int) $atts['limit'],
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	];
	if ( $atts['service'] ) {
		$args['meta_query'] = [ [ 'key' => '_tw_service', 'value' => sanitize_key( $atts['service'] ) ] ];
	}
	if ( ! $show_filters && $active ) {
		$args['tax_query'] = [ [ 'taxonomy' => 'tw_project_type', 'field' => 'slug', 'terms' => $active ] ];
	}
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}

	$base_url = remove_query_arg( 'project_type' );
	ob_start();
	?>
	<div class="tw-portfolio" data-tw-portfolio>
		<?php if ( $show_filters && $types ) : ?>
			<nav class="tw-portfolio__filters" aria-label="Filter projects by type">
				<a class="tw-filter" href="<?php echo esc_url( $base_url ); ?>" data-filter="" <?php echo $active ? '' : 'aria-current="true"'; ?>>
					All <span class="tw-filter__count"><?php echo esc_html( $query->post_count ); ?></span>
				</a>
				<?php foreach ( $types as $type ) : ?>
					<a class="tw-filter" href="<?php echo esc_url( add_query_arg( 'project_type', $type->slug, $base_url ) ); ?>" data-filter="<?php echo esc_attr( $type->slug ); ?>" <?php echo $active === $type->slug ? 'aria-current="true"' : ''; ?>>
						<?php echo esc_html( $type->name ); ?> <span class="tw-filter__count"><?php echo esc_html( $type->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
			<p class="screen-reader-text" aria-live="polite" data-tw-status></p>
		<?php endif; ?>

		<ul class="tw-portfolio__grid" role="list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$id    = get_the_ID();
				$data  = tidewell_project_data( $id );
				$slugs = wp_list_pluck( $data['types'], 'slug' );
				$names = wp_list_pluck( $data['types'], 'name' );
				$full  = wp_get_attachment_image_url( $data['after_id'], tidewell_image_size( 'lightbox' ) );
				?>
				<li class="tw-card" data-types="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" <?php echo ( $active && ! in_array( $active, $slugs, true ) ) ? 'hidden' : ''; ?>>
					<div class="tw-card__media">
						<button type="button" class="tw-card__zoom" data-tw-lightbox data-full="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( get_the_title() . ( $data['location'] ? ' — ' . $data['location'] : '' ) ); ?>">
							<span class="screen-reader-text">Enlarge photo: <?php the_title(); ?></span>
							<?php
							echo wp_get_attachment_image( $data['after_id'], tidewell_image_size( 'card' ), false, [
								'sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw',
								'alt'   => '',
							] );
							?>
						</button>
						<?php if ( $data['before_id'] ) : ?>
							<span class="tw-card__badge">Before &amp; after</span>
						<?php endif; ?>
					</div>
					<div class="tw-card__body">
						<p class="tw-card__meta"><?php echo esc_html( implode( ', ', $names ) . ( $data['location'] ? ' · ' . $data['location'] : '' ) ); ?></p>
						<h3 class="tw-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="tw-card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
					</div>
				</li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ul>

		<dialog class="tw-lightbox" aria-label="Project photo">
			<figure class="tw-lightbox__figure">
				<img class="tw-lightbox__img" src="" alt="">
				<figcaption class="tw-lightbox__caption"></figcaption>
			</figure>
			<button type="button" class="tw-lightbox__btn tw-lightbox__prev" data-tw-prev aria-label="Previous photo">&#8249;</button>
			<button type="button" class="tw-lightbox__btn tw-lightbox__next" data-tw-next aria-label="Next photo">&#8250;</button>
			<button type="button" class="tw-lightbox__close" data-tw-close aria-label="Close">&times;</button>
		</dialog>
	</div>
	<?php
	return ob_get_clean();
}

function tidewell_before_after( $before_id, $after_id ) {
	if ( ! $before_id || ! $after_id ) {
		return '';
	}
	$size = tidewell_image_size( 'lightbox' );
	$img  = fn( $id, $class ) => wp_get_attachment_image( $id, $size, false, [ 'class' => $class, 'sizes' => '(min-width: 1100px) 1040px, 100vw', 'loading' => 'eager' ] );
	ob_start();
	?>
	<figure class="tw-ba" data-tw-ba style="--pos: 50%">
		<div class="tw-ba__stage">
			<?php echo $img( $after_id, 'tw-ba__img tw-ba__img--after' ); ?>
			<div class="tw-ba__before" aria-hidden="true"><?php echo $img( $before_id, 'tw-ba__img' ); ?></div>
			<span class="tw-ba__label tw-ba__label--before" aria-hidden="true">Before</span>
			<span class="tw-ba__label tw-ba__label--after" aria-hidden="true">After</span>
			<span class="tw-ba__handle" aria-hidden="true"></span>
			<input class="tw-ba__range" type="range" min="0" max="100" value="50" aria-label="Before and after comparison. Higher values show more of the before photo.">
		</div>
		<figcaption class="tw-ba__caption">Drag the slider to compare before and after.</figcaption>
	</figure>
	<?php
	return ob_get_clean();
}
