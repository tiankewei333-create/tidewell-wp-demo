<?php
/**
 * Page definitions and the Elementor page builder.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_page_definitions() {
	$pages = [
		'home'      => [ 'title' => 'Home', 'slug' => 'home', 'build' => 'tidewell_layout_home' ],
		'services'  => [ 'title' => 'Services', 'slug' => 'services', 'build' => 'tidewell_layout_services' ],
	];
	foreach ( tidewell_services() as $key => $service ) {
		$pages[ 'service_' . $key ] = [ 'title' => $service['title'], 'slug' => $service['slug'], 'parent' => 'services', 'build' => fn() => tidewell_layout_service( $key ) ];
	}
	return $pages + [
		'pricing'   => [ 'title' => 'Pricing', 'slug' => 'pricing', 'build' => 'tidewell_layout_pricing' ],
		'portfolio' => [ 'title' => 'Portfolio', 'slug' => 'portfolio', 'build' => 'tidewell_layout_portfolio' ],
		'about'     => [ 'title' => 'About', 'slug' => 'about', 'build' => 'tidewell_layout_about' ],
		'blog'      => [ 'title' => 'Blog', 'slug' => 'blog', 'build' => null ],
		'faq'       => [ 'title' => 'FAQ', 'slug' => 'faq', 'build' => 'tidewell_layout_faq' ],
		'contact'   => [ 'title' => 'Contact', 'slug' => 'contact', 'build' => 'tidewell_layout_contact' ],
		'book'      => [ 'title' => 'Book a Cleaning', 'slug' => 'book', 'build' => 'tidewell_layout_book' ],
	];
}

function tidewell_page_url( $key ) {
	$id = (int) get_option( 'tidewell_page_' . $key );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

/**
 * Create or update every page, then save its Elementor layout.
 * Pages are created first so layouts can link to each other.
 */
function tidewell_build_pages() {
	// With the "internal" CSS print method, Elementor echoes a <style> tag whenever a page is saved
	// (including via its save_post hook); drop it so admin redirects and JSON responses still work.
	ob_start();
	tidewell_build_pages_inner();
	ob_end_clean();
}

function tidewell_build_pages_inner() {
	$defs = tidewell_page_definitions();

	foreach ( $defs as $key => $def ) {
		$id     = (int) get_option( 'tidewell_page_' . $key );
		$parent = isset( $def['parent'] ) ? (int) get_option( 'tidewell_page_' . $def['parent'] ) : 0;
		if ( ! $id || ! get_post( $id ) ) {
			$path     = ( $parent ? get_post_field( 'post_name', $parent ) . '/' : '' ) . $def['slug'];
			$existing = get_page_by_path( $path );
			$id       = $existing ? $existing->ID : 0;
		}
		$id = wp_insert_post( [
			'ID'          => $id,
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $def['title'],
			'post_name'   => $def['slug'],
			'post_parent' => $parent,
		] );
		update_option( 'tidewell_page_' . $key, $id );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) get_option( 'tidewell_page_home' ) );
	update_option( 'page_for_posts', (int) get_option( 'tidewell_page_blog' ) );

	foreach ( $defs as $key => $def ) {
		if ( ! $def['build'] ) {
			continue;
		}
		Tidewell_El::reset( 'tidewell-' . $key );
		tidewell_save_elementor_page( (int) get_option( 'tidewell_page_' . $key ), call_user_func( $def['build'] ) );
	}
}

function tidewell_save_elementor_page( $post_id, array $elements ) {
	update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( $document ) {
			// save() alone leaves _elementor_edit_mode empty, so the page would render post_content.
			$document->set_is_built_with_elementor( true );
		}
		if ( $document && $document->save( [ 'elements' => $elements, 'settings' => [ 'template' => 'elementor_header_footer', 'post_status' => 'publish' ] ] ) ) {
			return;
		}
	}

	// Fallback when the document API is unavailable (e.g. Elementor not active yet).
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
}

/* ---------- Shared blocks ---------- */

function tw_page_hero( $eyebrow, $title, $text, array $buttons = [] ) {
	return tw_section( [
		tw_stack( array_filter( [
			$eyebrow ? tw_eyebrow( $eyebrow ) : null,
			tw_heading( $title, 'h1', 'tw-display' ),
			tw_text( $text, 'tw-lead' ),
			$buttons ? tw_buttons( $buttons ) : null,
		] ), 'tw-page-hero__inner' ),
	], 'tw-page-hero', [ 'padding' => tw_sp( 72, 24, 64, 24 ), 'padding_mobile' => tw_sp( 48, 20, 40, 20 ) ] );
}

function tw_section_intro( $eyebrow, $title, $text = '', $class = '' ) {
	return tw_stack( array_filter( [
		$eyebrow ? tw_eyebrow( $eyebrow ) : null,
		tw_heading( $title, 'h2' ),
		$text ? tw_text( $text, 'tw-lead' ) : null,
	] ), trim( 'tw-section-intro ' . $class ) );
}

function tw_cta_band( $title = 'Ready for a cleaner home?', $text = 'Tell us about your space and we will confirm your booking within one business day.' ) {
	$b = tidewell_business();
	return tw_section( [
		tw_grid( [
			tw_stack( [
				tw_heading( $title, 'h2' ),
				tw_text( '<p>' . esc_html( $text ) . '</p>' ),
			] ),
			tw_buttons( [
				[ 'Book a cleaning', tidewell_page_url( 'book' ), 'tw-btn-accent' ],
				[ 'Call ' . $b['phone'], 'tel:' . $b['phone_e164'], 'tw-btn-ghost' ],
			] ),
		], '1.4fr 1fr', 'tw-cta__grid', [ 'grid_align_items' => 'center' ] ),
	], 'tw-cta' );
}

/* ---------- Layouts ---------- */

function tidewell_layout_home() {
	$b        = tidewell_business();
	$services = tidewell_services();

	$service_cards = [];
	foreach ( $services as $key => $s ) {
		$service_cards[] = tw_image_box( $s['image'], $s['title'], $s['summary'] . ' From $' . $s['price_from'] . '.', tidewell_service_url( $key ), 'tw-service-card' );
	}

	return [
		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_eyebrow( 'Eco-friendly cleaning · Portland, OR' ),
					tw_heading( $b['tagline'], 'h1', 'tw-display' ),
					tw_text( '<p>Background-checked teams, plant-based products and upfront prices. Book a recurring, deep or move-out clean in under a minute.</p>', 'tw-lead' ),
					tw_buttons( [
						[ 'Book a cleaning', tidewell_page_url( 'book' ), 'tw-btn-accent' ],
						[ 'See pricing', tidewell_page_url( 'pricing' ), 'tw-btn-outline' ],
					] ),
					tw_icon_list( [
						[ 'text' => 'Background-checked', 'icon' => 'fas fa-user-check' ],
						[ 'text' => 'Insured & bonded', 'icon' => 'fas fa-shield-alt' ],
						[ 'text' => '24-hour re-clean guarantee', 'icon' => 'fas fa-redo' ],
					], 'fas fa-check', 'tw-trust', true ),
				], 'tw-hero__copy', [ 'flex_gap' => tw_gap( 20 ) ] ),
				tw_image( 'hero.jpg', 'hero', 'tw-hero__img' ),
			], '1.05fr 1fr', 'tw-hero__grid', [ 'grid_align_items' => 'center', 'grid_gaps' => tw_gap( 48 ) ] ),
		], 'tw-hero', [ 'padding' => tw_sp( 64, 24, 80, 24 ), 'padding_mobile' => tw_sp( 32, 20, 56, 20 ) ] ),

		tw_section( [
			tw_section_intro( 'Services', 'Cleaning for every kind of mess', '<p>From weekly upkeep to the day you hand back the keys, one team you can trust.</p>' ),
			tw_grid( $service_cards, 4, 'tw-cards' ),
		], 'tw-services' ),

		tw_section( [
			tw_section_intro( 'How it works', 'Booked in a minute, clean by the weekend' ),
			tw_grid( [
				tw_icon_box( 'fas fa-calendar-check', '1. Book online', 'Pick a service and a preferred date. It takes less than a minute and there is no payment upfront.', 'tw-step' ),
				tw_icon_box( 'fas fa-comment-dots', '2. We confirm', 'Within one business day we confirm your arrival window and answer any questions by email or text.', 'tw-step' ),
				tw_icon_box( 'fas fa-home', '3. Come home to clean', 'Your team follows our checklist and texts you when they are done. Not perfect? We come back within 24 hours.', 'tw-step' ),
			], 3 ),
		], 'tw-band-sand' ),

		tw_section( [
			tw_row( [
				tw_section_intro( 'Recent work', 'Before and after, around Portland' ),
				tw_button( 'See all projects', tidewell_page_url( 'portfolio' ), 'tw-btn-outline' ),
			], 'tw-split-head', [ 'flex_justify_content' => 'space-between', 'flex_align_items' => 'flex-end' ] ),
			tw_shortcode( '[tidewell_portfolio limit="3" filters="no"]' ),
		] ),

		tw_section( [
			tw_section_intro( 'Reviews', 'What our clients say' ),
			tw_grid( array_map( 'tw_testimonial', tidewell_testimonials() ), 3 ),
		], 'tw-band-mint' ),

		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_eyebrow( 'Service area' ),
					tw_heading( 'Serving Portland and nearby cities', 'h2' ),
					tw_text( '<p>Our teams are based in Southeast Portland and clean homes and offices across the metro, from St. Johns to West Linn.</p>' ),
					tw_shortcode( '[tidewell_areas]' ),
				] ),
				tw_stack( [
					tw_heading( 'Office hours', 'h3' ),
					tw_shortcode( '[tidewell_hours]' ),
					tw_text( '<p>[tidewell_info field="phone_link"]<br>[tidewell_info field="email_link"]</p>' ),
					tw_button( 'Contact us', tidewell_page_url( 'contact' ), 'tw-btn-outline' ),
				], 'tw-card-panel' ),
			], '1.3fr 1fr', '', [ 'grid_gaps' => tw_gap( 40 ) ] ),
		] ),

		tw_section( [
			tw_row( [
				tw_section_intro( 'From the blog', 'Cleaning tips from our team' ),
				tw_button( 'All articles', tidewell_page_url( 'blog' ), 'tw-btn-outline' ),
			], 'tw-split-head', [ 'flex_justify_content' => 'space-between', 'flex_align_items' => 'flex-end' ] ),
			tw_shortcode( '[tidewell_recent_posts count="3"]' ),
		], 'tw-band-sand' ),

		tw_cta_band(),
	];
}

function tidewell_layout_services() {
	$rows = [];
	$i    = 0;
	foreach ( tidewell_services() as $key => $s ) {
		$copy = tw_stack( [
			tw_eyebrow( 'From $' . $s['price_from'] . ' · ' . $s['duration'] ),
			tw_heading( $s['title'], 'h2' ),
			tw_text( '<p>' . esc_html( $s['summary'] ) . '</p>' ),
			tw_icon_list( array_slice( $s['includes'], 0, 4 ) ),
			tw_buttons( [
				[ 'Learn more', tidewell_service_url( $key ), '' ],
				[ 'Book now', tidewell_page_url( 'book' ), 'tw-btn-outline' ],
			] ),
		] );
		$image  = tw_image( $s['image'], 'content', 'tw-rounded' );
		$rows[] = tw_section( [
			tw_grid( $i % 2 ? [ $copy, $image ] : [ $image, $copy ], 2, 'tw-feature', [ 'grid_align_items' => 'center', 'grid_gaps' => tw_gap( 56 ), 'grid_columns_grid_tablet' => [ 'unit' => 'fr', 'size' => 1, 'sizes' => [] ] ] ),
		], $i % 2 ? 'tw-band-mint' : '', [ 'padding' => tw_sp( 64, 24, 64, 24 ) ] );
		$i++;
	}

	return array_merge(
		[ tw_page_hero( 'Services', 'Cleaning services in Portland, OR', '<p>Four ways we can help, all with background-checked teams, plant-based products and a 24-hour re-clean guarantee.</p>', [ [ 'Compare prices', tidewell_page_url( 'pricing' ), 'tw-btn-outline' ] ] ) ],
		$rows,
		[ tw_cta_band() ]
	);
}

function tidewell_layout_service( $key ) {
	$s = tidewell_services()[ $key ];
	$b = tidewell_business();

	return [
		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_eyebrow( $b['city'] . ', ' . $b['region'] . ' · From $' . $s['price_from'] ),
					tw_heading( $s['title'] . ' in ' . $b['city'], 'h1', 'tw-display' ),
					tw_text( '<p>' . esc_html( $s['intro'] ) . '</p>', 'tw-lead' ),
					tw_buttons( [
						[ 'Book ' . strtolower( $s['menu_title'] ), tidewell_page_url( 'book' ), 'tw-btn-accent' ],
						[ 'See pricing', tidewell_page_url( 'pricing' ), 'tw-btn-outline' ],
					] ),
					tw_icon_list( [
						[ 'text' => 'Typical visit: ' . $s['duration'], 'icon' => 'far fa-clock' ],
						[ 'text' => 'Ideal for: ' . $s['ideal_for'], 'icon' => 'fas fa-users' ],
					], 'fas fa-check', 'tw-facts' ),
				], '', [ 'flex_gap' => tw_gap( 20 ) ] ),
				tw_image( $s['image'], 'hero', 'tw-rounded' ),
			], '1.05fr 1fr', '', [ 'grid_align_items' => 'center', 'grid_gaps' => tw_gap( 48 ) ] ),
		], 'tw-page-hero', [ 'padding' => tw_sp( 64, 24, 72, 24 ) ] ),

		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_section_intro( 'Checklist', 'What is included' ),
					tw_icon_list( $s['includes'], 'fas fa-check-circle', 'tw-list--two-col' ),
				] ),
				tw_stack( [
					tw_heading( 'Where we clean', 'h3' ),
					tw_text( '<p>We offer ' . esc_html( strtolower( $s['title'] ) ) . ' across ' . esc_html( $b['city'] ) . ' neighborhoods including:</p>' ),
					tw_shortcode( '[tidewell_areas type="neighborhoods"]' ),
					tw_text( '<p>Plus ' . esc_html( implode( ', ', array_slice( $b['areas'], 1 ) ) ) . '.</p>' ),
				], 'tw-card-panel' ),
			], '1.5fr 1fr', '', [ 'grid_gaps' => tw_gap( 40 ) ] ),
		] ),

		tw_section( [
			tw_section_intro( 'Our work', 'Recent ' . strtolower( $s['menu_title'] ) . ' projects' ),
			tw_shortcode( '[tidewell_portfolio filters="no" limit="3" service="' . $key . '"]' ),
		], 'tw-band-sand' ),

		tw_section( [
			tw_grid( [
				tw_section_intro( 'FAQ', 'Questions about ' . strtolower( $s['menu_title'] ) ),
				tw_accordion( $s['faqs'] ),
			], '1fr 1.6fr', '', [ 'grid_gaps' => tw_gap( 40 ) ] ),
		] ),

		tw_cta_band( 'Book your ' . strtolower( $s['menu_title'] ) . ' today' ),
	];
}

function tidewell_layout_pricing() {
	$ids  = implode( ',', array_filter( array_map( 'tidewell_product_id', [ 'standard', 'deep', 'move', 'office' ] ) ) );
	$gift = tidewell_product_id( 'gift' );

	return [
		tw_page_hero( 'Pricing', 'Simple, upfront pricing', '<p>Pick your package and home size. No payment today: we confirm your date by email and you pay after the clean.</p>' ),

		tw_section( [
			tw_shortcode( '[products ids="' . $ids . '" columns="4" orderby="post__in"]', 'tw-products' ),
			tw_text( '<p class="tw-note">Prices are per visit for a typical home. Very large homes or heavy build-up may need extra time, and we will always confirm with you first.</p>' ),
		] ),

		tw_section( [
			tw_section_intro( 'Every visit includes', 'No hidden extras', '', 'tw-center' ),
			tw_grid( [
				tw_icon_box( 'fas fa-leaf', 'Plant-based supplies', 'We bring everything, including a HEPA vacuum. Fragrance-free by default.' ),
				tw_icon_box( 'fas fa-user-shield', 'Vetted, insured team', 'Background-checked cleaners, covered by our liability insurance and bonding.' ),
				tw_icon_box( 'fas fa-redo', 'Re-clean guarantee', 'Tell us within 24 hours if anything was missed and we will come back for free.' ),
				tw_icon_box( 'fas fa-mobile-alt', 'Arrival texts', 'We text you when your team arrives and again when they leave.' ),
			], 4 ),
		], 'tw-band-mint' ),

		$gift ? tw_section( [
			tw_grid( [
				tw_image( 'about-team.jpg', 'content', 'tw-rounded' ),
				tw_stack( [
					tw_eyebrow( 'Gift cards' ),
					tw_heading( 'Give the gift of a clean home', 'h2' ),
					tw_text( '<p>Perfect for new parents, new homeowners or anyone who deserves a break. Available in $50, $100 and $200, delivered by email and never expires.</p>' ),
					tw_buttons( [ [ 'Buy a gift card', get_permalink( $gift ), '' ] ] ),
				] ),
			], 2, '', [ 'grid_align_items' => 'center', 'grid_gaps' => tw_gap( 56 ) ] ),
		] ) : null,

		tw_cta_band( 'Not sure which clean you need?', 'Send us a few details about your home and we will recommend the right service and price.' ),
	];
}

function tidewell_layout_portfolio() {
	return [
		tw_page_hero( 'Portfolio', 'Our work around Portland', '<p>Real kitchens, bathrooms, move-outs and offices our team has cleaned. Filter by project type and open any project for the full before-and-after story.</p>' ),
		tw_section( [ tw_shortcode( '[tidewell_portfolio]' ) ], '', [ 'padding' => tw_sp( 24, 24, 88, 24 ) ] ),
		tw_cta_band( 'Want results like these?' ),
	];
}

function tidewell_layout_about() {
	$b = tidewell_business();
	return [
		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_eyebrow( 'About us' ),
					tw_heading( 'A small team that sweats the details', 'h1', 'tw-display' ),
					tw_text( '<p>Tidewell started in ' . esc_html( (string) $b['founded'] ) . ' with one car, two cleaners and a simple idea: treat every home like it belongs to a friend. Today we are a team of fourteen, still based in Southeast Portland, still obsessed with the corners other cleaners skip.</p><p>We pay above the local average, train every new team member for two weeks, and keep teams small so you see familiar faces.</p>', 'tw-lead' ),
				], '', [ 'flex_gap' => tw_gap( 20 ) ] ),
				tw_image( 'about-team.jpg', 'hero', 'tw-rounded' ),
			], '1.05fr 1fr', '', [ 'grid_align_items' => 'center', 'grid_gaps' => tw_gap( 48 ) ] ),
		], 'tw-page-hero', [ 'padding' => tw_sp( 64, 24, 72, 24 ) ] ),

		tw_section( [
			tw_grid( [
				tw_counter( (int) gmdate( 'Y' ) - $b['founded'], '+', 'Years in Portland' ),
				tw_counter( 14, '', 'Team members' ),
				tw_counter( count( $b['areas'] ), '', 'Cities served' ),
				tw_counter( 24, 'h', 'Re-clean guarantee' ),
			], 4, 'tw-stats' ),
		], 'tw-band-mint', [ 'padding' => tw_sp( 48, 24, 48, 24 ) ] ),

		tw_section( [
			tw_section_intro( 'What we stand for', 'Our values' ),
			tw_grid( [
				tw_icon_box( 'fas fa-hand-holding-heart', 'People first', 'Fair pay, paid training and predictable schedules. Happy cleaners do better work, and they stay.' ),
				tw_icon_box( 'fas fa-leaf', 'Planet-friendly', 'Plant-based products, refillable bottles and washable microfiber instead of paper towels.' ),
				tw_icon_box( 'fas fa-clipboard-check', 'Accountable', 'A written checklist for every visit and a 24-hour re-clean guarantee if anything is missed.' ),
			], 3 ),
		] ),

		tw_cta_band( 'Meet your new cleaning team' ),
	];
}

function tidewell_layout_faq() {
	return [
		tw_page_hero( 'FAQ', 'Frequently asked questions', '<p>Everything you need to know about booking, pricing and what to expect on cleaning day.</p>' ),
		tw_section( [
			tw_accordion( tidewell_faqs() ),
		], 'tw-narrow', [ 'padding' => tw_sp( 24, 24, 88, 24 ) ] ),
		tw_cta_band( 'Still have a question?', 'Call us, send a message or book and add a note. A real person replies within one business day.' ),
	];
}

function tidewell_layout_contact() {
	return [
		tw_page_hero( 'Contact', 'Get in touch', '<p>Questions about a service, a quote for a larger space or feedback on a recent clean? We reply within one business day.</p>' ),
		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_heading( 'Send us a message', 'h2' ),
					tw_shortcode( tidewell_form_shortcode( 'contact' ), 'tw-form' ),
				], 'tw-card-panel' ),
				tw_stack( [
					tw_heading( 'Contact details', 'h2' ),
					tw_shortcode( '[tidewell_contact]' ),
					tw_heading( 'Office hours', 'h3' ),
					tw_shortcode( '[tidewell_hours]' ),
					tw_map(),
				] ),
			], '1.2fr 1fr', '', [ 'grid_gaps' => tw_gap( 40 ) ] ),
		], '', [ 'padding' => tw_sp( 24, 24, 88, 24 ) ] ),
	];
}

function tidewell_layout_book() {
	return [
		tw_page_hero( 'Book a cleaning', 'Book your clean in under a minute', '<p>Tell us about your home and when suits you. No payment today: we confirm your appointment within one business day.</p>' ),
		tw_section( [
			tw_grid( [
				tw_stack( [
					tw_shortcode( tidewell_form_shortcode( 'booking' ), 'tw-form' ),
				], 'tw-card-panel' ),
				tw_stack( [
					tw_heading( 'What happens next', 'h2' ),
					tw_icon_list( [
						[ 'text' => 'We check availability for your date and area', 'icon' => 'fas fa-search' ],
						[ 'text' => 'You get a confirmation with your arrival window', 'icon' => 'fas fa-envelope-open-text' ],
						[ 'text' => 'Your team arrives, cleans and texts you when done', 'icon' => 'fas fa-broom' ],
						[ 'text' => 'Pay after the clean by card or cash', 'icon' => 'fas fa-credit-card' ],
					], 'fas fa-check', 'tw-steps-list' ),
					tw_text( '<p>Prefer to pick a package? <a href="' . esc_url( tidewell_page_url( 'pricing' ) ) . '">See pricing</a>.<br>Rather talk to someone? Call [tidewell_info field="phone_link"].</p>' ),
				], 'tw-aside' ),
			], '1.5fr 1fr', '', [ 'grid_gaps' => tw_gap( 40 ) ] ),
		], '', [ 'padding' => tw_sp( 24, 24, 88, 24 ) ] ),
	];
}
