<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Tax;

/**
 * Register and handle the "Legislative_Body" Taxonomy.
 */
class LegislativeBody extends \Govpack\Abstracts\Taxonomy {

	/**
	 * Post Type slug. Used when registering and referencing
	 */
	const TAX_SLUG = 'govpack_legislative_body';

	/**
	 * URL slug. Also used for fixtures.
	 */
	const SLUG = 'legislative_body';

	/**
	 * Register this taxonomy for profiles.
	 */
	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAX_SLUG,
			self::get_taxonomy_post_types(),
			[
				'labels'             => [
					'name'                       => _x( 'Offices', 'Taxonomy General Name', 'newspack-elections' ),
					'singular_name'              => _x( 'Office', 'Taxonomy Singular Name', 'newspack-elections' ),
					'menu_name'                  => __( 'Profile Office', 'newspack-elections' ),
					'all_items'                  => __( 'All Offices', 'newspack-elections' ),
					'parent_item'                => __( 'Parent Office', 'newspack-elections' ),
					'parent_item_colon'          => __( 'Parent Office:', 'newspack-elections' ),
					'new_item_name'              => __( 'New Office Name', 'newspack-elections' ),
					'add_new_item'               => __( 'Add New Office', 'newspack-elections' ),
					'edit_item'                  => __( 'Edit Office', 'newspack-elections' ),
					'update_item'                => __( 'Update Office', 'newspack-elections' ),
					'view_item'                  => __( 'View Office', 'newspack-elections' ),
					'separate_items_with_commas' => __( 'Separate Offices with commas', 'newspack-elections' ),
					'add_or_remove_items'        => __( 'Add or remove Offices', 'newspack-elections' ),
					'choose_from_most_used'      => __( 'Choose from the most used', 'newspack-elections' ),
					'popular_items'              => __( 'Popular Offices', 'newspack-elections' ),
					'search_items'               => __( 'Search Offices', 'newspack-elections' ),
					'not_found'                  => __( 'Not Found', 'newspack-elections' ),
					'no_terms'                   => __( 'No Offices', 'newspack-elections' ),
					'items_list'                 => __( 'Offices list', 'newspack-elections' ),
					'items_list_navigation'      => __( 'Offices list navigation', 'newspack-elections' )
				],
				'public'             => true,
				'hierarchical'       => true,
				'rewrite'            => [
					'slug'         => self::SLUG,
					'with_front'   => false,
					'hierarchical' => true,
				],
				'meta_box_cb'        => false,
				'show_admin_column'  => true,
				'show_in_rest'       => true,
				'show_ui'            => true,
				'show_in_which_menu' => 'govpack',
				'show_in_nav_menus'  => false,
			]
		);
	}
}
