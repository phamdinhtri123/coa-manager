<?php
/**
 * Uninstall handler.
 *
 * @package COAM
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = (array) get_option( 'coam_settings', array() );
if ( empty( $settings['delete_on_uninstall'] ) ) {
	return;
}

$posts = get_posts(
	array(
		'post_type'      => 'coa_record',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $posts as $post_id ) {
	wp_delete_post( absint( $post_id ), true );
}

$terms = get_terms(
	array(
		'taxonomy'   => 'coa_category',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);

if ( ! is_wp_error( $terms ) ) {
	foreach ( $terms as $term_id ) {
		wp_delete_term( absint( $term_id ), 'coa_category' );
	}
}

delete_option( 'coam_settings' );
