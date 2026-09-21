<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
 // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
/**
*
* Subscribe Form to fetch Dynamic Sidebar
*
*/

if ( false === ( $pda_sidebar_content = get_transient( 'pda_sidebar_content' ) ) ) {

	$response = wp_remote_get( PDA_SIDEBAR_API );
	if ( is_array( $response ) && ! is_wp_error( $response ) ) {	

		$json = json_decode( $response['body'] );

		$section_1 		= !empty( $json->section_1 ) ? stripslashes( $json->section_1 ) : '';
		$section_2 		= !empty( $json->section_2 ) ? stripslashes( $json->section_2 ) : '';
		$section_3 		= !empty( $json->section_3 ) ? stripslashes( $json->section_3 ) : '';
		$pda_fss_expire = !empty( $json->pda_fss_expire ) ? (int) $json->pda_fss_expire : 1;

		set_transient( 'pda_sidebar_content', $response['body'], DAY_IN_SECONDS * $pda_fss_expire );

		if( !empty( $section_1 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-1">'.$section_1.'</div>';	
		}

		if( !empty( $section_2 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-2">'.$section_2.'</div>';	
		}

		if( !empty( $section_3 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-3">'.$section_3.'</div>';	
		}
	}

} else {

 	$response = get_transient( 'pda_sidebar_content' );
	$json = json_decode( $response );

	if ( !empty( $json ) ) {	

		$section_1 = !empty( $json->section_1 ) ? stripslashes( $json->section_1 ) : '';
		$section_2 = !empty( $json->section_2 ) ? stripslashes( $json->section_2 ) : '';
		$section_3 = !empty( $json->section_3 ) ? stripslashes( $json->section_3 ) : '';

		if( !empty( $section_1 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-1">'.$section_1.'</div>';	
		}

		if( !empty( $section_2 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-2">'.$section_2.'</div>';	
		}

		if( !empty( $section_3 ) ){
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is trusted and served from PDA-owned API.
			echo '<div class="main_container pda-section-3">'.$section_3.'</div>';	
		}
	}

}
