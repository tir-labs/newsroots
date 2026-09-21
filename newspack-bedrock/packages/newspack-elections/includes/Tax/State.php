<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Tax;

/**
 * Register and handle the "State" Taxonomy.
 */
class State extends \Govpack\Abstracts\Taxonomy {

	/**
	 * Post Type slug. Used when registering and referencing
	 */
	const TAX_SLUG = 'govpack_state';

	/**
	 * URL slug. Also used for fixtures.
	 */
	const SLUG = 'state';

	/**
	 * Register this taxonomy for profiles.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAX_SLUG,
			self::get_taxonomy_post_types(),
			[
				'labels'             => [
					'name'                       => _x( 'Profile States', 'Taxonomy General Name', 'newspack-elections' ),
					'singular_name'              => _x( 'Profile State', 'Taxonomy Singular Name', 'newspack-elections' ),
					'menu_name'                  => __( 'States', 'newspack-elections' ),
					'all_items'                  => __( 'All States', 'newspack-elections' ),
					'parent_item'                => __( 'Parent State', 'newspack-elections' ),
					'parent_item_colon'          => __( 'Parent State:', 'newspack-elections' ),
					'new_item_name'              => __( 'New State Name', 'newspack-elections' ),
					'add_new_item'               => __( 'Add New State', 'newspack-elections' ),
					'edit_item'                  => __( 'Edit State', 'newspack-elections' ),
					'update_item'                => __( 'Update State', 'newspack-elections' ),
					'view_item'                  => __( 'View State', 'newspack-elections' ),
					'separate_items_with_commas' => __( 'Separate states with commas', 'newspack-elections' ),
					'add_or_remove_items'        => __( 'Add or remove states', 'newspack-elections' ),
					'choose_from_most_used'      => __( 'Choose from the most used', 'newspack-elections' ),
					'popular_items'              => __( 'Popular States', 'newspack-elections' ),
					'search_items'               => __( 'Search States', 'newspack-elections' ),
					'not_found'                  => __( 'Not Found', 'newspack-elections' ),
					'no_terms'                   => __( 'No states', 'newspack-elections' ),
					'items_list'                 => __( 'States list', 'newspack-elections' ),
					'items_list_navigation'      => __( 'States list navigation', 'newspack-elections' ),
				],
				'public'             => true,
				'hierarchical'       => false,
				'rewrite'            => [
					'slug'         => self::SLUG,
					'with_front'   => false,
					'hierarchical' => false,
				],
				'meta_box_cb'        => false,
				'show_admin_column'  => true,
				'show_in_rest'       => true,
				'show_ui'            => true,
				'show_in_nav_menus'  => false,
				'show_in_which_menu' => 'govpack',
			]
		);
	}
}
