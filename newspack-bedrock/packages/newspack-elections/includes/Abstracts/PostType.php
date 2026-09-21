<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Abstracts;

use Govpack\Helpers;

/**
 * Abstract class for registering and handling of custom post types.
 */
abstract class PostType {

	/**
	 * WordPress Hooks
	 */
	public static function hooks(): void {
		add_action( 'init', [ get_called_class(), 'register_post_type' ] );
		add_filter( 'manage_' . static::CPT_SLUG . '_posts_columns', [ __CLASS__, 'manage_columns' ] );
	}

	/**
	 * Adds the post_type to array of supported post types.
	 *
	 * @param array $post_types   Array of post types.
	 *
	 * @return array
	 */
	public static function add_post_type( array $post_types ): array {
		$post_types[] = static::CPT_SLUG;

		return $post_types;
	}

	/**
	 * Remove tags column from profile admin screen.
	 *
	 * @param string[] $columns The column header labels keyed by column ID.
	 * @return array
	 */
	public static function manage_columns( array $columns ): array {
		unset( $columns['tags'] );
		return $columns;
	}

	/**
	 * Get All posts in a post type. Use sparingly.
	 *
	 * @return array
	 */
	public static function get_all(): array {
		$posts = new \WP_Query(
			[
				'post_type'      => static::CPT_SLUG,
				'post_status'    => 'all',
				'posts_per_page' => -1,
			]
		);

		return $posts->posts;
	}
}
