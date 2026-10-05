<?php
/**
 * One-time demo content setup. Safe to re-run: every step updates existing content instead of duplicating it.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_setup( $force = false ) {
	if ( ! $force && get_option( 'tidewell_setup_version' ) === TIDEWELL_VERSION ) {
		return;
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}
	wp_set_current_user( 1 );

	tidewell_setup_options();
	tidewell_setup_images();
	tidewell_setup_forms();
	tidewell_setup_woocommerce();
	tidewell_setup_elementor_kit();
	tidewell_apply_perf_mode( get_option( 'tidewell_perf_mode', 'optimized' ), false );
	tidewell_build_pages();
	tidewell_setup_projects();
	tidewell_setup_posts();
	tidewell_setup_menus();
	tidewell_setup_seo();

	flush_rewrite_rules();
	update_option( 'tidewell_setup_version', TIDEWELL_VERSION );
}

function tidewell_setup_options() {
	$b = tidewell_business();
	update_option( 'blogname', $b['name'] );
	update_option( 'blogdescription', $b['tagline'] );
	update_option( 'timezone_string', 'America/Los_Angeles' );
	update_option( 'date_format', 'F j, Y' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'blog_public', 1 );
	update_option( 'tidewell_demo_mode', 'yes' );
	add_option( 'tidewell_perf_mode', 'optimized' );

	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	// Playground's filesystem does not reliably persist Elementor's generated CSS files.
	update_option( 'elementor_css_print_method', 'internal' );
	update_option( 'elementor_onboarded', true );
	update_option( 'elementor_tracker_notice', '1' );
	update_option( 'elementor_cpt_support', [ 'page', 'post' ] );

	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	// set_permalink_structure() resets WP_Rewrite, dropping the endpoints WooCommerce added on init
	// (order-received, my-account pages). Any rule rebuild later in this request would lose them.
	if ( function_exists( 'WC' ) ) {
		WC()->query->add_endpoints();
	}

	foreach ( [ 'sample-page' => 'page', 'hello-world' => 'post' ] as $slug => $type ) {
		$post = get_page_by_path( $slug, OBJECT, $type );
		if ( $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy && 'publish' !== get_post_status( $privacy ) ) {
		wp_update_post( [ 'ID' => $privacy, 'post_status' => 'publish' ] );
	}
}

function tidewell_image_alts() {
	return [
		'hero.jpg'                    => 'Tidewell cleaner wiping a glass coffee table in a bright Portland living room',
		'service-standard.jpg'        => 'Gloved hand wiping a white quartz kitchen counter with a microfiber cloth',
		'service-deep.jpg'            => 'Cleaner scrubbing white subway tile grout in a walk-in shower',
		'service-move.jpg'            => 'Cleaner mopping the floor of an empty apartment on move-out day',
		'service-office.jpg'          => 'Cleaner wiping a desk in a small office after hours',
		'about-team.jpg'              => 'Three smiling Tidewell team members in teal polo shirts',
		'project-kitchen-after.jpg'   => 'Sage green craftsman kitchen after a deep clean',
		'project-kitchen-before.jpg'  => 'The same kitchen before cleaning, with grease and clutter',
		'project-condo-after.jpg'     => 'Empty Pearl District condo with spotless concrete floors',
		'project-condo-before.jpg'    => 'The same condo before the move-out clean, with packing debris',
		'project-bathroom-after.jpg'  => 'Vintage bathroom with a gleaming clawfoot tub and white hex tile',
		'project-bathroom-before.jpg' => 'The same bathroom before cleaning, with soap scum and stains',
		'project-office.jpg'          => 'Tidy studio office with exposed brick and light wood desks',
		'project-living.jpg'          => 'Clean vaulted living room with a grey sectional sofa',
		'project-bedroom.jpg'         => 'Calm bedroom with a freshly made bed and an organized closet',
	];
}

/**
 * Import bundled images into the media library. Returns filename => attachment ID.
 */
function tidewell_setup_images() {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$map = get_option( 'tidewell_images', [] );
	foreach ( tidewell_image_alts() as $file => $alt ) {
		if ( ! empty( $map[ $file ] ) && get_post( $map[ $file ] ) ) {
			continue;
		}
		$source = TIDEWELL_DIR . 'assets/images/' . $file;
		if ( ! file_exists( $source ) ) {
			continue;
		}
		$upload = wp_upload_bits( $file, null, file_get_contents( $source ) );
		if ( ! empty( $upload['error'] ) ) {
			continue;
		}
		$id = wp_insert_attachment( [
			'post_mime_type' => 'image/jpeg',
			'post_title'     => ucwords( str_replace( '-', ' ', pathinfo( $file, PATHINFO_FILENAME ) ) ),
			'post_status'    => 'inherit',
		], $upload['file'] );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		$map[ $file ] = $id;
	}
	update_option( 'tidewell_images', $map );
	return $map;
}

function tidewell_image_id( $file ) {
	return (int) ( get_option( 'tidewell_images', [] )[ $file ] ?? 0 );
}

/* ---------- Forms (Contact Form 7) ---------- */

function tidewell_setup_forms() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return;
	}
	$service_options = implode( ' ', array_map( fn( $s ) => '"' . $s['title'] . '"', tidewell_services() ) );

	$forms = [
		'booking' => [
			'title'   => 'Booking request',
			'form'    => <<<FORM
<div class="tw-form-grid">
<p><label>Full name *<br>[text* your-name autocomplete:name]</label></p>
<p><label>Email *<br>[email* your-email autocomplete:email]</label></p>
<p><label>Phone *<br>[tel* your-phone autocomplete:tel]</label></p>
<p><label>ZIP code *<br>[text* zip-code minlength:5 maxlength:10 autocomplete:postal-code]</label></p>
<p><label>Service *<br>[select* service first_as_label "Choose a service" {$service_options}]</label></p>
<p><label>Home size<br>[select home-size "Studio / 1 bedroom" "2 bedrooms" "3 bedrooms" "4+ bedrooms" "Office / commercial"]</label></p>
<p><label>Preferred date *<br>[date* preferred-date]</label></p>
<p><label>Arrival window<br>[select arrival-window "Morning (8–11 am)" "Midday (11 am–2 pm)" "Afternoon (2–5 pm)"]</label></p>
</div>
<p><label>Anything we should know? Pets, parking, rooms to focus on<br>[textarea notes x4]</label></p>
<p class="tw-form-consent">[acceptance consent] I agree to be contacted about this booking request. [/acceptance]</p>
<p>[submit "Request my booking"]</p>
FORM,
			'subject' => 'New booking request: [service] on [preferred-date]',
			'body'    => "Name: [your-name]\nEmail: [your-email]\nPhone: [your-phone]\nZIP: [zip-code]\n\nService: [service]\nHome size: [home-size]\nPreferred date: [preferred-date]\nArrival window: [arrival-window]\n\nNotes:\n[notes]",
			'reply'   => "Hi [your-name],\n\nThanks for your booking request for a [service] on [preferred-date]. We will confirm your appointment within one business day.\n\nTidewell Cleaning Co.\n(503) 555-0142",
			'sent'    => 'Thanks! Your booking request is in. We will confirm your arrival window by email within one business day.',
		],
		'contact' => [
			'title'   => 'Contact',
			'form'    => <<<FORM
<div class="tw-form-grid">
<p><label>Name *<br>[text* your-name autocomplete:name]</label></p>
<p><label>Email *<br>[email* your-email autocomplete:email]</label></p>
</div>
<p><label>Message *<br>[textarea* your-message x5]</label></p>
<p>[submit "Send message"]</p>
FORM,
			'subject' => 'Website message from [your-name]',
			'body'    => "From: [your-name] <[your-email]>\n\n[your-message]",
			'reply'   => "Hi [your-name],\n\nThanks for getting in touch. We usually reply within one business day.\n\nTidewell Cleaning Co.",
			'sent'    => 'Thanks for your message. We usually reply within one business day.',
		],
	];

	foreach ( $forms as $key => $config ) {
		$existing = (int) get_option( 'tidewell_form_' . $key );
		$form     = $existing ? wpcf7_contact_form( $existing ) : null;
		if ( ! $form ) {
			$form = WPCF7_ContactForm::get_template( [ 'title' => $config['title'] ] );
		}
		$props                               = $form->get_properties();
		$props['form']                       = $config['form'];
		$props['mail']['subject']            = $config['subject'];
		$props['mail']['body']               = $config['body'];
		$props['mail']['recipient']          = '[_site_admin_email]';
		$props['mail']['additional_headers'] = 'Reply-To: [your-email]';
		$props['mail_2']                     = array_merge( $props['mail_2'], [
			'active'             => true,
			'subject'            => 'We received your request – Tidewell Cleaning Co.',
			'recipient'          => '[your-email]',
			'body'               => $config['reply'],
			'additional_headers' => 'Reply-To: [_site_admin_email]',
		] );
		$props['messages']['mail_sent_ok'] = $config['sent'];
		// Flamingo defaults to [your-subject], which these forms do not have.
		$props['additional_settings'] = implode( "\n", [
			'flamingo_name: "[your-name]"',
			'flamingo_email: "[your-email]"',
			'flamingo_subject: "' . $config['subject'] . '"',
		] );
		$form->set_properties( $props );
		$form->set_title( $config['title'] );
		$form->save();
		update_option( 'tidewell_form_' . $key, $form->id() );
	}
}

function tidewell_form_shortcode( $key ) {
	$id   = (int) get_option( 'tidewell_form_' . $key );
	$form = $id && function_exists( 'wpcf7_contact_form' ) ? wpcf7_contact_form( $id ) : null;
	return $form ? $form->shortcode() : '';
}

/* ---------- WooCommerce ---------- */

function tidewell_setup_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$b = tidewell_business();
	$options = [
		'woocommerce_coming_soon'                 => 'no',
		'woocommerce_store_pages_only'            => 'no',
		'woocommerce_currency'                    => 'USD',
		'woocommerce_default_country'             => 'US:OR',
		'woocommerce_store_address'               => $b['street'],
		'woocommerce_store_city'                  => $b['city'],
		'woocommerce_store_postcode'              => $b['postal_code'],
		'woocommerce_calc_taxes'                  => 'no',
		'woocommerce_enable_guest_checkout'       => 'yes',
		'woocommerce_ship_to_countries'           => 'disabled',
		'woocommerce_task_list_hidden'            => 'yes',
		'woocommerce_show_marketplace_suggestions' => 'no',
		'woocommerce_allow_tracking'              => 'no',
		'woocommerce_onboarding_profile'          => [ 'skipped' => true, 'completed' => true ],
		'woocommerce_cod_settings'                => [
			'enabled'            => 'yes',
			'title'              => 'Pay after your clean',
			'description'        => 'No payment today. We confirm your appointment by email and you pay by card or cash after the clean.',
			'instructions'       => 'We will email you within one business day to confirm your date and arrival window.',
			'enable_for_methods' => [],
			'enable_for_virtual' => 'yes',
		],
	];
	foreach ( $options as $name => $value ) {
		update_option( $name, $value );
	}

	$categories = [];
	foreach ( [ 'cleaning-packages' => 'Cleaning packages', 'gift-cards' => 'Gift cards' ] as $slug => $name ) {
		$term = term_exists( $slug, 'product_cat' ) ?: wp_insert_term( $name, 'product_cat', [ 'slug' => $slug ] );
		$categories[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
	}

	$ids = [];
	foreach ( tidewell_products() as $i => $p ) {
		$sku        = 'tw-' . $p['key'];
		$existing   = wc_get_product_id_by_sku( $sku );
		$is_variable = isset( $p['variations'] );
		$product    = $existing ? wc_get_product( $existing ) : ( $is_variable ? new WC_Product_Variable() : new WC_Product_Simple() );

		$product->set_name( $p['name'] );
		$product->set_sku( $sku );
		$product->set_status( 'publish' );
		$product->set_virtual( true );
		$product->set_menu_order( $i );
		$product->set_short_description( $p['short'] );
		$product->set_description( $p['description'] );
		$product->set_image_id( tidewell_image_id( $p['image'] ) );
		$product->set_category_ids( [ $categories[ 'gift' === $p['key'] ? 'gift-cards' : 'cleaning-packages' ] ] );

		if ( ! $is_variable ) {
			$product->set_regular_price( (string) $p['price'] );
			$ids[ $p['key'] ] = $product->save();
			continue;
		}

		$attribute = new WC_Product_Attribute();
		$attribute->set_name( $p['attribute'] );
		$attribute->set_options( array_keys( $p['variations'] ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( [ $attribute ] );
		$attr_key = sanitize_title( $p['attribute'] );
		$product->set_default_attributes( [ $attr_key => array_keys( $p['variations'] )[ 'gift' === $p['key'] ? 1 : 0 ] ] );
		$parent_id = $product->save();

		foreach ( $p['variations'] as $option => $price ) {
			$variation_sku = $sku . '-' . sanitize_title( $option );
			$variation_id  = wc_get_product_id_by_sku( $variation_sku );
			$variation     = $variation_id ? wc_get_product( $variation_id ) : new WC_Product_Variation();
			$variation->set_parent_id( $parent_id );
			$variation->set_attributes( [ $attr_key => $option ] );
			$variation->set_regular_price( (string) $price );
			$variation->set_virtual( true );
			$variation->set_sku( $variation_sku );
			$variation->set_status( 'publish' );
			$variation->save();
		}
		WC_Product_Variable::sync( $parent_id );
		$ids[ $p['key'] ] = $parent_id;
	}
	update_option( 'tidewell_products', $ids );
}

function tidewell_product_id( $key ) {
	return (int) ( get_option( 'tidewell_products', [] )[ $key ] ?? 0 );
}

/* ---------- Elementor kit (global colors, fonts, buttons) ---------- */

function tidewell_setup_elementor_kit() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( ! $kit_id ) {
		$kit_id = \Elementor\Plugin::$instance->kits_manager->create_default();
		update_option( 'elementor_active_kit', $kit_id );
	}
	$font     = fn( $weight ) => [ 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => $weight ];
	$settings = array_merge( (array) get_post_meta( $kit_id, '_elementor_page_settings', true ), [
		'system_colors'                      => [
			[ '_id' => 'primary', 'title' => 'Primary', 'color' => '#0F766E' ],
			[ '_id' => 'secondary', 'title' => 'Ink', 'color' => '#12302C' ],
			[ '_id' => 'text', 'title' => 'Text', 'color' => '#3F5754' ],
			[ '_id' => 'accent', 'title' => 'Accent', 'color' => '#F59E0B' ],
		],
		'custom_colors'                      => [
			[ '_id' => 'tw_mint', 'title' => 'Mint background', 'color' => '#EEF7F5' ],
			[ '_id' => 'tw_sand', 'title' => 'Sand background', 'color' => '#FBF7F0' ],
			[ '_id' => 'tw_deep', 'title' => 'Deep teal', 'color' => '#0B3B36' ],
		],
		'system_typography'                  => [
			[ '_id' => 'primary', 'title' => 'Headings' ] + $font( '700' ),
			[ '_id' => 'secondary', 'title' => 'Subheadings' ] + $font( '600' ),
			[ '_id' => 'text', 'title' => 'Body' ] + $font( '400' ),
			[ '_id' => 'accent', 'title' => 'Buttons' ] + $font( '600' ),
		],
		'container_width'                    => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
		'container_padding'                  => [ 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '0', 'left' => '24', 'isLinked' => false ],
		'space_between_widgets'              => [ 'unit' => 'px', 'column' => '16', 'row' => '16', 'isLinked' => true, 'size' => 16 ],
		'body_color'                         => '#3F5754',
		'body_typography_typography'         => 'custom',
		'body_typography_font_family'        => 'Inter',
		'body_typography_font_size'          => [ 'unit' => 'px', 'size' => 17, 'sizes' => [] ],
		'body_typography_line_height'        => [ 'unit' => 'em', 'size' => 1.65, 'sizes' => [] ],
		'site_name'                          => tidewell_business()['name'],
		'site_description'                   => tidewell_business()['tagline'],
	] );
	// Kit link/button styles compile to ".elementor-kit-N a/button" and would restyle every link and
	// <button> on the site (lightbox, filters, nav). Buttons and links are styled in the theme's site.css.
	foreach ( array_keys( $settings ) as $key ) {
		if ( str_starts_with( $key, 'button_' ) || str_starts_with( $key, 'link_' ) ) {
			unset( $settings[ $key ] );
		}
	}
	update_post_meta( $kit_id, '_elementor_page_settings', $settings );
}

/* ---------- Portfolio projects ---------- */

function tidewell_setup_projects() {
	$terms = [];
	foreach ( tidewell_project_types() as $slug => $name ) {
		$term = term_exists( $slug, 'tw_project_type' ) ?: wp_insert_term( $name, 'tw_project_type', [ 'slug' => $slug ] );
		$terms[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
	}

	foreach ( tidewell_projects() as $i => $p ) {
		$existing = get_page_by_path( $p['slug'], OBJECT, 'tw_project' );
		$content  = implode( "\n\n", array_map( fn( $text ) => tidewell_block( 'p', $text ), $p['body'] ) );
		$id       = wp_insert_post( [
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'tw_project',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $p['slug'],
			'post_excerpt' => $p['summary'],
			'post_content' => $content,
			'menu_order'   => $i,
			'meta_input'   => [
				'_tw_location'          => $p['location'],
				'_tw_service'           => $p['service'],
				'_tw_duration'          => $p['duration'],
				'_tw_before_id'         => $p['before'] ? tidewell_image_id( $p['before'] ) : 0,
				'_tw_gallery_ids'       => implode( ',', array_map( 'tidewell_image_id', $p['gallery'] ) ),
				'_yoast_wpseo_metadesc' => $p['summary'],
			],
		] );
		set_post_thumbnail( $id, tidewell_image_id( $p['after'] ) );
		wp_set_object_terms( $id, [ $terms[ $p['type'] ] ], 'tw_project_type' );
	}
}

/* ---------- Blog posts ---------- */

function tidewell_block( $type, $content ) {
	switch ( $type ) {
		case 'h2':
			return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$content}</h2>\n<!-- /wp:heading -->";
		case 'ul':
			$items = implode( "\n", array_map( fn( $item ) => "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->", $content ) );
			return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$items}</ul>\n<!-- /wp:list -->";
		default:
			return "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->";
	}
}

function tidewell_replace_links( $html ) {
	return preg_replace_callback( '/\{(service|page):([a-z]+)\}/', function ( $m ) {
		if ( 'service' === $m[1] ) {
			return esc_url( tidewell_service_url( $m[2] ) );
		}
		$id = (int) get_option( 'tidewell_page_' . $m[2] );
		return esc_url( $id ? get_permalink( $id ) : home_url( '/' ) );
	}, $html );
}

function tidewell_setup_posts() {
	foreach ( tidewell_posts() as $i => $p ) {
		$cat      = term_exists( $p['category'], 'category' ) ?: wp_insert_term( $p['category'], 'category' );
		$existing = get_page_by_path( $p['slug'], OBJECT, 'post' );
		$content  = implode( "\n\n", array_map( fn( $block ) => tidewell_block( $block[0], $block[1] ), $p['blocks'] ) );
		$id       = wp_insert_post( [
			'ID'            => $existing ? $existing->ID : 0,
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $p['title'],
			'post_name'     => $p['slug'],
			'post_excerpt'  => $p['excerpt'],
			'post_content'  => tidewell_replace_links( $content ),
			'post_date'     => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( 6 + $i * 17 ) . ' days 09:30' ) ),
			'post_category' => [ (int) ( is_array( $cat ) ? $cat['term_id'] : $cat ) ],
			'meta_input'    => [ '_yoast_wpseo_metadesc' => $p['excerpt'] ],
		] );
		set_post_thumbnail( $id, tidewell_image_id( $p['image'] ) );
	}
}

/* ---------- Menus ---------- */

function tidewell_setup_menus() {
	$page = fn( $key ) => (int) get_option( 'tidewell_page_' . $key );

	$menus = [
		'menu-1' => [
			'name'  => 'Main menu',
			'items' => [
				[ 'page' => 'home', 'title' => 'Home' ],
				[ 'page' => 'services', 'children' => array_keys( tidewell_services() ) ],
				[ 'page' => 'pricing' ],
				[ 'page' => 'portfolio' ],
				[ 'page' => 'about' ],
				[ 'page' => 'blog' ],
				[ 'page' => 'contact' ],
			],
		],
		'menu-2' => [
			'name'  => 'Footer menu',
			'items' => [
				[ 'page' => 'faq' ],
				[ 'page' => 'book' ],
				[ 'page' => 'privacy', 'id' => (int) get_option( 'wp_page_for_privacy_policy' ) ],
				[ 'page' => 'account', 'id' => function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'myaccount' ) : 0 ],
			],
		],
	];

	$locations = get_theme_mod( 'nav_menu_locations', [] );
	foreach ( $menus as $location => $menu ) {
		$existing = wp_get_nav_menu_object( $menu['name'] );
		if ( $existing ) {
			wp_delete_nav_menu( $existing->term_id );
		}
		$menu_id = wp_create_nav_menu( $menu['name'] );
		foreach ( $menu['items'] as $item ) {
			$object_id = $item['id'] ?? $page( $item['page'] );
			if ( $object_id <= 0 ) {
				continue;
			}
			$parent = wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-object-id' => $object_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-title'     => $item['title'] ?? '',
				'menu-item-status'    => 'publish',
			] );
			foreach ( $item['children'] ?? [] as $service ) {
				wp_update_nav_menu_item( $menu_id, 0, [
					'menu-item-object-id' => $page( 'service_' . $service ),
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-title'     => tidewell_services()[ $service ]['menu_title'],
					'menu-item-parent-id' => $parent,
					'menu-item-status'    => 'publish',
				] );
			}
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* ---------- SEO (Yoast) ---------- */

function tidewell_setup_seo() {
	$b      = tidewell_business();
	$titles = [
		'company_or_person' => 'company',
		'company_name'      => $b['name'],
		'website_name'      => $b['name'],
		'separator'         => 'sc-pipe',
	];
	if ( class_exists( 'WPSEO_Options' ) ) {
		foreach ( $titles as $key => $value ) {
			WPSEO_Options::set( $key, $value );
		}
		WPSEO_Options::set( 'show_onboarding_notice', false );
	}

	foreach ( tidewell_page_seo() as $key => $seo ) {
		$id = (int) get_option( 'tidewell_page_' . $key );
		if ( ! $id ) {
			continue;
		}
		update_post_meta( $id, '_yoast_wpseo_title', $seo['title'] );
		update_post_meta( $id, '_yoast_wpseo_metadesc', $seo['desc'] );
		if ( ! empty( $seo['kw'] ) ) {
			update_post_meta( $id, '_yoast_wpseo_focuskw', $seo['kw'] );
		}
	}
}

function tidewell_page_seo() {
	$seo = [
		'home'      => [ 'title' => 'House Cleaning in Portland, OR | Tidewell Cleaning Co.', 'desc' => 'Eco-friendly home and office cleaning in Portland, Lake Oswego and Beaverton. Background-checked teams, upfront pricing and a 24-hour re-clean guarantee.', 'kw' => 'cleaning service portland' ],
		'services'  => [ 'title' => 'Cleaning Services in Portland, OR | Tidewell Cleaning Co.', 'desc' => 'House cleaning, deep cleaning, move-out and office cleaning across the Portland metro. Compare services and book online.' ],
		'pricing'   => [ 'title' => 'Cleaning Prices & Packages | Tidewell Cleaning Co. Portland', 'desc' => 'Upfront prices for house, deep, move-out and office cleaning in Portland. Book and pay after your clean, or buy a gift card.' ],
		'portfolio' => [ 'title' => 'Before & After Cleaning Projects in Portland | Tidewell', 'desc' => 'See real-world kitchens, bathrooms, move-outs and offices our Portland team has cleaned, with before and after photos.' ],
		'about'     => [ 'title' => 'About Tidewell | Local Portland Cleaning Company', 'desc' => 'Meet the small Portland team behind Tidewell Cleaning Co.: fair pay, plant-based products and a re-clean guarantee on every visit.' ],
		'faq'       => [ 'title' => 'Cleaning Service FAQ | Tidewell Cleaning Co. Portland', 'desc' => 'Answers to common questions about booking, pricing, products, pets and our satisfaction guarantee.' ],
		'contact'   => [ 'title' => 'Contact Tidewell Cleaning Co. | Portland, OR', 'desc' => 'Call (503) 555-0142, email us or send a message. Our Portland office is open Monday to Saturday.' ],
		'book'      => [ 'title' => 'Book a Cleaning in Portland | Tidewell Cleaning Co.', 'desc' => 'Request a house, deep, move-out or office clean in under a minute. We confirm every booking within one business day.' ],
	];
	foreach ( tidewell_services() as $key => $service ) {
		$seo[ 'service_' . $key ] = [ 'title' => $service['seo_title'], 'desc' => $service['seo_desc'], 'kw' => $service['focus_kw'] ];
	}
	return $seo;
}
