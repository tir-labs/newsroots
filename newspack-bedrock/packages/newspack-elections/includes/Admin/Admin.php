<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Admin;

use Govpack\Capabilities;
use Govpack\Profile\CPT as Profile;
use Govpack\Govpack;

use Exception;

/**
 * GovPack Admin Hooks
 */
class Admin {

	//use \Govpack\Instance;

	private Govpack $plugin;

	public function __construct( Govpack $plugin ) {
		$this->plugin = $plugin;
	}
	/**
	 * Register Hooks for usage in wp-admin.
	 */
	public function hooks(): void {
		
		\add_action( 'admin_menu', [ $this, 'create_menus' ], 1, 1 );
		\add_action( 'admin_menu', [ $this, 'alter_menu_icon' ], 99, 1 );

		\add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 100, 1 );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'load_assets' ], 101, 1 );
		//\add_action( 'block_categories_all', [ __CLASS__, 'block_categories' ], 10, 2 );                
		\add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_block_editor_assets' ] );
		\add_action( 'current_screen', [ __CLASS__, 'conditional_hooks' ] );
	
		\add_action( 'admin_init', [ $this, 'exporter' ], 11, 1 );

	}

	public function alter_menu_icon(): void {
		global $menu, $submenu;

		foreach($menu as $key => $inner_menu){
			if($inner_menu[2] !== 'edit.php?post_type=' . Profile::CPT_SLUG){
				continue;
			}

			$menu[$key][6] = $this->create_menu_svg();
			break;
		}

	}

	public function exporter() {
		
		$exporter = new Export($this->plugin);
		$exporter->hooks();
	}

	/**
	 * Register Block Assets.
	 */
	public static function enqueue_block_editor_assets(): void {
	}

	/**
	 * Callback that adds a Category to the Block Editor for Govpack
	 * 
	 * @param array $categories The existing Block Categories.
	 */

	
	
	/**
	 * Utility Function that redirects to Profiles archive.
	 */
	public static function redirect_to_profiles(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . Profile::CPT_SLUG ), 302 );
		exit;
	}

	/**
	 * Used to check if we're loaing the main "Govpack" page, redirect to the profile stable is we are.
	 */
	public static function conditional_hooks(): void {
		$screen = get_current_screen();
		

		if ( ! $screen ) {
			return;
		}

		switch ( $screen->base ) {
			case 'toplevel_page_govpack':
				self::redirect_to_profiles();
				break;
			case 'options-permalink':
				PermalinkSettings::hooks();
				break;
		}
	}

	public function get_menu_svg() {

		return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
  			<path d="M13.5 6.19L14.56 7.25L11.5 10.31L9.44 8.25L10.5 7.19L11.5 8.19L13.5 6.19ZM20.75 10.25V18.25C20.75 18.8467 20.5129 19.419 20.091 19.841C19.669 20.2629 19.0967 20.5 18.5 20.5H5.5C4.90326 20.5 4.33097 20.2629 3.90901 19.841C3.48705 19.419 3.25 18.8467 3.25 18.25V10.25C3.25 9.65326 3.48705 9.08097 3.90901 8.65901C4.33097 8.23705 4.90326 8 5.5 8H7.25V5.75C7.25 5.15326 7.48705 4.58097 7.90901 4.15901C8.33097 3.73705 8.90326 3.5 9.5 3.5H14.5C15.0967 3.5 15.669 3.73705 16.091 4.15901C16.5129 4.58097 16.75 5.15326 16.75 5.75V8H18.5C19.0967 8 19.669 8.23705 20.091 8.65901C20.5129 9.08097 20.75 9.65326 20.75 10.25ZM8.75 11.5H15.25V5.75C15.25 5.55109 15.171 5.36032 15.0303 5.21967C14.8897 5.07902 14.6989 5 14.5 5H9.5C9.30109 5 9.11032 5.07902 8.96967 5.21967C8.82902 5.36032 8.75 5.55109 8.75 5.75V11.5ZM19.25 16.5H4.75V18.25C4.75 18.664 5.086 19 5.5 19H18.5C18.6989 19 18.8897 18.921 19.0303 18.7803C19.171 18.6397 19.25 18.4489 19.25 18.25V16.5ZM19.25 10.25C19.25 10.0511 19.171 9.86032 19.0303 9.71967C18.8897 9.57902 18.6989 9.5 18.5 9.5H16.75V11.5H17.75V13H6.25V11.5H7.25V9.5H5.5C5.30109 9.5 5.11032 9.57902 4.96967 9.71967C4.82902 9.86032 4.75 10.0511 4.75 10.25V15H19.25V10.25Z"/>
		</svg>';

	}

	public function create_menu_svg() {
		return sprintf( 'data:image/svg+xml;base64,%s', base64_encode( $this->get_menu_svg() ) );
	}
	
	/**
	 * Creates the Govpack Menu in the Dashboard Navigation
	 */
	public function create_menus(): void {


		$import_item = (new MenuItem())
			->set_parent_slug("edit.php?post_type=govpack_profiles")
			->set_page_title( __( 'Import', 'newspack-elections' ) )
			->set_menu_title( __( 'Import', 'newspack-elections' ) )
			->set_menu_slug( 'govpack_import' )
			->set_capability( Capabilities::CAN_IMPORT )
			->set_callback( [ '\Govpack\Admin\Pages\Import', 'view' ] )
			->create();
		
		
		$export_item = (new MenuItem())
			->set_parent_slug("edit.php?post_type=govpack_profiles")
			->set_page_title( __( 'Export', 'newspack-elections' ) )
			->set_menu_title( __( 'Export', 'newspack-elections' ) )
			->set_menu_slug( 'govpack_export' )
			->set_capability( Capabilities::CAN_EXPORT )
			->set_callback( [ '\Govpack\Admin\Pages\Export', 'view' ] )
			->create();

		
	}

	/**
	 * Register Govpack JS/CSS Assets for wp-admin 
	 */
	public function register_assets(): void {

		$file = $this->plugin->build_path('admin.asset.php');

		if ( file_exists( $file ) ) {
			$asset_data = require_once $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}

		wp_register_style(
			'govpack-admin-style',
			$this->plugin->build_url('admin.css'),
			$asset_data['dependencies'] ?? '',
			$asset_data['version'] ?? '',
			'all'
		);

		wp_register_script(
			'govpack-admin-script',
			$this->plugin->build_url('admin.js'),
			$asset_data['dependencies'] ?? '',
			$asset_data['version'] ?? '',
			true
		);


		$file = $this->plugin->build_path('profile-editor.asset.php');

		if ( file_exists( $file ) ) {
		
			$asset_data = require_once $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}

		wp_register_script(
			'govpack-profile-meta-editor',
			$this->plugin->build_url('profile-editor.js'),
			$asset_data['dependencies'] ?? [],
			$asset_data['version'] ?? '',
			true
		);
		
		wp_register_style(
			'govpack-profile-meta-editor-style',
			$this->plugin->build_url('profile-editor.css'),
			[],
			true,
			'all'
		);
	}

	/**
	 * Conditionally Enqueue JS/CSS Assets depending on wp_screen
	 */
	public static function load_assets(): void {
		\wp_enqueue_style( 'govpack-admin-style' );
		
		$screen = get_current_screen();

		if ( $screen === null ) {
			return;
		}

		if ( true === $screen->is_block_editor() && 'govpack_profiles' === $screen->post_type ) {
			
			\wp_enqueue_script( 'govpack-profile-meta-editor' );
			\wp_enqueue_style( 'govpack-profile-meta-editor-style' );
		}
	}
}
