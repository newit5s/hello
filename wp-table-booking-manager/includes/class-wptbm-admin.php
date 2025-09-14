<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Admin {
	public static function init() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'save_post_wptbm_table', [ __CLASS__, 'save_table_meta' ] );
		add_action( 'save_post_wptbm_customer', [ __CLASS__, 'save_customer_meta' ] );
		add_action( 'save_post_wptbm_booking', [ __CLASS__, 'save_booking_meta' ] );
		add_action( 'admin_notices', [ __CLASS__, 'admin_notices' ] );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'wptbm_table_meta', __( 'Table Details', 'wptbm' ), [ __CLASS__, 'render_table_meta' ], 'wptbm_table', 'normal', 'default' );
		add_meta_box( 'wptbm_customer_meta', __( 'Customer Details', 'wptbm' ), [ __CLASS__, 'render_customer_meta' ], 'wptbm_customer', 'normal', 'default' );
		add_meta_box( 'wptbm_booking_meta', __( 'Booking Details', 'wptbm' ), [ __CLASS__, 'render_booking_meta' ], 'wptbm_booking', 'normal', 'default' );
	}

	public static function render_table_meta( $post ) {
		wp_nonce_field( 'wptbm_save_table', 'wptbm_table_nonce' );
		$capacity = (int) get_post_meta( $post->ID, '_wptbm_capacity', true );
		if ( $capacity <= 0 ) { $capacity = 2; }
		$duration = (int) get_post_meta( $post->ID, '_wptbm_default_duration', true );
		if ( $duration <= 0 ) { $duration = 90; }
		?>
		<p>
			<label for="wptbm_capacity"><?php esc_html_e( 'Capacity', 'wptbm' ); ?></label>
			<input type="number" min="1" step="1" id="wptbm_capacity" name="wptbm_capacity" value="<?php echo esc_attr( $capacity ); ?>" />
		</p>
		<p>
			<label for="wptbm_default_duration"><?php esc_html_e( 'Default duration (minutes)', 'wptbm' ); ?></label>
			<input type="number" min="15" step="15" id="wptbm_default_duration" name="wptbm_default_duration" value="<?php echo esc_attr( $duration ); ?>" />
		</p>
		<?php
	}

	public static function render_customer_meta( $post ) {
		wp_nonce_field( 'wptbm_save_customer', 'wptbm_customer_nonce' );
		$email = get_post_meta( $post->ID, '_wptbm_email', true );
		$phone = get_post_meta( $post->ID, '_wptbm_phone', true );
		?>
		<p>
			<label for="wptbm_email"><?php esc_html_e( 'Email', 'wptbm' ); ?></label>
			<input type="email" id="wptbm_email" name="wptbm_email" value="<?php echo esc_attr( $email ); ?>" />
		</p>
		<p>
			<label for="wptbm_phone"><?php esc_html_e( 'Phone', 'wptbm' ); ?></label>
			<input type="text" id="wptbm_phone" name="wptbm_phone" value="<?php echo esc_attr( $phone ); ?>" />
		</p>
		<?php
	}

	public static function render_booking_meta( $post ) {
		wp_nonce_field( 'wptbm_save_booking', 'wptbm_booking_nonce' );
		$table_id = (int) get_post_meta( $post->ID, '_wptbm_table_id', true );
		$customer_id = (int) get_post_meta( $post->ID, '_wptbm_customer_id', true );
		$party_size = (int) get_post_meta( $post->ID, '_wptbm_party_size', true );
		$start_ts = (int) get_post_meta( $post->ID, '_wptbm_start_ts', true );
		$end_ts = (int) get_post_meta( $post->ID, '_wptbm_end_ts', true );
		$status = get_post_meta( $post->ID, '_wptbm_status', true );
		$notes = get_post_meta( $post->ID, '_wptbm_notes', true );
		$custom1 = get_post_meta( $post->ID, '_wptbm_custom1', true );
		if ( empty( $status ) ) { $status = 'pending'; }

		$tables = get_posts( [ 'post_type' => 'wptbm_table', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
		$customers = get_posts( [ 'post_type' => 'wptbm_customer', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
		$settings = WPTBM_Settings::get_settings();
		?>
		<p>
			<label for="wptbm_table_id"><?php esc_html_e( 'Table', 'wptbm' ); ?></label>
			<select id="wptbm_table_id" name="wptbm_table_id">
				<option value=""><?php esc_html_e( 'Select a table', 'wptbm' ); ?></option>
				<?php foreach ( $tables as $table ) : ?>
					<option value="<?php echo esc_attr( $table->ID ); ?>" <?php selected( $table_id, $table->ID ); ?>><?php echo esc_html( $table->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="wptbm_customer_id"><?php esc_html_e( 'Customer', 'wptbm' ); ?></label>
			<select id="wptbm_customer_id" name="wptbm_customer_id">
				<option value=""><?php esc_html_e( 'Select a customer', 'wptbm' ); ?></option>
				<?php foreach ( $customers as $customer ) : ?>
					<option value="<?php echo esc_attr( $customer->ID ); ?>" <?php selected( $customer_id, $customer->ID ); ?>><?php echo esc_html( $customer->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="wptbm_party_size"><?php esc_html_e( 'Party size', 'wptbm' ); ?></label>
			<input type="number" min="1" step="1" id="wptbm_party_size" name="wptbm_party_size" value="<?php echo esc_attr( $party_size ?: 2 ); ?>" />
		</p>
		<p>
			<label for="wptbm_start"><?php esc_html_e( 'Start', 'wptbm' ); ?></label>
			<input type="datetime-local" id="wptbm_start" name="wptbm_start" value="<?php echo $start_ts ? esc_attr( gmdate( 'Y-m-d\TH:i', $start_ts ) ) : ''; ?>" />
		</p>
		<p>
			<label for="wptbm_end"><?php esc_html_e( 'End', 'wptbm' ); ?></label>
			<input type="datetime-local" id="wptbm_end" name="wptbm_end" value="<?php echo $end_ts ? esc_attr( gmdate( 'Y-m-d\TH:i', $end_ts ) ) : ''; ?>" />
		</p>
		<p>
			<label for="wptbm_status"><?php esc_html_e( 'Status', 'wptbm' ); ?></label>
			<select id="wptbm_status" name="wptbm_status">
				<?php foreach ( [ 'pending', 'confirmed', 'cancelled' ] as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="wptbm_notes"><?php esc_html_e( 'Notes', 'wptbm' ); ?></label>
			<textarea id="wptbm_notes" name="wptbm_notes" rows="3" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea>
		</p>
		<p>
			<label for="wptbm_custom1"><?php echo esc_html( $settings['custom1_label'] ); ?></label>
			<input type="text" id="wptbm_custom1" name="wptbm_custom1" value="<?php echo esc_attr( $custom1 ); ?>" />
		</p>
		<?php
	}

	public static function save_table_meta( $post_id ) {
		if ( ! isset( $_POST['wptbm_table_nonce'] ) || ! wp_verify_nonce( $_POST['wptbm_table_nonce'], 'wptbm_save_table' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		$capacity = isset( $_POST['wptbm_capacity'] ) ? max( 1, (int) $_POST['wptbm_capacity'] ) : 2;
		$duration = isset( $_POST['wptbm_default_duration'] ) ? max( 15, (int) $_POST['wptbm_default_duration'] ) : 90;
		update_post_meta( $post_id, '_wptbm_capacity', $capacity );
		update_post_meta( $post_id, '_wptbm_default_duration', $duration );
	}

	public static function save_customer_meta( $post_id ) {
		if ( ! isset( $_POST['wptbm_customer_nonce'] ) || ! wp_verify_nonce( $_POST['wptbm_customer_nonce'], 'wptbm_save_customer' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		$email = isset( $_POST['wptbm_email'] ) ? sanitize_email( wp_unslash( $_POST['wptbm_email'] ) ) : '';
		$phone = isset( $_POST['wptbm_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['wptbm_phone'] ) ) : '';
		update_post_meta( $post_id, '_wptbm_email', $email );
		update_post_meta( $post_id, '_wptbm_phone', $phone );
	}

	public static function save_booking_meta( $post_id ) {
		if ( ! isset( $_POST['wptbm_booking_nonce'] ) || ! wp_verify_nonce( $_POST['wptbm_booking_nonce'], 'wptbm_save_booking' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$table_id = isset( $_POST['wptbm_table_id'] ) ? (int) $_POST['wptbm_table_id'] : 0;
		$customer_id = isset( $_POST['wptbm_customer_id'] ) ? (int) $_POST['wptbm_customer_id'] : 0;
		$party_size = isset( $_POST['wptbm_party_size'] ) ? max( 1, (int) $_POST['wptbm_party_size'] ) : 2;
		$start = isset( $_POST['wptbm_start'] ) ? sanitize_text_field( wp_unslash( $_POST['wptbm_start'] ) ) : '';
		$end = isset( $_POST['wptbm_end'] ) ? sanitize_text_field( wp_unslash( $_POST['wptbm_end'] ) ) : '';
		$status = isset( $_POST['wptbm_status'] ) ? sanitize_text_field( wp_unslash( $_POST['wptbm_status'] ) ) : 'pending';
		$notes = isset( $_POST['wptbm_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wptbm_notes'] ) ) : '';
		$custom1 = isset( $_POST['wptbm_custom1'] ) ? sanitize_text_field( wp_unslash( $_POST['wptbm_custom1'] ) ) : '';

		$start_ts = $start ? strtotime( $start . ' UTC' ) : 0;
		$end_ts = $end ? strtotime( $end . ' UTC' ) : 0;
		if ( $start_ts && $end_ts && $end_ts <= $start_ts ) {
			self::add_error( __( 'End time must be after start time.', 'wptbm' ) );
		}

		update_post_meta( $post_id, '_wptbm_table_id', $table_id );
		update_post_meta( $post_id, '_wptbm_customer_id', $customer_id );
		update_post_meta( $post_id, '_wptbm_party_size', $party_size );
		update_post_meta( $post_id, '_wptbm_start_ts', $start_ts );
		update_post_meta( $post_id, '_wptbm_end_ts', $end_ts );
		update_post_meta( $post_id, '_wptbm_status', $status );
		update_post_meta( $post_id, '_wptbm_notes', $notes );
		update_post_meta( $post_id, '_wptbm_custom1', $custom1 );

		$title_bits = [];
		if ( $customer_id ) { $title_bits[] = get_the_title( $customer_id ); }
		if ( $start_ts ) { $title_bits[] = gmdate( 'Y-m-d H:i', $start_ts ); }
		if ( ! empty( $title_bits ) ) {
			remove_action( 'save_post_wptbm_booking', [ __CLASS__, 'save_booking_meta' ] );
			wp_update_post( [ 'ID' => $post_id, 'post_title' => implode( ' – ', $title_bits ) ] );
			add_action( 'save_post_wptbm_booking', [ __CLASS__, 'save_booking_meta' ] );
		}

		// Validate capacity and overlap
		$errors = self::validate_booking( $post_id, $table_id, $party_size, $start_ts, $end_ts );
		foreach ( $errors as $msg ) {
			self::add_error( $msg );
		}
	}

	private static function validate_booking( $booking_id, $table_id, $party_size, $start_ts, $end_ts ) {
		$errors = [];
		if ( ! $table_id || ! $start_ts || ! $end_ts ) { return $errors; }
		$capacity = (int) get_post_meta( $table_id, '_wptbm_capacity', true );
		if ( $capacity && $party_size > $capacity ) {
			$errors[] = sprintf( __( 'Party size exceeds table capacity (%d).', 'wptbm' ), $capacity );
		}
		// Overlapping bookings for same table
		$q = new WP_Query( [
			'post_type' => 'wptbm_booking',
			'post_status' => [ 'publish', 'pending', 'draft' ],
			'posts_per_page' => -1,
			'post__not_in' => $booking_id ? [ $booking_id ] : [],
			'fields' => 'ids',
			'meta_query' => [
				'relation' => 'AND',
				[ 'key' => '_wptbm_table_id', 'value' => $table_id, 'compare' => '=' ],
				[ 'key' => '_wptbm_start_ts', 'value' => $end_ts, 'type' => 'NUMERIC', 'compare' => '<' ],
				[ 'key' => '_wptbm_end_ts', 'value' => $start_ts, 'type' => 'NUMERIC', 'compare' => '>' ],
			],
		] );
		if ( $q->have_posts() ) {
			$errors[] = __( 'Selected time overlaps with another booking for this table.', 'wptbm' );
		}
		wp_reset_postdata();
		return $errors;
	}

	public static function add_error( $message ) {
		add_settings_error( 'wptbm', 'wptbm_error_' . wp_generate_uuid4(), $message, 'error' );
	}

	public static function admin_notices() {
		settings_errors( 'wptbm' );
	}
}