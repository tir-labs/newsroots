<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Blocks;

defined( 'ABSPATH' ) || exit;

/**
 * Register and handle the block.
 */
class LegacyProfileSelf extends \Govpack\Blocks\LegacyProfile {

	public string $block_name = 'govpack/profile-self';
	public $template          = 'profile-self';


	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/LegacyProfileSelf' );
	}

	/**
	 * Renders the block.
	 *
	 * @param array  $attributes Attribues from the block.
	 * @param string $content contentf rom the block.
	 * @return string
	 */
	public function render( $attributes, $content = null, $block = null ) {

		$attributes['profileId'] = get_queried_object_id();

		
		return $this->handle_render( $attributes, $content, $block );
	}

	public function disable_block( $allowed_blocks, $editor_context ): bool {
		return false;
	}
}   
