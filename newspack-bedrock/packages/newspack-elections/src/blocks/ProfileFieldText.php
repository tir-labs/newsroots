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
class ProfileFieldText extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'npe/profile-field-text';
	public $field_type        = 'text';
	

	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileFieldText' );
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

	public function show_block(): bool {
		
		if ( $this->get_value() === '' ) {
			return false;
		}

		return true;
	}


	
	public function variations(): array {
		
		return $this->create_field_variations();
	}

	public function create_field_variations(): array {

		$variations = [];

		foreach ( \Govpack\Profile\CPT::fields()->of_format( $this->field_type ) as $field ) {
			$variation = [
				'category'    => 'govpack-profile-fields',
				'name'        => sprintf( 'profile-field-%s', $field->slug ),
				'title'       => $field->label,
				'description' => sprintf(
					/* translators: %s: taxonomy's label */
					__( 'Display Profile Field: %s', 'newspack-elections' ),
					$field->label
				),
				'attributes'  => [
					'fieldType' => $field->type->slug,
					'fieldKey'  => $field->slug,
				],
				'isActive'    => [ 'fieldKey' ],
				'scope'       => [ 'inserter' ],
				'icon'        => $field->type->variation_icon(),
			];

			$variations[] = $variation;
		}

		return $variations;
	}
}
