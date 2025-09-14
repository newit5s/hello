<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Ajax {
	public static function init() {
		add_action( 'wp_ajax_nopriv_wptbm_create_booking', [ __CLASS__, 'create_booking' ] );
		add_action( 'wp_ajax_wptbm_create_booking', [ __CLASS__, 'create_booking' ] );
	}

	private static function within_business_hours( $timestamp ) {
		$settings = WPTBM_Settings::get_settings();
		$w = (int) wp_date( 'w', $timestamp );
		$day = $settings['business_hours'][ $w ];
		if ( ! empty( $day['closed'] ) ) { return false; }
		$open = strtotime( wp_date( 'Y-m-d', $timestamp ) . ' ' . $day['open'] . ' UTC' );
		$close = strtotime( wp_date( 'Y-m-d', $timestamp ) . ' ' . $day['close'] . ' UTC' );
		return ( $timestamp >= $open && $timestamp <= $close );
	}

	public static function create_booking() {
		check_ajax_referer( 'wptbm_nonce' );

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$table_id = isset( $_POST['table_id'] ) ? (int) $_POST['table_id'] : 0;
		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
		$duration = isset( $_POST['duration'] ) ? max( 15, (int) $_POST['duration'] ) : 0;
		$party_size = isset( $_POST['party_size'] ) ? max( 1, (int) $_POST['party_size'] ) : 2;
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
		$custom1 = isset( $_POST['custom1'] ) ? sanitize_text_field( wp_unslash( $_POST['custom1'] ) ) : '';

		if ( empty( $name ) || empty( $email ) || empty( $table_id ) || empty( $date ) || empty( $time ) ) {
			wp_send_json_error( [ 'message' => __( 'Please fill in all required fields.', 'wptbm' ) ] );
		}

		// Default duration per table
		if ( $duration <= 0 ) {
			$td = (int) get_post_meta( $table_id, '_wptbm_default_duration', true );
			$duration = $td > 0 ? $td : 90;
		}

		$datetime = $date . ' ' . $time;
		$start_ts = strtotime( $datetime . ' UTC' );
		$end_ts = $start_ts + ( $duration * 60 );
		if ( ! $start_ts || ! $end_ts || $end_ts <= $start_ts ) {
			wp_send_json_error( [ 'message' => __( 'Invalid date/time provided.', 'wptbm' ) ] );
		}

		// Business hours check for start and end
		if ( ! self::within_business_hours( $start_ts ) || ! self::within_business_hours( $end_ts ) ) {
			wp_send_json_error( [ 'message' => __( 'Selected time is outside business hours.', 'wptbm' ) ] );
		}

		// Required custom field?
		$settings = WPTBM_Settings::get_settings();
		if ( ! empty( $settings['custom1_required'] ) && '' === $custom1 ) {
			wp_send_json_error( [ 'message' => sprintf( __( '%s is required.', 'wptbm' ), $settings['custom1_label'] ) ] );
		}

		// Ensure customer exists or create
		$customer_id = 0;
		$existing = get_posts( [
			'post_type' => 'wptbm_customer',
			'numberposts' => 1,
			'fields' => 'ids',
			'meta_key' => '_wptbm_email',
			'meta_value' => $email,
		] );
		if ( ! empty( $existing ) ) {
			$customer_id = (int) $existing[0];
			wp_update_post( [ 'ID' => $customer_id, 'post_title' => $name ] );
			update_post_meta( $customer_id, '_wptbm_phone', $phone );
		} else {
			$customer_id = wp_insert_post( [
				'post_type' => 'wptbm_customer',
				'post_status' => 'publish',
				'post_title' => $name,
			] );
			if ( is_wp_error( $customer_id ) ) {
				wp_send_json_error( [ 'message' => __( 'Could not create customer.', 'wptbm' ) ] );
			}
			update_post_meta( $customer_id, '_wptbm_email', $email );
			update_post_meta( $customer_id, '_wptbm_phone', $phone );
		}

		// Validate capacity
		$capacity = (int) get_post_meta( $table_id, '_wptbm_capacity', true );
		if ( $capacity && $party_size > $capacity ) {
			wp_send_json_error( [ 'message' => sprintf( __( 'Party size exceeds table capacity (%d).', 'wptbm' ), $capacity ) ] );
		}

		// Validate overlap
		$q = new WP_Query( [
			'post_type' => 'wptbm_booking',
			'post_status' => [ 'publish', 'pending', 'draft' ],
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => [
				'relation' => 'AND',
				[ 'key' => '_wptbm_table_id', 'value' => $table_id, 'compare' => '=' ],
				[ 'key' => '_wptbm_start_ts', 'value' => $end_ts, 'type' => 'NUMERIC', 'compare' => '<' ],
				[ 'key' => '_wptbm_end_ts', 'value' => $start_ts, 'type' => 'NUMERIC', 'compare' => '>' ],
			],
		] );
		if ( $q->have_posts() ) {
			wp_send_json_error( [ 'message' => __( 'Selected time overlaps with another booking for this table.', 'wptbm' ) ] );
		}
		wp_reset_postdata();

		$booking_id = wp_insert_post( [
			'post_type' => 'wptbm_booking',
			'post_status' => 'publish',
			'post_title' => $name . ' – ' . gmdate( 'Y-m-d H:i', $start_ts ),
		] );
		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Could not create booking.', 'wptbm' ) ] );
		}
		update_post_meta( $booking_id, '_wptbm_table_id', $table_id );
		update_post_meta( $booking_id, '_wptbm_customer_id', $customer_id );
		update_post_meta( $booking_id, '_wptbm_party_size', $party_size );
		update_post_meta( $booking_id, '_wptbm_start_ts', $start_ts );
		update_post_meta( $booking_id, '_wptbm_end_ts', $end_ts );
		update_post_meta( $booking_id, '_wptbm_status', 'pending' );
		update_post_meta( $booking_id, '_wptbm_notes', $notes );
		update_post_meta( $booking_id, '_wptbm_custom1', $custom1 );

		// Send notifications
		if ( class_exists( 'WPTBM_Notifications' ) ) {
			WPTBM_Notifications::send_on_create( $booking_id );
		}

		wp_send_json_success( [ 'message' => __( 'Booking request submitted. We will confirm shortly.', 'wptbm' ), 'booking_id' => $booking_id ] );
	}
}