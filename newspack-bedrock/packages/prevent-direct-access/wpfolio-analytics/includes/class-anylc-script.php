<?php
/**
 * Script Class
 *
 * Handles the script and style 
 *
 * @package WPFolio Pda Analytic
 * @since 1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPFolio_Pda_Anylc_Script {

	function __construct() {

        // Action to add style backend
		add_action( 'admin_enqueue_scripts', array($this, 'wpfolio_pda_anylc_admin_script_style') );
	}

     /**
	 * Enqueue script for backend
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
    function wpfolio_pda_anylc_admin_script_style( $hook ) {

		// Process Promotion Data (redirect params from server-side redirect; used only for localize_script).
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only use for script data.
		if ( ! empty( $_GET['message'] ) && 'wpfolio_pda_anylc_promotion' === $_GET['message'] && ! empty( $_GET['wpfolio_pda_anylc_pdt'] ) && ! empty( $_GET['wpfolio_pda_anylc_promo_pdt'] ) ) {
			global $wpfolio_pda_analytics_product;

			$promotion                      = 1;
			$wpfolio_pda_anylc_promo_pdt   = sanitize_text_field( wp_unslash( $_GET['wpfolio_pda_anylc_promo_pdt'] ) );
			$promotion_pdt                  = explode( ',', $wpfolio_pda_anylc_promo_pdt );

			$anylc_pdt = sanitize_text_field( wp_unslash( $_GET['wpfolio_pda_anylc_pdt'] ) );
			$anylc_pdt_data = isset( $wpfolio_pda_analytics_product[ $anylc_pdt ] ) ? $wpfolio_pda_analytics_product[ $anylc_pdt ] : false;

			if ( ! empty( $promotion_pdt ) ) {
				foreach ( $promotion_pdt as $pdt_key => $pdt ) {
					if ( isset( $anylc_pdt_data['promotion'][ $pdt ]['file'] ) ) {
						$promotion_pdt_data[] = $anylc_pdt_data['promotion'][ $pdt ]['file'];
					}
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

    	// Registring admin Style
		wp_register_style( 'wpfolio-pda-anylc-admin-style', WPFOLIO_PDA_ANYLC_URL.'assets/css/wpfolio-pda-anylc-admin.css', null, WPFOLIO_PDA_ANYLC_VERSION );
		wp_enqueue_style( 'wpfolio-pda-anylc-admin-style' );

		// Registring admin script
		wp_register_script( 'wpfolio-pda-anylc-admin-script', WPFOLIO_PDA_ANYLC_URL.'assets/js/wpfolio-pda-anylc-admin.js', array('jquery'), WPFOLIO_PDA_ANYLC_VERSION, true );
		wp_localize_script( 'wpfolio-pda-anylc-admin-script', 'WPFolioAnylc', array(
																		'promotion' 	=> isset($promotion) ? 1 : 0,
																		'promotion_pdt' => isset( $promotion_pdt_data ) ? $promotion_pdt_data : 0,
																	));
		wp_enqueue_script( 'wpfolio-pda-anylc-admin-script' );
    }
}

$wpfolio_pda_anylc_script = new WPFolio_Pda_Anylc_Script();