<?php

namespace Govpack\Blocks;

interface ProfileFieldInterface {

	public function disable_block( $allowed_blocks, $editor_context ): bool;

	/**
	 * Block render handler for .
	 *
	 * @param array  $attributes    Array of shortcode attributes.
	 * @param string $content Post content.
	 * @param \WP_Block $block Reference to the block being rendered .
	 *
	 * @return string HTML for the block.
	 */
	public function render( array $attributes, string $content, \WP_Block $block );

	public function get_value(): mixed;

	public function output(): mixed;
	
	/**
	 * Loads a block from display on the frontend/via render.
	 *
	 * @param array  $attributes array of block attributes.
	 * @param string $content Any HTML or content redurned form the block.
	 * @param WP_Block $template The filename of the template-part to use.
	 */
	public function handle_render( array $attributes, string $content, \WP_Block $block ); 

	/**
	 * Determines if a block is shown during render
	 */
	public function show_block(): bool;

	public function attribute( string $key );

	public function has_attribute( string $key ): mixed;

	/**
	 * These could be moved to a Trait - BlockContextAware
	 */

	public function context( $key ): mixed;

	public function has_context( string $key ): mixed;

	public function has_context_value( $key ): bool;

	public function has_prefixed_context_value( $key ): bool;

	public function get_from_context( $key ): mixed;

	public function get_prefixed_value_from_context( string $key ): mixed;

	public function get_context_prefix(): string;

	public function get_prefixed_context_key( string $key ): string;

	/**
	 * Get the profileId from the context
	 */
	public function get_profile_id();

	public function get_profile();

	public function has_profile_id(): bool;

	public function has_profile(): bool;
	
	public function get_field_key();

	public function get_field();

	public function has_field();
}
