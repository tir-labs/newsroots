<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Importer\Abstracts;

use Exception;



/**
 * Register and handle the "USIO" Importer
 */
abstract class AbstractImporter {

	const IMPORT_NOT_RUNNING = 0;
	const IMPORT_RUNNING     = 1;
	const IMPORT_DONE        = 2;
	const IMPORT_TEST_KEY    = 'govpack_import_processing';

	/**
	 * Store the generated import group for the whole request, It's timestamp based so it could change during a request.
	 * 
	 * @var string|null 
	 */
	public static $import_group = null;

	/**
	 * Returns a new Importer based on the calling class
	 */
	public static function make(): static {
		return new static();
	}

	/**
	 * Should we need to cancel the import the quickest way to to delete the keys storing how it runs.
	 */
	public static function cancel(): void {
		delete_option( self::IMPORT_TEST_KEY );
	}

		
	/**
	 * Checks if an import is already running
	 *
	 * @return string[]
	 *
	 * @psalm-return array{status: 'done'|'not_running'|'running'}
	 */
	public static function status(): array {


		$import_processing_running = get_option( self::IMPORT_TEST_KEY, self::IMPORT_NOT_RUNNING );

		if ( self::IMPORT_RUNNING === $import_processing_running ) {
			return [ 'status' => 'running' ];
		}

		if ( self::IMPORT_DONE === $import_processing_running ) {
			return [ 'status' => 'done' ];
		}

		return [ 'status' => 'not_running' ];
	}

	/**
	 * Main Import Process Runner
	 *
	 * @param string $file  Name of the JSON file.
	 * @param array  $extra Array of extra import configuration passed to the importer.
	 *
	 * @return (mixed|string)[]|\WP_Error
	 *
	 * @psalm-return \WP_Error|array{status: 'done'|'running', import_group?: mixed}
	 */
	public static function import( $file, $extra ): array|\WP_Error {

		$import_group              = get_option( 'govpack_import_group', false );
		$import_processing_running = get_option( self::IMPORT_TEST_KEY, self::IMPORT_NOT_RUNNING );

		if ( self::IMPORT_RUNNING === $import_processing_running ) {
			return [ 'status' => 'running' ];
		}

		if ( self::IMPORT_DONE === $import_processing_running ) {
			return [ 'status' => 'done' ];
		}

		update_option( self::IMPORT_TEST_KEY, self::IMPORT_RUNNING );
		

		$file = \Govpack\Importer\Importer::check_file( $file );

		try {
			$reader = static::create_reader( $file );
			static::process( $reader, $extra );
		} catch ( Exception $e ) {
			$error_response = new \WP_Error( 'import-error', $e->getMessage() );
			return $error_response;
		}


		$response = [ 'status' => 'running' ];

		if ( $import_group ) {
			$response['import_group'] = $import_group;
		}

		return $response;
	}

	

	/**
	 * Creates and returns the XML reader for the Import File
	 *
	 * @param string $file  path of the JSON file.
	 * @throws Exception Could Not Open File to Parse.
	 */
	abstract public static function create_reader( string $file );

	/**
	 * Process Loop over WML file
	 * calls  read_x functions for elements it finds
	 *
	 * @param XMLReader $reader  path of the JSON file.
	 * @param array     $extra Array of extra import configuration passed to the importer.
	 */
	abstract public static function process( $reader, array|bool $extra ): void;

	/**
	 * Creates a group for the import that is passed to ActionScheduler.
	 *
	 * The Action Scheduler group is used to package the items in this import together.
	 */
	public static function import_group(): string {

		if ( self::$import_group ) {
			return self::$import_group;
		}

		self::$import_group = sprintf( 'govpack_%s', time() );

		return self::$import_group;
	}
}
