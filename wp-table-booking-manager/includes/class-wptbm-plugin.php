<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPTBM_Plugin {
	/** @var WPTBM_Plugin */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', [ 'WPTBM_CPT', 'register_post_types' ] );
		add_action( 'init', [ 'WPTBM_CPT', 'register_meta' ] );
		add_action( 'admin_init', [ 'WPTBM_Admin', 'init' ] );
		add_action( 'admin_menu', [ 'WPTBM_Settings', 'register_menu' ] );
		add_action( 'admin_init', [ 'WPTBM_Settings', 'register_settings' ] );
		add_action( 'init', [ 'WPTBM_Shortcodes', 'init' ] );
		add_action( 'init', [ 'WPTBM_Ajax', 'init' ] );

		// Notifications on status change
		add_action( 'transition_post_status', [ $this, 'maybe_notify_on_status_change' ], 10, 3 );
	}

	public function maybe_notify_on_status_change( $new_status, $old_status, $post ) {
		if ( $post && 'wptbm_booking' === $post->post_type && $new_status !== $old_status ) {
			WPTBM_Notifications::send_on_status_change( $post->ID, $old_status, $new_status );
		}
	}
}