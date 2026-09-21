<?php 
/**
 *
 * Manage Remote Logs
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
 // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// Class Started
class PDALogger {

	public static function remote_log($message) {
		$remote_log = get_option('remote_log');
		$pda_license_key = get_option('pda_license_key');
		if($remote_log && !empty($pda_license_key) && !is_null($pda_license_key)) { 
			$configs = include('config.php');
			$serviceUrl = $configs->pda_lg_api;
			$bodyInput = array(
				"key" => $pda_license_key,
				"message" => $message
			);
			$args = array(
				'body' => json_encode($bodyInput),
			    'timeout' => '100',
			    'redirection' => '5',
			    'httpversion' => '1.0',
			    'blocking' => true,
			    'headers' => array(
			    	'x-api-key' => $configs->lc_key,
			    	'Content-Type' => 'application/json'
		    	),
			    'cookies' => array()
			);
			$response = wp_remote_post( $serviceUrl, $args );
		}
	}
}

?>