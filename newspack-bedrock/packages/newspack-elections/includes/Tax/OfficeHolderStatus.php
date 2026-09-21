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
class OfficeHolderStatus extends \Govpack\Abstracts\Taxonomy {

	/**
	 * Post Type slug. Used when registering and referencing
	 */
	const TAX_SLUG = 'govpack_officeholder_status';

	/**
	 * URL slug. Also used for fixtures.
	 */
	const SLUG = 'officeholder_status';

	/**
	 * Register this taxonomy for profiles.
	 */
	public static function register_taxonomy(): void {

	  
		register_taxonomy(
			self::TAX_SLUG,
			self::get_taxonomy_post_types(),
			[
				'labels'            => [
					'name'                       => _x( 'Profile Office Statuses', 'Taxonomy General Name', 'newspack-elections' ),
					'singular_name'              => _x( 'Profile Office Status', 'Taxonomy Singular Name', 'newspack-elections' ),
					'menu_name'                  => __( 'Office Holder Status', 'newspack-elections' ),
					'all_items'                  => __( 'All Statuses', 'newspack-elections' ),
					'parent_item'                => __( 'Parent Status', 'newspack-elections' ),
					'parent_item_colon'          => __( 'Parent Status:', 'newspack-elections' ),
					'new_item_name'              => __( 'New Status Name', 'newspack-elections' ),
					'add_new_item'               => __( 'Add New Status', 'newspack-elections' ),
					'edit_item'                  => __( 'Edit Status', 'newspack-elections' ),
					'update_item'                => __( 'Update Status', 'newspack-elections' ),
					'view_item'                  => __( 'View Status', 'newspack-elections' ),
					'separate_items_with_commas' => __( 'Separate statuses with commas', 'newspack-elections' ),
					'add_or_remove_items'        => __( 'Add or remove statuses', 'newspack-elections' ),
					'choose_from_most_used'      => __( 'Choose from the most used', 'newspack-elections' ),
					'popular_items'              => __( 'Popular Statuses', 'newspack-elections' ),
					'search_items'               => __( 'Search Statuses', 'newspack-elections' ),
					'not_found'                  => __( 'Not Found', 'newspack-elections' ),
					'no_terms'                   => __( 'No statuses', 'newspack-elections' ),
					'items_list'                 => __( 'Statuses list', 'newspack-elections' ),
					'items_list_navigation'      => __( 'Statuses list navigation', 'newspack-elections' )
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
