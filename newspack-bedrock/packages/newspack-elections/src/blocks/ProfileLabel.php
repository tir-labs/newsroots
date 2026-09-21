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
class ProfileLabel extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'govpack/profile-label';

	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileLabel' );
	}

	
	/**
	 * Loads a block from display on the frontend/via render.
	 *
	 * @param array  $attributes array of block attributes.
	 * @param string $content Any HTML or content redurned form the block.
	 * @param WP_Block $template The filename of the template-part to use.
	 */
	public function handle_render( array $attributes, string $content, WP_Block $block ) {
		
		?>
		<div <?php echo get_block_wrapper_attributes(); ?>>
			<?php echo wp_kses_post( $this->output() ); ?>
		</div>
		<?php
	}


	public function output(): string {

		
		if ( $this->has_label_from_attributes() ) {
			return $this->attribute( 'label' );
		} 
		
		if ( $this->has_field() ) {
			return $this->get_field()->label;
		}

		return '';
	}

	public function show_block(): bool {
		
		return ( $this->show_label() && $this->has_label() );
	}

	public function show_label(): bool {
		$row_show_label   = ( $this->has_context( 'showLabel' ) ? $this->context( 'showLabel' ) : null );
		$group_show_label = ( $this->has_context( 'showLabels' ) ? $this->context( 'showLabels' ) : null );

		$show_label = $row_show_label ?? $group_show_label ?? true;
		return $show_label;
	}

	public function has_label(): bool {
		return $this->has_label_from_attributes() || $this->has_label_from_context();
	}

	private function has_label_from_attributes(): bool {
		
		if (
			! $this->has_attribute( 'label' ) ||
			( $this->attribute( 'label' ) === '' ) ||
			( $this->attribute( 'label' ) === false ) ||
			( $this->attribute( 'label' ) === null )
		) {
			return false;
		}

		return true;
	}

	private function has_label_from_context(): bool {
		return true;
	}
}
