<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_CPT {
	public static function register_post_types() {
		register_post_type( 'wptbm_table', [
			'labels' => [
				'name' => __( 'Tables', 'wptbm' ),
				'singular_name' => __( 'Table', 'wptbm' ),
			],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'supports' => [ 'title' ],
			'menu_icon' => 'dashicons-editor-table',
		] );

		register_post_type( 'wptbm_customer', [
			'labels' => [
				'name' => __( 'Customers', 'wptbm' ),
				'singular_name' => __( 'Customer', 'wptbm' ),
			],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			supports' => [ 'title' ],
			'menu_icon' => 'dashicons-groups',
		] );

		register_post_type( 'wptbm_booking', [
			'labels' => [
				'name' => __( 'Bookings', 'wptbm' ),
				'singular_name' => __( 'Booking', 'wptbm' ),
			],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			supports' => [ 'title' ],
			'menu_icon' => 'dashicons-calendar-alt',
		] );
	}

	public static function register_meta() {
		// Table meta
		register_post_meta( 'wptbm_table', '_wptbm_capacity', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'default' => 2,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_table', '_wptbm_default_duration', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'default' => 90,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );

		// Customer meta
		register_post_meta( 'wptbm_customer', '_wptbm_email', [
			'show_in_rest' => true,
			'type' => 'string',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_customer', '_wptbm_phone', [
			'show_in_rest' => true,
			'type' => 'string',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );

		// Booking meta
		register_post_meta( 'wptbm_booking', '_wptbm_table_id', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_customer_id', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_party_size', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'default' => 2,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_start_ts', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_end_ts', [
			'show_in_rest' => true,
			'type' => 'integer',
			'single' => true,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_status', [
			'show_in_rest' => true,
			'type' => 'string',
			'single' => true,
			'default' => 'pending',
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_notes', [
			'show_in_rest' => true,
			'type' => 'string',
			'single' => true,
			'default' => '',
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( 'wptbm_booking', '_wptbm_custom1', [
			'show_in_rest' => true,
			'type' => 'string',
			'single' => true,
			'default' => '',
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		] );
	}
}