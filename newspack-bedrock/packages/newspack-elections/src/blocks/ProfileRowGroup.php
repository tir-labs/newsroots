<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Blocks;

use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Register and handle the block.
 */
class ProfileRowGroup extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'npe/profile-row-group';


	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileRowGroup' );
	}

	/**
	 * Loads a block from display on the frontend/via render.
	 *
	 * @param array  $attributes array of block attributes.
	 * @param string $content Any HTML or content redurned form the block.
	 * @param WP_Block $template The filename of the template-part to use.
	 */
	public function handle_render( array $attributes, string $content, \WP_Block $block ) {
		
		?>
		<div <?php echo get_block_wrapper_attributes(); ?>>
			<?php 
				// $content will be escaped already as passed up from other blocks
				echo $content;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</div>
		<?php
	}

	public function output(): string {
		return '';
	}
}
