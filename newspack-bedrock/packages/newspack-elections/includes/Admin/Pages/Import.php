<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Admin\Pages;

use Govpack\Profile\CPT as Profile;

/**
 * GovPack Class to Handle Import
 */
class Import {

	const CSV_EXAMPLE_QUERY_ARG = 'csv-example';
	/**
	 * Handle View for the Import Form
	 */
	public static function view(): void {
		
		

		\Govpack\Importer\Importer::check_for_stuck_import();

		wp_enqueue_script( 'govpack-importer' );
		wp_enqueue_style( 'wp-components' );
		
		wp_add_inline_script(
			'govpack-importer',
			'var govpack_importer_options = ' . 
			wp_json_encode(
				[ 
					'profiles_path'   => admin_url( 'edit.php?post_type=' . Profile::CPT_SLUG ),
					'csv_example_url' => \add_query_arg( self::CSV_EXAMPLE_QUERY_ARG, true ),
				] 
			),
			'before' 
		);
		

		include __DIR__ . './../Views/import.php';
	}

	
}
