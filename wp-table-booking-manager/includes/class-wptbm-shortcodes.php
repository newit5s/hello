<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Shortcodes {
	public static function init() {
		add_shortcode( 'wptbm_booking_form', [ __CLASS__, 'booking_form' ] );
	}

	public static function booking_form( $atts ) {
		$atts = shortcode_atts( [ 'table' => '' ], $atts, 'wptbm_booking_form' );
		$preselected_table = (int) $atts['table'];

		wp_enqueue_style( 'wptbm-frontend', WPTBM_PLUGIN_URL . 'assets/css/frontend.css', [], WPTBM_VERSION );
		wp_enqueue_script( 'wptbm-frontend', WPTBM_PLUGIN_URL . 'assets/js/frontend.js', [ 'jquery' ], WPTBM_VERSION, true );
		wp_localize_script( 'wptbm-frontend', 'WPTBM', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'wptbm_nonce' ),
		] );

		$tables = get_posts( [ 'post_type' => 'wptbm_table', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
		$settings = WPTBM_Settings::get_settings();
		$default_duration = 90;
		if ( $preselected_table ) {
			$td = (int) get_post_meta( $preselected_table, '_wptbm_default_duration', true );
			if ( $td > 0 ) { $default_duration = $td; }
		}

		ob_start();
		?>
		<form id="wptbm-form">
			<div class="wptbm-field">
				<label><?php esc_html_e( 'Name', 'wptbm' ); ?></label>
				<input type="text" name="name" required />
			</div>
			<div class="wptbm-field">
				<label><?php esc_html_e( 'Email', 'wptbm' ); ?></label>
				<input type="email" name="email" required />
			</div>
			<div class="wptbm-field">
				<label><?php esc_html_e( 'Phone', 'wptbm' ); ?></label>
				<input type="text" name="phone" />
			</div>
			<div class="wptbm-field">
				<label><?php esc_html_e( 'Table', 'wptbm' ); ?></label>
				<select name="table_id" <?php echo $preselected_table ? 'disabled' : ''; ?>>
					<option value=""><?php esc_html_e( 'Select a table', 'wptbm' ); ?></option>
					<?php foreach ( $tables as $table ) : ?>
						<option value="<?php echo esc_attr( $table->ID ); ?>" <?php selected( $preselected_table, $table->ID ); ?>><?php echo esc_html( $table->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php if ( $preselected_table ) : ?>
					<input type="hidden" name="table_id" value="<?php echo esc_attr( $preselected_table ); ?>" />
				<?php endif; ?>
			</div>
			<div class="wptbm-field grid-2">
				<div>
					<label><?php esc_html_e( 'Date', 'wptbm' ); ?></label>
					<input type="date" name="date" required />
				</div>
				<div>
					<label><?php esc_html_e( 'Time', 'wptbm' ); ?></label>
					<input type="time" name="time" required />
				</div>
			</div>
			<div class="wptbm-field grid-2">
				<div>
					<label><?php esc_html_e( 'Duration (minutes)', 'wptbm' ); ?></label>
					<input type="number" min="15" step="15" name="duration" value="<?php echo esc_attr( $default_duration ); ?>" required />
				</div>
				<div>
					<label><?php esc_html_e( 'Party size', 'wptbm' ); ?></label>
					<input type="number" min="1" step="1" name="party_size" value="2" required />
				</div>
			</div>
			<div class="wptbm-field">
				<label><?php esc_html_e( 'Notes', 'wptbm' ); ?></label>
				<textarea name="notes" rows="3"></textarea>
			</div>
			<div class="wptbm-field">
				<label><?php echo esc_html( $settings['custom1_label'] ); ?><?php echo ! empty( $settings['custom1_required'] ) ? ' *' : ''; ?></label>
				<input type="text" name="custom1" <?php echo ! empty( $settings['custom1_required'] ) ? 'required' : ''; ?> />
			</div>
			<input type="hidden" name="action" value="wptbm_create_booking" />
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wptbm_nonce' ) ); ?>" />
			<button type="submit" class="wptbm-submit"><?php esc_html_e( 'Book Table', 'wptbm' ); ?></button>
			<div class="wptbm-message" role="status" aria-live="polite"></div>
		</form>
		<?php
		return ob_get_clean();
	}
}