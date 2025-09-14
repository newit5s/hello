<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Notifications {
	private static function build_context( $booking_id ) {
		$booking = get_post( $booking_id );
		$table_id = (int) get_post_meta( $booking_id, '_wptbm_table_id', true );
		$customer_id = (int) get_post_meta( $booking_id, '_wptbm_customer_id', true );
		$party_size = (int) get_post_meta( $booking_id, '_wptbm_party_size', true );
		$start_ts = (int) get_post_meta( $booking_id, '_wptbm_start_ts', true );
		$end_ts = (int) get_post_meta( $booking_id, '_wptbm_end_ts', true );
		$status = get_post_meta( $booking_id, '_wptbm_status', true );
		$notes = get_post_meta( $booking_id, '_wptbm_notes', true );
		$custom1 = get_post_meta( $booking_id, '_wptbm_custom1', true );
		$customer_email = get_post_meta( $customer_id, '_wptbm_email', true );
		$customer_phone = get_post_meta( $customer_id, '_wptbm_phone', true );

		return [
			'booking_id' => $booking_id,
			'site_name' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'customer_name' => $customer_id ? get_the_title( $customer_id ) : '',
			'customer_email' => $customer_email,
			'customer_phone' => $customer_phone,
			'table_title' => $table_id ? get_the_title( $table_id ) : '',
			'party_size' => $party_size,
			'date' => $start_ts ? wp_date( 'Y-m-d', $start_ts ) : '',
			'time' => $start_ts ? wp_date( 'H:i', $start_ts ) : '',
			'notes' => $notes,
			'custom1' => $custom1,
			'status' => $status,
		];
	}

	private static function render( $template, $context ) {
		foreach ( $context as $key => $value ) {
			$template = str_replace( '{' . $key . '}', (string) $value, $template );
		}
		return $template;
	}

	private static function maybe_set_from( $from_email ) {
		if ( ! empty( $from_email ) ) {
			add_filter( 'wp_mail_from', function() use ( $from_email ) { return $from_email; } );
		}
	}

	public static function send_on_create( $booking_id ) {
		$settings = WPTBM_Settings::get_settings();
		$context = self::build_context( $booking_id );
		self::maybe_set_from( $settings['from_email'] );

		// Customer
		if ( ! empty( $context['customer_email'] ) ) {
			$subject = self::render( $settings['customer_email_subject'], $context );
			$body = self::render( $settings['customer_email_template'], $context );
			wp_mail( $context['customer_email'], $subject, $body );
		}

		// Admin
		if ( ! empty( $settings['admin_email'] ) ) {
			$subject = self::render( $settings['admin_email_subject'], $context );
			$body = self::render( $settings['admin_email_template'], $context );
			wp_mail( $settings['admin_email'], $subject, $body );
		}
	}

	public static function send_on_status_change( $booking_id, $old_status, $new_status ) {
		$settings = WPTBM_Settings::get_settings();
		$context = self::build_context( $booking_id );
		$context['status'] = $new_status;
		self::maybe_set_from( $settings['from_email'] );

		if ( ! empty( $context['customer_email'] ) ) {
			$subject = sprintf( __( 'Booking #%d status updated to %s', 'wptbm' ), $booking_id, ucfirst( $new_status ) );
			$body = self::render( $settings['customer_email_template'], $context );
			wp_mail( $context['customer_email'], $subject, $body );
		}
	}
}