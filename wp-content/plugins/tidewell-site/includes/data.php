<?php
/**
 * Business details and demo content. Tidewell is a fictional company.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_business_defaults() {
	return [
		'name'        => 'Tidewell Cleaning Co.',
		'short_name'  => 'Tidewell',
		'tagline'     => 'Home cleaning in Portland, done right.',
		'phone'       => '(503) 555-0142',
		'email'       => 'hello@tidewell.example',
		'street'      => '2150 SE Division St, Suite 4',
		'city'        => 'Portland',
		'region'      => 'OR',
		'postal_code' => '97202',
		'country'     => 'US',
		'lat'         => 45.5048,
		'lng'         => -122.6430,
		'price_range' => '$$',
		'founded'     => 2017,
		'weekday_opens'  => '08:00',
		'weekday_closes' => '18:00',
		'sat_opens'      => '09:00',
		'sat_closes'     => '15:00',
		'areas'       => [ 'Portland', 'Lake Oswego', 'Beaverton', 'Milwaukie', 'Tigard', 'West Linn' ],
		'neighborhoods' => [ 'Sellwood', 'Hawthorne', 'Pearl District', 'Alberta Arts', 'Laurelhurst', 'St. Johns', 'Hillsdale', 'Irvington' ],
	];
}

const TIDEWELL_EDITABLE_FIELDS = [
	'phone'          => 'Phone',
	'email'          => 'Email',
	'street'         => 'Street address',
	'city'           => 'City',
	'region'         => 'State',
	'postal_code'    => 'ZIP code',
	'weekday_opens'  => 'Mon–Fri opens',
	'weekday_closes' => 'Mon–Fri closes',
	'sat_opens'      => 'Saturday opens',
	'sat_closes'     => 'Saturday closes',
];

/**
 * Business details, with the fields editable under Tidewell > Business details applied on top.
 */
function tidewell_business() {
	$b     = tidewell_business_defaults();
	$saved = get_option( 'tidewell_business', [] );
	foreach ( array_keys( TIDEWELL_EDITABLE_FIELDS ) as $key ) {
		if ( ! empty( $saved[ $key ] ) ) {
			$b[ $key ] = $saved[ $key ];
		}
	}
	$b['phone_e164'] = '+1' . substr( preg_replace( '/\D/', '', $b['phone'] ), -10 );
	$b['hours']      = [
		[ 'days' => [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ], 'label' => 'Mon – Fri', 'opens' => $b['weekday_opens'], 'closes' => $b['weekday_closes'] ],
		[ 'days' => [ 'Saturday' ], 'label' => 'Saturday', 'opens' => $b['sat_opens'], 'closes' => $b['sat_closes'] ],
	];
	return $b;
}

function tidewell_address_line() {
	$b = tidewell_business();
	return sprintf( '%s, %s, %s %s', $b['street'], $b['city'], $b['region'], $b['postal_code'] );
}

function tidewell_services() {
	return [
		'standard' => [
			'title'      => 'Standard House Cleaning',
			'menu_title' => 'House Cleaning',
			'slug'       => 'house-cleaning-portland',
			'image'      => 'service-standard.jpg',
			'price_from' => 119,
			'duration'   => '2–4 hours',
			'icon'       => 'fas fa-broom',
			'summary'    => 'Weekly, bi-weekly or monthly visits that keep your home consistently fresh: kitchens, bathrooms, floors and every surface in between.',
			'intro'      => 'Our standard clean is the backbone of what we do. The same small team visits on a schedule that suits you, follows a 52-point checklist and leaves your Portland home ready for the week ahead.',
			'includes'   => [
				'Kitchen counters, sink, stovetop and appliance fronts',
				'Bathrooms scrubbed and disinfected: toilet, tub, shower and mirrors',
				'Dusting of all reachable surfaces, sills and baseboards',
				'Vacuuming carpets and rugs, mopping hard floors',
				'Beds made and linens changed (if left out)',
				'Trash and recycling taken out',
			],
			'ideal_for'  => 'Busy households, families with pets, anyone who would rather spend Saturday outside.',
			'faqs'       => [
				[ 'q' => 'Do I need to be home during the clean?', 'a' => 'No. Most clients give us a door code or leave a key in a lockbox. Every team member is background-checked, and we text you when we arrive and leave.' ],
				[ 'q' => 'Will I get the same cleaners each time?', 'a' => 'Yes. Recurring clients are matched with a regular two-person team so they learn your home and your preferences.' ],
			],
			'seo_title'  => 'House Cleaning in Portland, OR | Weekly & Bi-weekly | Tidewell',
			'seo_desc'   => 'Reliable recurring house cleaning in Portland, Lake Oswego and Beaverton. Background-checked teams, eco-friendly supplies, from $119. Book online in 60 seconds.',
			'focus_kw'   => 'house cleaning portland',
		],
		'deep' => [
			'title'      => 'Deep Cleaning',
			'menu_title' => 'Deep Cleaning',
			'slug'       => 'deep-cleaning-portland',
			'image'      => 'service-deep.jpg',
			'price_from' => 219,
			'duration'   => '4–7 hours',
			'icon'       => 'fas fa-spray-can',
			'summary'    => 'A top-to-bottom reset for homes that need extra attention: inside appliances, grout, cabinets, baseboards and built-up grime.',
			'intro'      => 'A deep clean goes where a regular clean does not. We recommend one before starting recurring service, at the change of seasons, or before hosting family.',
			'includes'   => [
				'Everything in the standard clean',
				'Inside oven, microwave and refrigerator',
				'Grout and tile scrubbing, hard-water stain removal',
				'Cabinet fronts, door frames, light switches and vents',
				'Baseboards hand-wiped throughout',
				'Interior windows and window tracks',
			],
			'ideal_for'  => 'First-time clients, spring cleaning, homes being prepared for guests or for sale.',
			'faqs'       => [
				[ 'q' => 'How long does a deep clean take?', 'a' => 'A 2-bedroom home usually takes a two-person team 4–5 hours. We confirm the estimate after you tell us about your home.' ],
				[ 'q' => 'Do you bring your own supplies?', 'a' => 'Yes. We bring everything, including a HEPA vacuum and plant-based products. Let us know if you prefer we use your own.' ],
			],
			'seo_title'  => 'Deep Cleaning Services in Portland, OR | Tidewell Cleaning Co.',
			'seo_desc'   => 'Top-to-bottom deep cleaning in Portland: inside appliances, grout, baseboards and more. Eco-friendly products, satisfaction guarantee, from $219.',
			'focus_kw'   => 'deep cleaning portland',
		],
		'move' => [
			'title'      => 'Move-In / Move-Out Cleaning',
			'menu_title' => 'Move-Out Cleaning',
			'slug'       => 'move-out-cleaning-portland',
			'image'      => 'service-move.jpg',
			'price_from' => 289,
			'duration'   => '5–8 hours',
			'icon'       => 'fas fa-box-open',
			'summary'    => 'Empty-home cleaning that landlords and property managers sign off on, so you can focus on the move.',
			'intro'      => 'Moving is stressful enough. Our move-out clean follows the checklist most Portland property managers use, and we come back within 48 hours if anything is flagged at inspection.',
			'includes'   => [
				'Inside all cabinets, drawers and closets',
				'Inside oven, refrigerator and dishwasher',
				'Walls spot-cleaned, scuffs removed where possible',
				'Bathrooms descaled and disinfected',
				'Floors vacuumed and mopped, including under where furniture stood',
				'Garage and balcony sweep on request',
			],
			'ideal_for'  => 'Renters moving out, buyers moving in, landlords turning over a unit.',
			'faqs'       => [
				[ 'q' => 'Does the home need to be empty?', 'a' => 'Ideally yes. An empty home lets us reach every corner. If a few items remain, tell us when you book so we can plan around them.' ],
				[ 'q' => 'What if my landlord finds something we missed?', 'a' => 'Send us the inspection notes within 48 hours and we will return to fix it at no charge.' ],
			],
			'seo_title'  => 'Move-Out Cleaning in Portland, OR | Deposit-Ready | Tidewell',
			'seo_desc'   => 'Move-in and move-out cleaning in Portland and nearby suburbs. Landlord-ready checklist, 48-hour re-clean guarantee, from $289.',
			'focus_kw'   => 'move out cleaning portland',
		],
		'office' => [
			'title'      => 'Office Cleaning',
			'menu_title' => 'Office Cleaning',
			'slug'       => 'office-cleaning-portland',
			'image'      => 'service-office.jpg',
			'price_from' => 199,
			'duration'   => 'After hours',
			'icon'       => 'fas fa-building',
			'summary'    => 'After-hours cleaning for small offices and studios up to 5,000 sq ft, on a schedule that never interrupts your team.',
			'intro'      => 'We clean small offices, studios and clinics across the Portland metro after hours, so your team walks into a clean space every morning.',
			'includes'   => [
				'Desks, shared tables and high-touch points disinfected',
				'Kitchenette and break room cleaned, dishwasher loaded',
				'Restrooms cleaned and restocked',
				'Trash, recycling and compost emptied',
				'Floors vacuumed and mopped',
				'Monthly glass and baseboard detail',
			],
			'ideal_for'  => 'Creative studios, small professional offices, co-working spaces.',
			'faqs'       => [
				[ 'q' => 'Are you insured for commercial spaces?', 'a' => 'Yes. We carry general liability insurance and can add your building as an additional insured.' ],
				[ 'q' => 'Can you work around our schedule?', 'a' => 'We clean evenings and weekends, and we can work with your building manager on access.' ],
			],
			'seo_title'  => 'Office Cleaning in Portland, OR | Small Office & Studio | Tidewell',
			'seo_desc'   => 'After-hours office cleaning for small offices and studios in Portland. Insured, background-checked crews, flexible schedules, from $199 per visit.',
			'focus_kw'   => 'office cleaning portland',
		],
	];
}

function tidewell_project_types() {
	return [
		'kitchens'      => 'Kitchens',
		'bathrooms'     => 'Bathrooms',
		'move-in-out'   => 'Move-in / out',
		'living-spaces' => 'Living spaces',
		'offices'       => 'Offices',
	];
}

function tidewell_projects() {
	return [
		[
			'title'    => 'Craftsman kitchen deep clean in Sellwood',
			'slug'     => 'sellwood-kitchen-deep-clean',
			'type'     => 'kitchens',
			'service'  => 'deep',
			'location' => 'Sellwood, Portland',
			'duration' => '5 hours, 2 cleaners',
			'after'    => 'project-kitchen-after.jpg',
			'before'   => 'project-kitchen-before.jpg',
			'gallery'  => [ 'service-standard.jpg' ],
			'summary'  => 'A busy family kitchen brought back to its original 1920s charm after a long renovation elsewhere in the house.',
			'body'     => [
				'The homeowners had just finished a bathroom remodel and construction dust had settled into every corner of their kitchen. The butcher block counters were greasy, and the farmhouse sink had picked up stains from months of paint-brush rinsing.',
				'We degreased the range and backsplash, oiled the butcher block, and lifted the sink stains with a gentle oxygen cleaner that is safe for fireclay. Cabinet fronts were hand-wiped and the original fir floor was cleaned with a pH-neutral wood cleaner.',
			],
		],
		[
			'title'    => 'Pearl District condo move-out',
			'slug'     => 'pearl-district-condo-move-out',
			'type'     => 'move-in-out',
			'service'  => 'move',
			'location' => 'Pearl District, Portland',
			'duration' => '6 hours, 3 cleaners',
			'after'    => 'project-condo-after.jpg',
			'before'   => 'project-condo-before.jpg',
			'gallery'  => [ 'service-move.jpg' ],
			'summary'  => 'A 1,100 sq ft condo left inspection-ready on a tight same-week deadline.',
			'body'     => [
				'Our client was relocating for work and needed the unit handed back to the property manager two days after the movers left. Packing debris, scuffed concrete floors and streaky floor-to-ceiling windows were the main concerns.',
				'A three-person team cleared the debris, machine-scrubbed the polished concrete, cleaned all interior glass and detailed the kitchen inside and out. The unit passed inspection on the first walkthrough and the full deposit was returned.',
			],
		],
		[
			'title'    => 'Vintage bathroom refresh in Hawthorne',
			'slug'     => 'hawthorne-vintage-bathroom-refresh',
			'type'     => 'bathrooms',
			'service'  => 'deep',
			'location' => 'Hawthorne, Portland',
			'duration' => '3 hours, 2 cleaners',
			'after'    => 'project-bathroom-after.jpg',
			'before'   => 'project-bathroom-before.jpg',
			'gallery'  => [ 'service-deep.jpg' ],
			'summary'  => 'Hard-water stains and dull grout lifted from a clawfoot tub and original hexagon tile, without harsh chemicals.',
			'body'     => [
				'Portland water is soft, but years of soap scum had left the cast-iron clawfoot tub dull and the white hex tile grout grey. The owner wanted it clean without anything that could etch the vintage finishes.',
				'We used a citric-acid descaler on the tub and fixtures, steam-cleaned the grout, and polished the chrome by hand. The shower curtain and bath mat were laundered and the mirror and tile wall were left streak-free.',
			],
		],
		[
			'title'    => 'Weekly studio office cleaning in Alberta Arts',
			'slug'     => 'alberta-arts-studio-office',
			'type'     => 'offices',
			'service'  => 'office',
			'location' => 'Alberta Arts District, Portland',
			'duration' => 'Weekly, 2 hours',
			'after'    => 'project-office.jpg',
			'before'   => '',
			'gallery'  => [ 'service-office.jpg' ],
			'summary'  => 'A design studio of twelve people that now starts every Monday in a clean, quiet space.',
			'body'     => [
				'This design studio shares a converted brick warehouse with two other businesses. They needed a team that could work on Friday evenings, respect client work left on desks and keep the shared kitchen under control.',
				'We set up a weekly checklist with the studio manager, clean around anything marked "do not touch", and do a deeper glass and baseboard detail on the first Friday of each month.',
			],
		],
		[
			'title'    => 'Lake Oswego living room before a family reunion',
			'slug'     => 'lake-oswego-living-room',
			'type'     => 'living-spaces',
			'service'  => 'deep',
			'location' => 'Lake Oswego',
			'duration' => '4 hours, 2 cleaners',
			'after'    => 'project-living.jpg',
			'before'   => '',
			'gallery'  => [ 'hero.jpg' ],
			'summary'  => 'A vaulted-ceiling great room ready for twenty guests, including the high windows nobody could reach.',
			'body'     => [
				'With relatives flying in for a reunion, the homeowners asked us to focus on the shared spaces: the great room, entry and guest bath. The tall windows and ceiling fan had not been cleaned since the house was built.',
				'We used extension poles and a wet-dust method for the high surfaces, vacuumed and groomed the wool rug, and cleaned upholstery spots with a low-moisture cleaner so the sofa was dry before guests arrived.',
			],
		],
		[
			'title'    => 'Laurelhurst bedroom and closet reset',
			'slug'     => 'laurelhurst-bedroom-closet-reset',
			'type'     => 'living-spaces',
			'service'  => 'standard',
			'location' => 'Laurelhurst, Portland',
			'duration' => '3 hours, 2 cleaners',
			'after'    => 'project-bedroom.jpg',
			'before'   => '',
			'gallery'  => [ 'about-team.jpg' ],
			'summary'  => 'A calm, dust-free primary bedroom and an organized walk-in closet for a client with allergies.',
			'body'     => [
				'Dust was triggering our client\'s allergies, so we focused on removing it rather than moving it around: HEPA vacuuming, microfiber wet-dusting from top to bottom, and washing the bedding on hot.',
				'In the closet we wiped every shelf, vacuumed the floor and baskets, and grouped clothing by type so the space stays easy to keep clean between visits.',
			],
		],
	];
}

function tidewell_faqs() {
	return [
		[ 'q' => 'Which areas do you serve?', 'a' => 'We clean homes and offices across Portland and nearby cities including Lake Oswego, Beaverton, Milwaukie, Tigard and West Linn. Not sure if you are in range? Enter your ZIP code on the booking form and we will confirm.' ],
		[ 'q' => 'How do I book a cleaning?', 'a' => 'Use the online booking form, buy a package from our pricing page, or call us at (503) 555-0142. We confirm every booking by email within one business day.' ],
		[ 'q' => 'Are your cleaners background-checked and insured?', 'a' => 'Yes. Every team member passes a background check before their first clean, and Tidewell carries general liability insurance and bonding.' ],
		[ 'q' => 'What products do you use?', 'a' => 'We use plant-based, fragrance-free products by default and a HEPA-filter vacuum. If you prefer a specific product, we are happy to use yours.' ],
		[ 'q' => 'Do I need to be home?', 'a' => 'No. Many clients leave a key in a lockbox or share a door code. We text you when we arrive and when we leave.' ],
		[ 'q' => 'What if I am not happy with the clean?', 'a' => 'Let us know within 24 hours and we will come back to re-clean the areas you are not satisfied with, at no charge.' ],
		[ 'q' => 'How do I reschedule or cancel?', 'a' => 'Reply to your confirmation email or call us at least 48 hours before your appointment. Cancellations with less notice may incur a $40 fee.' ],
		[ 'q' => 'Do you clean homes with pets?', 'a' => 'Absolutely. Just let us know about your pets when you book, and whether they should be kept in a particular room.' ],
	];
}

function tidewell_testimonials() {
	return [
		[ 'name' => 'Megan R.', 'meta' => 'Bi-weekly client, Sellwood', 'image' => '', 'text' => 'Same two people every visit, they remember how we like things and they text when they are done. Coming home on cleaning day is the best part of my week.' ],
		[ 'name' => 'Daniel K.', 'meta' => 'Move-out clean, Pearl District', 'image' => '', 'text' => 'Booked on a Monday, cleaned on Wednesday, full deposit back on Friday. The before and after photos they sent made the handover with my property manager painless.' ],
		[ 'name' => 'Priya S.', 'meta' => 'Office client, Alberta Arts', 'image' => '', 'text' => 'They work around our client projects and never move anything they should not. Our studio has never looked this good on a Monday morning.' ],
	];
}

function tidewell_posts() {
	return [
		[
			'title'    => 'How often should you deep clean? A seasonal checklist for Portland homes',
			'slug'     => 'how-often-deep-clean-portland',
			'image'    => 'service-deep.jpg',
			'category' => 'Cleaning tips',
			'excerpt'  => 'Our rule of thumb for deep cleaning in the Pacific Northwest, plus a printable checklist for each season.',
			'blocks'   => [
				[ 'p', 'Most homes do well with a deep clean two to four times a year, on top of regular cleaning. In Portland, the seasons give you natural reminders: wet winters bring mud and mildew, spring brings pollen, and summer is the easiest time to air everything out.' ],
				[ 'h2', 'Fall: get ready for the rainy season' ],
				[ 'ul', [ 'Clean window tracks and check seals before the rain sets in', 'Deep clean entry mats and the area around exterior doors', 'Vacuum and wipe heating vents before turning the furnace on' ] ],
				[ 'h2', 'Winter: fight moisture and mildew' ],
				[ 'ul', [ 'Scrub bathroom grout and caulk where mildew starts', 'Run and clean bathroom exhaust fans', 'Wash shower curtains and bath mats monthly' ] ],
				[ 'h2', 'Spring: pollen and the big reset' ],
				[ 'ul', [ 'Wash interior windows and screens', 'Wet-dust ceiling fans, light fixtures and high shelves', 'Clean inside the oven and refrigerator' ] ],
				[ 'h2', 'Summer: air it out' ],
				[ 'ul', [ 'Steam-clean or deep vacuum rugs and upholstery', 'Declutter closets and donate what you no longer use', 'Wipe baseboards and door frames throughout' ] ],
				[ 'p', 'Want us to handle it? Our <a href="{service:deep}">deep cleaning service</a> covers all of the above in a single visit.' ],
			],
		],
		[
			'title'    => 'Move-out cleaning checklist: what landlords actually check',
			'slug'     => 'move-out-cleaning-checklist',
			'image'    => 'service-move.jpg',
			'category' => 'Moving',
			'excerpt'  => 'The rooms and details property managers look at first during a move-out inspection, and how to prepare for each.',
			'blocks'   => [
				[ 'p', 'After hundreds of move-out cleans, we have noticed that property managers almost always check the same handful of things first. If these are spotless, the rest of the inspection usually goes smoothly.' ],
				[ 'h2', 'The kitchen comes first' ],
				[ 'p', 'Open the oven, the refrigerator and a few cabinets. Grease inside the oven and crumbs in drawers are the most common deductions we hear about. Pull the fridge out if you can and clean behind it.' ],
				[ 'h2', 'Bathrooms: scale and grout' ],
				[ 'p', 'Shower glass, faucets and grout lines show neglect quickly. A descaler and a stiff grout brush go a long way.' ],
				[ 'h2', 'Walls, floors and the little things' ],
				[ 'ul', [ 'Spot-clean scuffs and fingerprints near light switches', 'Vacuum and mop where furniture stood', 'Clean window sills and tracks', 'Replace burnt-out bulbs' ] ],
				[ 'p', 'Always take dated photos after cleaning. If there is a dispute, they are your best evidence. Our <a href="{service:move}">move-out clean</a> includes a photo report and a 48-hour re-clean guarantee.' ],
			],
		],
		[
			'title'    => 'Eco-friendly cleaning: what we use and what we skip',
			'slug'     => 'eco-friendly-cleaning-products',
			'image'    => 'service-standard.jpg',
			'category' => 'Behind the scenes',
			'excerpt'  => 'A look inside our supply caddy, and why we left bleach and heavy fragrances behind.',
			'blocks'   => [
				[ 'p', 'Clients often ask what is in the caddy our teams carry. The short answer: fewer products than you might think, and nothing that leaves a strong smell behind.' ],
				[ 'h2', 'What we use' ],
				[ 'ul', [ 'A plant-based all-purpose cleaner for most surfaces', 'Citric-acid descaler for faucets, glass and tile', 'Hydrogen-peroxide disinfectant for bathrooms and high-touch points', 'Color-coded microfiber cloths so bathroom cloths never touch the kitchen', 'A HEPA-filter vacuum that traps dust instead of blowing it around' ] ],
				[ 'h2', 'What we skip' ],
				[ 'p', 'We do not use chlorine bleach, ammonia or added fragrance. They are harder on surfaces, on pets and on people with allergies, and in our experience they are not needed for a thorough clean.' ],
				[ 'p', 'Have a product you love? Leave it out and we will use it instead. Ready to try us? <a href="{page:book}">Book your first clean</a>.' ],
			],
		],
	];
}

function tidewell_products() {
	$sizes = [ 'Studio / 1 bedroom', '2 bedrooms', '3 bedrooms', '4+ bedrooms' ];
	return [
		[
			'key'         => 'standard',
			'name'        => 'Standard House Clean',
			'image'       => 'service-standard.jpg',
			'short'       => 'Recurring-quality clean for kitchens, bathrooms, floors and surfaces. Choose your home size.',
			'description' => 'Our 52-point standard clean, performed by a background-checked two-person team with eco-friendly supplies. After checkout we will email you within one business day to confirm your date and time.',
			'attribute'   => 'Home size',
			'variations'  => array_combine( $sizes, [ 119, 149, 179, 219 ] ),
		],
		[
			'key'         => 'deep',
			'name'        => 'Deep Clean',
			'image'       => 'service-deep.jpg',
			'short'       => 'Top-to-bottom reset including inside appliances, grout and baseboards.',
			'description' => 'Everything in the standard clean plus inside the oven, microwave and fridge, grout scrubbing, cabinet fronts, baseboards and interior windows.',
			'attribute'   => 'Home size',
			'variations'  => array_combine( $sizes, [ 219, 269, 319, 389 ] ),
		],
		[
			'key'         => 'move',
			'name'        => 'Move-In / Move-Out Clean',
			'image'       => 'service-move.jpg',
			'short'       => 'Landlord-ready empty-home clean with a 48-hour re-clean guarantee.',
			'description' => 'Inside every cabinet, closet and appliance, walls spot-cleaned and floors detailed. Includes a photo report you can share with your property manager.',
			'attribute'   => 'Home size',
			'variations'  => array_combine( $sizes, [ 289, 349, 409, 479 ] ),
		],
		[
			'key'         => 'office',
			'name'        => 'Office Clean (up to 2,000 sq ft)',
			'image'       => 'service-office.jpg',
			'short'       => 'One after-hours visit for small offices and studios.',
			'description' => 'Desks and high-touch points disinfected, kitchenette and restrooms cleaned, trash emptied and floors vacuumed and mopped. Ask us about weekly rates.',
			'price'       => 199,
		],
		[
			'key'         => 'gift',
			'name'        => 'Tidewell Gift Card',
			'image'       => 'about-team.jpg',
			'short'       => 'Give someone a clean home. Delivered by email, never expires.',
			'description' => 'A thoughtful gift for new parents, new homeowners or anyone who deserves a break. We email the gift card code to you after purchase so you can forward it or print it.',
			'attribute'   => 'Amount',
			'variations'  => [ '$50' => 50, '$100' => 100, '$200' => 200 ],
		],
	];
}
