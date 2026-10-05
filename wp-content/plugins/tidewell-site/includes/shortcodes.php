<?php
/**
 * Shortcodes used inside Elementor pages so business details live in one place.
 */

defined( 'ABSPATH' ) || exit;

function tidewell_format_time( $time ) {
	return gmdate( 'g:i a', strtotime( '1970-01-01 ' . $time . ' UTC' ) );
}

/** [tidewell_info field="phone|email|address|phone_link|email_link"] */
add_shortcode( 'tidewell_info', function ( $atts ) {
	$atts = shortcode_atts( [ 'field' => 'phone' ], $atts, 'tidewell_info' );
	$b    = tidewell_business();
	switch ( $atts['field'] ) {
		case 'phone_link':
			return sprintf( '<a href="tel:%s">%s</a>', esc_attr( $b['phone_e164'] ), esc_html( $b['phone'] ) );
		case 'email_link':
			return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $b['email'] ) );
		case 'address':
			return esc_html( tidewell_address_line() );
		default:
			return esc_html( $b[ $atts['field'] ] ?? '' );
	}
} );

/** [tidewell_contact] */
add_shortcode( 'tidewell_contact', function () {
	$b = tidewell_business();
	return sprintf(
		'<dl class="tw-contact"><div><dt>Phone</dt><dd><a href="tel:%1$s">%2$s</a></dd></div><div><dt>Email</dt><dd><a href="mailto:%3$s">%3$s</a></dd></div><div><dt>Office</dt><dd>%4$s</dd></div></dl>',
		esc_attr( $b['phone_e164'] ),
		esc_html( $b['phone'] ),
		esc_html( $b['email'] ),
		esc_html( tidewell_address_line() )
	);
} );

/** [tidewell_hours] */
add_shortcode( 'tidewell_hours', function () {
	$rows = '';
	foreach ( tidewell_business()['hours'] as $row ) {
		$rows .= sprintf(
			'<div class="tw-hours__row"><dt>%s</dt><dd>%s – %s</dd></div>',
			esc_html( $row['label'] ),
			esc_html( tidewell_format_time( $row['opens'] ) ),
			esc_html( tidewell_format_time( $row['closes'] ) )
		);
	}
	$rows .= '<div class="tw-hours__row"><dt>Sunday</dt><dd>Closed</dd></div>';
	return '<dl class="tw-hours">' . $rows . '</dl>';
} );

/** [tidewell_areas type="cities|neighborhoods"] */
add_shortcode( 'tidewell_areas', function ( $atts ) {
	$atts  = shortcode_atts( [ 'type' => 'cities' ], $atts, 'tidewell_areas' );
	$b     = tidewell_business();
	$items = 'neighborhoods' === $atts['type'] ? $b['neighborhoods'] : $b['areas'];
	return '<ul class="tw-areas">' . implode( '', array_map( fn( $i ) => '<li>' . esc_html( $i ) . '</li>', $items ) ) . '</ul>';
} );

/** [tidewell_recent_posts count="3"] */
add_shortcode( 'tidewell_recent_posts', function ( $atts ) {
	$atts  = shortcode_atts( [ 'count' => 3 ], $atts, 'tidewell_recent_posts' );
	$posts = get_posts( [ 'numberposts' => (int) $atts['count'], 'post_status' => 'publish' ] );
	if ( ! $posts ) {
		return '';
	}
	$out = '<ul class="tw-posts" role="list">';
	foreach ( $posts as $post ) {
		$cats = get_the_category( $post->ID );
		$out .= sprintf(
			'<li class="tw-post-card"><a class="tw-post-card__media" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a><div class="tw-post-card__body"><p class="tw-post-card__meta">%3$s · <time datetime="%4$s">%5$s</time></p><h3 class="tw-post-card__title"><a href="%1$s">%6$s</a></h3><p class="tw-post-card__excerpt">%7$s</p></div></li>',
			esc_url( get_permalink( $post ) ),
			get_the_post_thumbnail( $post, tidewell_image_size( 'card' ), [ 'alt' => '', 'sizes' => '(min-width: 1024px) 380px, 100vw' ] ),
			esc_html( $cats ? $cats[0]->name : 'Blog' ),
			esc_attr( get_the_date( 'c', $post ) ),
			esc_html( get_the_date( '', $post ) ),
			esc_html( get_the_title( $post ) ),
			esc_html( get_the_excerpt( $post ) )
		);
	}
	return $out . '</ul>';
} );
