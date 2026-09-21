<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Admin;

use Exception;

/**
 * Create an Admin menu
 */
class Menu {
	/** 
	 * Title added to broser bag, eg <title> 
	 */ 
	protected string $page_title; 

	/**
	 * Title in the Menu
	 */
	protected string $menu_title; 

	/**
	 * Unique ID for the menu in urls etc
	 */
	protected string $menu_slug;
	
	/** 
	 * Call back to render the page
	 */ 
	protected mixed $function; 

	/** 
	 * WP_capability required or you'll get an error
	 */ 
	protected string $capability = 'manage_options'; 
	
	/** 
	 * Where in the menu will our item be located
	 */ 
	protected int $position = 30; 

	/** 
	 *  URL for icon placement
	 */ 
	protected string $icon_url = ''; 
	
	/** 
	 * Array of children to include in the menu
	 * 
	 * @var array<int, MenuItem>
	 */  
	protected array $items = []; 

	/**
	 * Generic set and return so we can use a fluent style
	 *
	 * @param string $key string of object property to set.
	 * @param mixed $value caluw to set the property.
	 */
	public function set( string $key, mixed $value ): static {
		$this->$key = $value;
		return $this;
	}

	/**
	 * Set page title
	 *
	 * @param string $value value to set page title.
	 */
	public function set_page_title( string $value ): static {
		return $this->set( 'page_title', $value );
	}

		/**
	 * Set page title
	 *
	 * @param string $value value to set page title.
	 */
	public function set_menu_title( string $value ): static {
		return $this->set( 'menu_title', $value );
	}
	/**
	 * Set menu title
	 *
	 * @param string $value value to set menu title.
	 */
	public function set_menu_slug( string $value ): static {
		return $this->set( 'menu_slug', $value );
	}
	/**
	 * Set capability
	 *
	 * @param string $value value to set capability.
	 */
	public function set_capability( string $value ): static {
		return $this->set( 'capability', $value );
	}
	/**
	 * Set position to use in menu
	 *
	 * @param int $value value to set position to use in menu.
	 */
	public function set_position( int $value ): static {
		return $this->set( 'position', $value );
	}
	/**
	 * Set icon url
	 *
	 * @param string $value value to set icon url.
	 */
	public function set_icon( string $value ): static {
		return $this->set( 'icon_url', $value );
	}
	/**
	 * Set callback function
	 *
	 * @param array|callable $value value to set callback function.
	 */
	public function set_callback( array|callable $value ): static {
		return $this->set( 'function', $value );
	}
	/**
	 * Add a submenu item
	 *
	 * @param MenuItem $item Submemnu Item.
	 */
	public function add_item( MenuItem $item ): void {

		$this->items[] = $item->set_parent_slug( $this->menu_slug );
	}
	
	/** 
	 * Checks that the required items are included.
	 * 
	 *  @param array<int, string> $required Require properties.
	 *  @throws Exception Required property Missing.
	 *  @throws Exception Required property is an empty string.
	 */ 
	public function check_required( array $required ): bool {
		
		foreach ( $required as $key ) {
			/**
			 * @var string $key
			 */
			if ( null === $this->$key ) {
				throw new Exception( 'Required Menu Property ' . $key . ' is unset' ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			if ( '' === $this->$key ) {
				throw new Exception( 'Required Menu Property ' . $key . ' is an empty string and much have a value' ); //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}
		}

		return true;
	}

	/**
	 * Creates The menu and calls main WP menthods to do it.
	 *
	 * @return void
	 */
	public function create() {
		try {
			

			$this->check_required(
				[
					'page_title',
					'menu_title',
					'menu_slug',
					'function',
				]
			);

			\add_action(
				'admin_menu',
				function () {

					\add_menu_page( 
						$this->page_title, 
						$this->menu_title, 
						$this->capability, //phpcs:ignore WordPress.WP.Capabilities.Undetermined
						$this->menu_slug,
						function (): mixed {
							return $this->function; },
						$this->icon_url,
						$this->position 
					);

					foreach ( $this->items as $item ) {
						$item->create();
					}
				},
				9 
			);
		
		} catch ( \Exception $e ) {
			\wp_die( \esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Add submenus for post types.
	 *
	 * @access private
	 *
	 * @since 3.1.0
	 */
	public static function add_taxonomy_submenus(): void {
	
		/**
		 * @var \WP_Taxonomy $tax 
		 */
		foreach ( \get_taxonomies( [ 'show_ui' => true ], 'objects' ) as $tax ) {
			
			if ( ! isset( $tax->show_in_which_menu ) ) {
				continue;
			}

		
			// Sub-menus only.
			if ( ! $tax->show_in_which_menu || 'govpack' !== $tax->show_in_which_menu ) {
				continue;
			}


			\add_submenu_page( 
				$tax->show_in_which_menu, 
				(string) $tax->labels->name, 
				(string) $tax->labels->name, 
				(string) $tax->cap->manage_terms, //phpcs:ignore WordPress.WP.Capabilities.Undetermined
				'edit-tags.php?taxonomy=' . $tax->name . '&post_type=govpack_profiles' 
			);
		}
	}
}
