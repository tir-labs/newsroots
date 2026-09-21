<?php
/**
 *
 * Load Admin JS Resources
 *
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
if ( ! defined( 'ABSPATH' ) ) exit;
function admin_load_js() {

	// Register Script
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingInFooter -- Intentionally loaded in header.
	wp_register_script( 'ajaxHandle', plugins_url( '../js/custom-file.js', __FILE__ ), array( 'jquery' ), PDAF_VERSION ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
	
	// Enqueue Script
	wp_enqueue_script( 'ajaxHandle' );

	// Localize Script
	wp_localize_script( 'ajaxHandle', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}
?>
