<?php
/**
 * Small helpers that produce Elementor container/widget data.
 * Pages built with them stay fully editable in the Elementor editor.
 */

defined( 'ABSPATH' ) || exit;

final class Tidewell_El {
	private static $salt = '';
	private static $n    = 0;

	public static function reset( $salt ) {
		self::$salt = $salt;
		self::$n    = 0;
	}

	public static function id() {
		return substr( md5( self::$salt . '-' . ( ++self::$n ) ), 0, 7 );
	}
}

function tw_sp( $top, $right, $bottom, $left ) {
	return [ 'unit' => 'px', 'top' => (string) $top, 'right' => (string) $right, 'bottom' => (string) $bottom, 'left' => (string) $left, 'isLinked' => false ];
}

function tw_gap( $px ) {
	return [ 'unit' => 'px', 'column' => (string) $px, 'row' => (string) $px, 'isLinked' => true, 'size' => $px ];
}

function tw_w( $type, array $settings = [] ) {
	return [ 'id' => Tidewell_El::id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => array_filter( $settings, fn( $v ) => '' !== $v ), 'elements' => [] ];
}

function tw_c( array $children, array $settings = [], $inner = true ) {
	return [ 'id' => Tidewell_El::id(), 'elType' => 'container', 'isInner' => $inner, 'settings' => array_filter( $settings, fn( $v ) => '' !== $v ), 'elements' => array_values( array_filter( $children ) ) ];
}

/** Top-level boxed section. */
function tw_section( array $children, $class = '', array $extra = [] ) {
	return tw_c( $children, array_merge( [
		'content_width'  => 'boxed',
		'flex_direction' => 'column',
		'flex_gap'       => tw_gap( 24 ),
		'html_tag'       => 'section',
		'padding'        => tw_sp( 88, 24, 88, 24 ),
		'padding_mobile' => tw_sp( 56, 20, 56, 20 ),
		'css_classes'    => trim( 'tw-section ' . $class ),
	], $extra ), false );
}

/** Vertical stack inside a section. */
function tw_stack( array $children, $class = '', array $extra = [] ) {
	return tw_c( $children, array_merge( [
		'content_width'  => 'full',
		'flex_direction' => 'column',
		'flex_gap'       => tw_gap( 16 ),
		'padding'        => tw_sp( 0, 0, 0, 0 ),
		'css_classes'    => $class,
	], $extra ) );
}

/** Horizontal wrapping row (button groups, badges). */
function tw_row( array $children, $class = '', array $extra = [] ) {
	return tw_c( $children, array_merge( [
		'content_width'    => 'full',
		'flex_direction'   => 'row',
		'flex_wrap'        => 'wrap',
		'flex_gap'         => tw_gap( 12 ),
		'flex_align_items' => 'center',
		'padding'          => tw_sp( 0, 0, 0, 0 ),
		'css_classes'      => $class,
	], $extra ) );
}

/**
 * Responsive grid. $cols is a column count, or a custom template such as "1.1fr 1fr".
 */
function tw_grid( array $children, $cols, $class = '', array $extra = [] ) {
	$columns = is_int( $cols ) ? [ 'unit' => 'fr', 'size' => $cols, 'sizes' => [] ] : [ 'unit' => 'custom', 'size' => $cols, 'sizes' => [] ];
	return tw_c( $children, array_merge( [
		'container_type'           => 'grid',
		'content_width'            => 'full',
		'grid_columns_grid'        => $columns,
		'grid_columns_grid_tablet' => [ 'unit' => 'fr', 'size' => is_int( $cols ) ? min( 2, $cols ) : 1, 'sizes' => [] ],
		'grid_columns_grid_mobile' => [ 'unit' => 'fr', 'size' => 1, 'sizes' => [] ],
		'grid_rows_grid'           => [ 'unit' => 'custom', 'size' => 'auto', 'sizes' => [] ],
		'grid_gaps'                => tw_gap( 28 ),
		'grid_align_items'         => 'stretch',
		'padding'                  => tw_sp( 0, 0, 0, 0 ),
		'css_classes'              => $class,
	], $extra ) );
}

function tw_heading( $text, $tag = 'h2', $class = '', array $extra = [] ) {
	return tw_w( 'heading', array_merge( [ 'title' => $text, 'header_size' => $tag, '_css_classes' => $class ], $extra ) );
}

function tw_eyebrow( $text ) {
	return tw_heading( $text, 'p', 'tw-eyebrow' );
}

function tw_text( $html, $class = '' ) {
	return tw_w( 'text-editor', [ 'editor' => $html, '_css_classes' => $class ] );
}

function tw_button( $text, $url, $class = '', array $extra = [] ) {
	return tw_w( 'button', array_merge( [
		'text'         => $text,
		'link'         => [ 'url' => $url, 'is_external' => '', 'nofollow' => '' ],
		'size'         => 'md',
		'_css_classes' => $class,
	], $extra ) );
}

function tw_buttons( array $buttons ) {
	return tw_row( array_map( fn( $b ) => tw_button( $b[0], $b[1], $b[2] ?? '' ), $buttons ), 'tw-actions' );
}

function tw_image_setting( $file ) {
	$id = tidewell_image_id( $file );
	return [ 'id' => $id, 'url' => (string) wp_get_attachment_url( $id ), 'alt' => '', 'source' => 'library' ];
}

function tw_image( $file, $context = 'content', $class = '' ) {
	return tw_w( 'image', [ 'image' => tw_image_setting( $file ), 'image_size' => tidewell_image_size( $context ), '_css_classes' => trim( 'tw-image ' . $class ) ] );
}

function tw_icon( $value ) {
	return [ 'value' => $value, 'library' => str_starts_with( $value, 'far ' ) ? 'fa-regular' : 'fa-solid' ];
}

function tw_icon_list( array $items, $icon = 'fas fa-check', $class = '', $inline = false ) {
	return tw_w( 'icon-list', [
		'view'         => $inline ? 'inline' : 'traditional',
		'icon_list'    => array_map( fn( $item ) => [
			'_id'           => Tidewell_El::id(),
			'text'          => is_array( $item ) ? $item['text'] : $item,
			'selected_icon' => tw_icon( is_array( $item ) && isset( $item['icon'] ) ? $item['icon'] : $icon ),
			'link'          => [ 'url' => is_array( $item ) ? ( $item['url'] ?? '' ) : '', 'is_external' => '', 'nofollow' => '' ],
		], $items ),
		'_css_classes' => trim( 'tw-list ' . $class ),
	] );
}

function tw_icon_box( $icon, $title, $desc, $class = '' ) {
	return tw_w( 'icon-box', [
		'selected_icon'    => tw_icon( $icon ),
		'view'             => 'stacked',
		'shape'            => 'circle',
		'title_text'       => $title,
		'description_text' => $desc,
		'title_size'       => 'h3',
		'position'         => 'top',
		'text_align'       => 'left',
		'_css_classes'     => trim( 'tw-icon-box ' . $class ),
	] );
}

function tw_image_box( $file, $title, $desc, $url, $class = '' ) {
	return tw_w( 'image-box', [
		'image'            => tw_image_setting( $file ),
		'thumbnail_size'   => tidewell_image_size( 'card' ),
		'title_text'       => $title,
		'description_text' => $desc,
		'link'             => [ 'url' => $url, 'is_external' => '', 'nofollow' => '' ],
		'title_size'       => 'h3',
		'position'         => 'top',
		'text_align'       => 'left',
		'_css_classes'     => trim( 'tw-image-box ' . $class ),
	] );
}

function tw_testimonial( array $t ) {
	return tw_w( 'testimonial', [
		'testimonial_content'   => $t['text'],
		'testimonial_name'      => $t['name'],
		'testimonial_job'       => $t['meta'],
		// An empty image stops Elementor from rendering its grey placeholder avatar.
		'testimonial_image'     => [ 'url' => '', 'id' => '' ],
		'testimonial_alignment' => 'left',
		'_css_classes'          => 'tw-testimonial',
	] );
}

function tw_shortcode( $shortcode, $class = '' ) {
	return tw_w( 'shortcode', [ 'shortcode' => $shortcode, '_css_classes' => $class ] );
}

function tw_accordion( array $faqs, $schema = true ) {
	return tw_w( 'accordion', [
		'tabs'                 => array_map( fn( $f ) => [ '_id' => Tidewell_El::id(), 'tab_title' => $f['q'], 'tab_content' => '<p>' . esc_html( $f['a'] ) . '</p>' ], $faqs ),
		'title_html_tag'       => 'h3',
		'faq_schema'           => $schema ? 'yes' : '',
		'selected_icon'        => tw_icon( 'fas fa-plus' ),
		'selected_active_icon' => tw_icon( 'fas fa-minus' ),
		'icon_align'           => 'right',
		'_css_classes'         => 'tw-accordion',
	] );
}

function tw_counter( $number, $suffix, $title ) {
	return tw_w( 'counter', [
		'starting_number' => 0,
		'ending_number'   => $number,
		'suffix'          => $suffix,
		'title'           => $title,
		'duration'        => 1200,
		'_css_classes'    => 'tw-counter',
	] );
}

function tw_map() {
	return tw_w( 'google_maps', [
		'address'      => tidewell_address_line(),
		'zoom'         => [ 'unit' => 'px', 'size' => 13, 'sizes' => [] ],
		'height'       => [ 'unit' => 'px', 'size' => 360, 'sizes' => [] ],
		'_css_classes' => 'tw-map',
	] );
}
