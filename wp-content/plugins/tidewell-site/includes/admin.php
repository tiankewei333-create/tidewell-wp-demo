<?php
/**
 * Tidewell admin page: business details, performance mode and demo tools.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'Tidewell', 'Tidewell', 'manage_options', 'tidewell', 'tidewell_admin_page', 'dashicons-admin-home', 3 );
} );

add_action( 'admin_init', function () {
	register_setting( 'tidewell_business', 'tidewell_business', [
		'type'              => 'array',
		'sanitize_callback' => function ( $input ) {
			$clean = [];
			foreach ( array_keys( TIDEWELL_EDITABLE_FIELDS ) as $key ) {
				$value = sanitize_text_field( $input[ $key ] ?? '' );
				if ( 'email' === $key ) {
					$value = sanitize_email( $value );
				} elseif ( str_ends_with( $key, '_opens' ) || str_ends_with( $key, '_closes' ) ) {
					$value = preg_match( '/^\d{2}:\d{2}$/', $value ) ? $value : '';
				}
				$clean[ $key ] = $value;
			}
			return $clean;
		},
	] );
} );

add_action( 'admin_post_tidewell_perf_mode', function () {
	check_admin_referer( 'tidewell_perf_mode' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	tidewell_apply_perf_mode( sanitize_key( $_POST['mode'] ?? 'optimized' ) );
	wp_safe_redirect( admin_url( 'admin.php?page=tidewell&updated=perf' ) );
	exit;
} );

add_action( 'admin_post_tidewell_rebuild', function () {
	check_admin_referer( 'tidewell_rebuild' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	tidewell_build_pages();
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	wp_safe_redirect( admin_url( 'admin.php?page=tidewell&updated=rebuild' ) );
	exit;
} );

function tidewell_admin_page() {
	$b        = tidewell_business();
	$mode     = tidewell_perf_mode();
	$updated  = sanitize_key( $_GET['updated'] ?? '' );
	$messages = [
		'perf'    => 'Performance mode updated and pages rebuilt.',
		'rebuild' => 'Demo pages rebuilt from the latest layout.',
	];
	?>
	<div class="wrap">
		<h1>Tidewell site settings</h1>
		<?php if ( isset( $messages[ $updated ] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $messages[ $updated ] ); ?></p></div>
		<?php endif; ?>

		<h2>Business details</h2>
		<p>Used in the header, footer, contact page and Google structured data. Change them here once and every page updates.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'tidewell_business' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( TIDEWELL_EDITABLE_FIELDS as $key => $label ) : ?>
					<?php $is_time = str_ends_with( $key, '_opens' ) || str_ends_with( $key, '_closes' ); ?>
					<tr>
						<th scope="row"><label for="tw_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input
								type="<?php echo $is_time ? 'time' : ( 'email' === $key ? 'email' : 'text' ); ?>"
								id="tw_<?php echo esc_attr( $key ); ?>"
								name="tidewell_business[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( $b[ $key ] ); ?>"
								class="<?php echo $is_time ? '' : 'regular-text'; ?>">
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button( 'Save business details' ); ?>
		</form>

		<hr>
		<h2>Performance mode</h2>
		<p>Switch between the state of a typical site before a maintenance pass and after it, to compare Lighthouse / PageSpeed results. See <code>docs/PERFORMANCE.md</code>.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tidewell_perf_mode">
			<?php wp_nonce_field( 'tidewell_perf_mode' ); ?>
			<fieldset>
				<legend class="screen-reader-text">Performance mode</legend>
				<p><label><input type="radio" name="mode" value="optimized" <?php checked( $mode, 'optimized' ); ?>> <strong>Optimized</strong> – WebP subsizes, self-hosted font, inline SVG icons, plugin assets only where needed</label></p>
				<p><label><input type="radio" name="mode" value="baseline" <?php checked( $mode, 'baseline' ); ?>> <strong>Baseline</strong> – full-size JPEGs, Google Fonts CDN, icon font, all plugin assets on every page</label></p>
			</fieldset>
			<?php submit_button( 'Apply mode', 'secondary' ); ?>
		</form>

		<hr>
		<h2>Demo tools</h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tidewell_rebuild">
			<?php wp_nonce_field( 'tidewell_rebuild' ); ?>
			<p>Rebuild the Elementor pages from the layout defined in the plugin. Manual edits to those pages will be overwritten.</p>
			<?php submit_button( 'Rebuild demo pages', 'secondary' ); ?>
		</form>
		<?php if ( post_type_exists( 'flamingo_inbound' ) ) : ?>
			<p>Booking and contact requests are stored under <a href="<?php echo esc_url( admin_url( 'admin.php?page=flamingo_inbound' ) ); ?>">Flamingo &rsaquo; Inbound Messages</a>.</p>
		<?php endif; ?>
	</div>
	<?php
}
