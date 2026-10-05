<?php
/**
 * Local SEO structured data. Extends Yoast's schema graph when Yoast is active,
 * otherwise prints a standalone LocalBusiness block on the front page.
 * FAQ pages get FAQPage markup from Elementor's accordion "FAQ Schema" option.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_local_business_fields() {
	$b = tidewell_business();
	return [
		'name'                      => $b['name'],
		'url'                       => home_url( '/' ),
		'telephone'                 => $b['phone_e164'],
		'email'                     => $b['email'],
		'priceRange'                => $b['price_range'],
		'image'                     => wp_get_attachment_url( (int) ( get_option( 'tidewell_images', [] )['hero.jpg'] ?? 0 ) ) ?: null,
		'foundingDate'              => (string) $b['founded'],
		'address'                   => [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $b['street'],
			'addressLocality' => $b['city'],
			'addressRegion'   => $b['region'],
			'postalCode'      => $b['postal_code'],
			'addressCountry'  => $b['country'],
		],
		'geo'                       => [
			'@type'     => 'GeoCoordinates',
			'latitude'  => $b['lat'],
			'longitude' => $b['lng'],
		],
		'areaServed'                => array_map( fn( $city ) => [ '@type' => 'City', 'name' => $city . ', ' . $b['region'] ], $b['areas'] ),
		'openingHoursSpecification' => array_map( fn( $row ) => [
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $row['days'],
			'opens'     => $row['opens'],
			'closes'    => $row['closes'],
		], $b['hours'] ),
	];
}

function tidewell_current_service_key() {
	if ( ! is_page() ) {
		return '';
	}
	foreach ( array_keys( tidewell_services() ) as $key ) {
		if ( (int) get_option( 'tidewell_page_service_' . $key ) === get_queried_object_id() ) {
			return $key;
		}
	}
	return '';
}

function tidewell_service_schema( $key, $provider ) {
	$service = tidewell_services()[ $key ];
	$b       = tidewell_business();
	return [
		'@type'       => 'Service',
		'@id'         => get_permalink() . '#service',
		'name'        => $service['title'],
		'serviceType' => $service['title'],
		'description' => $service['summary'],
		'url'         => get_permalink(),
		'provider'    => $provider,
		'areaServed'  => array_map( fn( $city ) => [ '@type' => 'City', 'name' => $city . ', ' . $b['region'] ], $b['areas'] ),
		'offers'      => [
			'@type'         => 'Offer',
			'priceCurrency' => 'USD',
			'price'         => $service['price_from'],
			'description'   => 'Starting price',
		],
	];
}

add_filter( 'wpseo_schema_organization', function ( $data ) {
	$data['@type'] = [ 'Organization', 'LocalBusiness' ];
	return array_merge( $data, array_filter( tidewell_local_business_fields() ) );
} );

add_filter( 'wpseo_schema_graph', function ( $graph ) {
	$key = tidewell_current_service_key();
	if ( $key ) {
		$graph[] = tidewell_service_schema( $key, [ '@id' => home_url( '/#organization' ) ] );
	}
	return $graph;
} );

add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$graph = [];
	if ( is_front_page() ) {
		$graph[] = [ '@type' => 'LocalBusiness', '@id' => home_url( '/#organization' ) ] + array_filter( tidewell_local_business_fields() );
	}
	$key = tidewell_current_service_key();
	if ( $key ) {
		$graph[] = tidewell_service_schema( $key, [ '@type' => 'LocalBusiness', 'name' => tidewell_business()['name'] ] );
	}
	if ( $graph ) {
		printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_SLASHES ) );
	}
} );
