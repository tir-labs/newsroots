<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Tax;

/**
 * Register and handle the "OfficeHolder_Status" Taxonomy.
 */
class OfficeHolderTitle extends \Govpack\Abstracts\Taxonomy {

	/**
	 * Post Type slug. Used when registering and referencing
	 */
	const TAX_SLUG = 'govpack_officeholder_title';

	/**
	 * URL slug. Also used for fixtures.
	 */
	const SLUG = 'officeholder_title';

	/**
	 * Register this taxonomy for profiles.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAX_SLUG,
			self::get_taxonomy_post_types(),
			[
				'labels'            => [
					'name'                       => _x( 'Profile Office Titles', 'Taxonomy General Name', 'newspack-elections' ),
					'singular_name'              => _x( 'Profile Office Title', 'Taxonomy Singular Name', 'newspack-elections' ),
					'menu_name'                  => __( 'Titles', 'newspack-elections' ),
					'all_items'                  => __( 'All Titles', 'newspack-elections' ),
					'parent_item'                => __( 'Parent Title', 'newspack-elections' ),
					'parent_item_colon'          => __( 'Parent Title:', 'newspack-elections' ),
					'new_item_name'              => __( 'New Title Name', 'newspack-elections' ),
					'add_new_item'               => __( 'Add New Title', 'newspack-elections' ),
					'edit_item'                  => __( 'Edit Title', 'newspack-elections' ),
					'update_item'                => __( 'Update Title', 'newspack-elections' ),
					'view_item'                  => __( 'View Title', 'newspack-elections' ),
					'separate_items_with_commas' => __( 'Separate officeholder Titles with commas', 'newspack-elections' ),
					'add_or_remove_items'        => __( 'Add or remove officeholder Titles', 'newspack-elections' ),
					'choose_from_most_used'      => __( 'Choose from the most used', 'newspack-elections' ),
					'popular_items'              => __( 'Popular Titles', 'newspack-elections' ),
					'search_items'               => __( 'Search Titles', 'newspack-elections' ),
					'not_found'                  => __( 'Not Found', 'newspack-elections' ),
					'no_terms'                   => __( 'No officeholder Titles', 'newspack-elections' ),
					'items_list'                 => __( 'Titles list', 'newspack-elections' ),
					'items_list_navigation'      => __( 'Titles list navigation', 'newspack-elections' ),
				],
				'public'            => true,
				'hierarchical'      => true,
				'rewrite'           => [
					'slug'         => self::SLUG,
					'with_front'   => false,
					'hierarchical' => false,
				],
				'meta_box_cb'       => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'show_ui'           => true,
				'show_in_nav_menus' => false,
			]
		);
	}
}
