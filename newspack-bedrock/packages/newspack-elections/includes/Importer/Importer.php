<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Importer;

use Exception;
use Govpack\Govpack;
use Govpack\Capabilities;
use Govpack\Importer\Abstracts\AbstractImporter;
use Govpack\PluginAware;
use Govpack\Abstracts\Plugin;
use Govpack\Profile\CPT;

/**
 * Register and handle the "USIO" Importer
 */
class Importer {

	use PluginAware;

	const CSV_EXAMPLE_QUERY_ARG = 'csv-example';
	const CSV_EXAMPLE_FILE_NAME = 'npe-profile-import-template.csv';

	public function __construct(Plugin $plugin){
		$this->plugin($plugin);
	}
	/**
	 * Adds Actions to Hooks
	 */
	public function hooks(): void {

		\add_action( 'rest_api_init', [ __CLASS__, 'register_rest_endpoints' ] );
		
		ChunkedUpload::hooks();
		Actions::hooks();

		\add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
		\add_action( 'admin_init', [ __CLASS__, 'maybe_download_example' ] );
	}

	public static function maybe_download_example() {
		if ( isset( $_GET[ self::CSV_EXAMPLE_QUERY_ARG ] ) ) {
			self::download_example();
			die();
		}
	}

	public static function download_example() {

		header( 'Access-Control-Expose-Headers: Content-Disposition', false );
		header( 'Content-type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . self::CSV_EXAMPLE_FILE_NAME . '"' );

		$example_file = self::example();
		$example_file->download();
		die();
	}

	/**
	 * Adds ASSETS used for importing
	 */
	public  function register_scripts(): void {

		$file = $this->plugin->build_path('importer.asset.php');

		if ( file_exists( $file ) ) {
			$asset_data = require_once $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
		
		$script_handle = 'govpack-importer';

		

		wp_register_script(
			$script_handle,
			$this->plugin->build_url('importer.js'),
			$asset_data['dependencies'] ?? [],
			$asset_data['version'] ?? '',
			true
		);

		wp_script_add_data( $script_handle, 'profiles_url', []);
	}

	/**
	 * Register the REST Routes
	 */
	public static function register_rest_endpoints(): void {

		\register_rest_route(
			Govpack::REST_PREFIX,
			'/import',
			[
				'methods'             => 'GET',
				'callback'            => [
					__CLASS__,
					'import',
				],
				'permission_callback' => function () {
				
					return \current_user_can( 'govpack_import' );
				},
			] 
		);

		\register_rest_route(
			Govpack::REST_PREFIX,
			'/import/progress',
			[
				'methods'             => 'GET',
				'callback'            => [
					__CLASS__,
					'progress',
				],
				'permission_callback' => function () {
				
					return \current_user_can( 'govpack_import' );
				},
			] 
		);

		\register_rest_route(
			Govpack::REST_PREFIX,
			'/import/status',
			[
				'methods'             => 'GET',
				'callback'            => [
					__CLASS__,
					'status',
				],
				'permission_callback' => function () {
				
					return \current_user_can( 'govpack_import' );
				},
			] 
		);

		\register_rest_route(
			Govpack::REST_PREFIX,
			'/import/example',
			[
				'methods'             => 'GET',
				'callback'            => [
					__CLASS__,
					'example',
				],
				'permission_callback' => function () {
					return true;
					//return \current_user_can( 'govpack_import' );
				},
			] 
		);
	}

	public static function example() {
		return CSV::example();
	}
	/**
	 * Called By The REST API to Check the status of an ongoing import
	 *
	 * @return string[]
	 *
	 * @psalm-return array{status: 'done'|'not_running'|'running'}
	 */
	public static function status(): array {
		return AbstractImporter::status();
	}

	

	/**
	 * Called By The REST API to Check the progress of an ongoing import
	 *
	 * @psalm-return array{total?: mixed, done?: mixed, todo?: mixed, failed?: mixed}
	 */
	public static function progress(): array {
		return self::progress_check();
	}

	/**
	 * Called By The REST API to Kickoff an Import and check its process
	 *
	 * @return (mixed|string)[]|\WP_Error
	 *
	 * @psalm-return \WP_Error|array{status: 'done'|'running', import_group?: mixed}
	 */
	public static function import(): array|\WP_Error {

		$file  = get_option( 'govpack_import_path', false );
		$extra = get_option( 'govpack_import_extra_args', false );

		if ( ! $file ) {
			return new \WP_Error( '500', 'No File For Import' );
		}
		

		$importer = self::make( $file );

		return $importer::import( $file, false, $extra );
	}

	/**
	 * Checks the file exists in the Govpack uploads folder
	 *
	 * @param string $file  Name of the JSON file.
	 *
	 * @throws \Exception File Not Found.
	 */
	public static function check_file( $file ): string {

		if ( file_exists( $file ) ) {
			return $file;
		}

		$path = wp_get_upload_dir();
		$path = $path['basedir'] . '/govpack/' . $file;

		if ( file_exists( $path ) ) {
			return $path;
		}

		throw new \Exception( 'File Not Found' );
	} 

	/**
	 * Checks the file exists in the Govpack uploads folder
	 *
	 * @param string $file  Name of the JSON file.
	 *
	 * @throws \Exception File Not Found.
	 */
	public static function filetype( $file ): string {

	
		if ( file_exists( $file ) ) {
			return pathinfo( $file, PATHINFO_EXTENSION );
		}

		$path = wp_get_upload_dir();
		$path = $path['basedir'] . '/govpack/' . $file;


		if ( file_exists( $path ) ) {

			return pathinfo( $path, PATHINFO_EXTENSION );
		}

		throw new \Exception( 'File Not Found' );
	} 

	/**
	 * Create the importer based on the filetype passed in
	 *
	 * @param string $file Path of the file to import.
	 *
	 * @throws \Exception File Not Found.
	 */
	public static function make( $file ): CSV|false {
		
		try {
			self::check_file( $file );
			$file_type = self::filetype( $file );
			if ( 'csv' === $file_type ) {
				return CSV::make();
			} else {
				return false;
			}       
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Custom function that gets counts of Action Scheduler actions
	 *
	 * @param array $args XML node being processed.
	 *
	 * @psalm-return array<never, never>|int<min, max>
	 */
	public static function as_count_scheduled_actions( $args = [] ): array|int {
		if ( ! \ActionScheduler::is_initialized( __FUNCTION__ ) ) {
			return [];
		}
		$store = \ActionScheduler::store();
		foreach ( [ 'date', 'modified' ] as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$args[ $key ] = as_get_datetime_object( $args[ $key ] );
			}
		}

		$count = $store->query_actions( $args, 'count' );

		// $count may contain an extra cleanup action which we should remove from the count
		// can't be sure what the return type of the query will, force result though intval to make sure.
		$count = intval( $count );
		if ( $count > 1 ) {
			--$count;
		}

		return $count;
	}

	/**
	 * Call the Import/Action Scheduler backend and see progress
	 *
	 * @psalm-return array{total?: mixed, done?: mixed, todo?: mixed, failed?: mixed}
	 */
	public static function progress_check(): array {

		$import_group = get_option( 'govpack_import_group', false );

		if ( ! $import_group ) {
			return [];
		}

		return [
			'total'  => self::as_count_scheduled_actions(
				[
					'group' => $import_group,
				]
			),
			'done'   => self::as_count_scheduled_actions(
				[
					'group'  => $import_group,
					'status' => \ActionScheduler_Store::STATUS_COMPLETE,
				]
			),
			'todo'   => self::as_count_scheduled_actions(
				[
					'group'  => $import_group,
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				]
			),
			'failed' => self::as_count_scheduled_actions(
				[
					'group'  => $import_group,
					'status' => \ActionScheduler_Store::STATUS_FAILED,
				]
			),
		];
	}

	/**
	 * Removes stored options from the last import
	 *
	 * @return void
	 */
	public static function check_for_stuck_import() {

		$progress_check = self::progress_check();

		// no known import on the go.
		if ( empty( $progress_check ) ) {
			return;
		}

		// there are no items left todo, reset the importer.
		if ( 0 === intval( $progress_check['todo'] ) ) {
			self::clean();
			return;
		}
	}

	  



	/**
	 * Reset all Import Funcions to empty
	 *
	 * @return void
	 */
	public static function clear() {
		AbstractImporter::cancel();
		delete_option( 'govpack_import_path' );

		if ( ! \ActionScheduler::is_initialized( __FUNCTION__ ) ) {
			return;
		}

	 
		$store = \ActionScheduler::store();
		$store->cancel_actions_by_group( 'govpack' );
		$store->delete_actions_by_group( 'govpack' );
	}

	/**
	 * Removes stored options from the last import
	 */
	public static function clean(): void {
		// delete the options cached for the import.
		\delete_option( 'govpack_import_extra_args' );
		\delete_option( 'govpack_import_path' );
		\delete_option( 'govpack_import_processing' );
		\delete_option( 'govpack_import_group' );
	}


	/**
	 * For a Given Post ID, look for the image meta value, sideload it and save it as the post thumbnail
	 *
	 * @param integer $id Post ID to lookup and sideload.
	 * @param string  $meta_key key used to find a sideload url.
	 *
	 * @throws \Exception Profile errors.
	 *
	 * @return true
	 */
	public static function sideload( $id = null, $meta_key = 'photo' ): bool {

	
		if ( ! $id ) {
			throw new \Exception( 'No Profile ID given' );
		}

		$post = get_post( $id );
		if ( ! $post ) {
			throw new \Exception( sprintf( 'No Entity with ID %s exists', $id ) ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( 'govpack_profiles' !== $post->post_type ) {
			throw new \Exception( sprintf( 'No Profile with ID %s exists', $id ) ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( ! $post->{$meta_key} ) {
			throw new \Exception( sprintf( 'Profile %s Does not have an `%s` meta field', $id, $meta_key ) ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( ! \wp_http_validate_url( $post->{$meta_key} ) ) {
			throw new \Exception( sprintf( 'Image meta field for profile %s does not contain a valid url', $id ) ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		
		


		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$url = $post->{$meta_key};
	
		try {
			
			$sideload = \media_sideload_image( $url, $id, '', 'id' );
			
			if ( \is_wp_error( $sideload ) ) {
				throw new \Exception( sprintf( 'Side load failed for profile %s', $id ) );
			}

			if ( ! \set_post_thumbnail( $id, $sideload ) ) {
				throw new \Exception( sprintf( 'Side load failed for to side post thumbnail/featured image for profile %s', $id ) );
			}       
		} catch ( Exception $e ) {
			return true;
		}

		return true;
	}
}
