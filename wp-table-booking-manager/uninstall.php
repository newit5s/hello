<?php
// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$types = [ 'wptbm_booking', 'wptbm_customer', 'wptbm_table' ];
foreach ( $types as $type ) {
	$posts = get_posts( [
		'post_type' => $type,
		'post_status' => 'any',
		'numberposts' => -1,
		'fields' => 'ids',
	] );
	foreach ( $posts as $pid ) {
		wp_delete_post( $pid, true );
	}
}

delete_option( 'wptbm_settings' );