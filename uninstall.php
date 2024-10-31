<?php
/**
 * Uninstall
 *
 * @package Png Compress
 */

/* if uninstall not called from WordPress exit */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;
/* For Single site */
if ( ! is_multisite() ) {
	delete_option( 'pngcompress' );
} else {
	/* For Multisite */
	$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->prefix}blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blogid ) {
		switch_to_blog( $blogid );
		delete_option( 'pngcompress' );
	}
	switch_to_blog( $original_blog_id );
	/* For site options. */
	delete_site_option( 'pngcompress' );
}
