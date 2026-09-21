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
class ProfileSeparator extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'npe/profile-separator';
	
	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileSeparator' );
	}

	/**
	 * Block render handler for .
	 *
	 * @param array  $attributes    Array of shortcode attributes.
	 * @param string $content Post content.
	 * @param WP_Block $block Reference to the block being rendered .
	 *
	 * @return string HTML for the block.
	 */
	//public function render( array $attributes, ?string $content = null, ?WP_Block $block = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	//}

	/**
	 * Loads a block from display on the frontend/via render.
	 *
	 * @param array  $attributes array of block attributes.
	 * @param string $content Any HTML or content redurned form the block.
	 * @param WP_Block $template The filename of the template-part to use.
	 */
	public function handle_render( array $attributes, string $content, WP_Block $block ) {

		$color_block_styles = [];

		$preset_color               = ( array_key_exists( 'separatorColor', $this->attributes ) && $this->attribute( 'separatorColor' ) ) ? "var:preset|color|{$this->attribute('separatorColor')}" : null;
		$custom_color               = array_key_exists( 'customSeparatorColor', $this->attributes ) ? $this->attribute( 'customSeparatorColor' ) : null;
		$color_block_styles['text'] = $preset_color ? $preset_color : $custom_color;

		$color_styles = wp_style_engine_get_styles( [ 'color' => $color_block_styles ], [ 'convert_vars_to_classnames' => true ] );

		$extra_attributes = [];


		if ( ! empty( $color_styles['classnames'] ) ) {
			$extra_attributes['class'] = $color_styles['classnames'];
		}
	
		if ( ! empty( $color_styles['css'] ) ) {
			$extra_attributes['style'] = $color_styles['css'];
		}

		$height_css = sprintf( '%s:%s;', 'border-top-width', $this->attribute( 'height' ) );

		if ( ! isset( $extra_attributes['style'] ) ) {
			$extra_attributes['style'] = '';
		}

		$extra_attributes['style'] .= $height_css;

		?>
		<hr <?php echo get_block_wrapper_attributes( $extra_attributes ); ?> />
		<?php
	}
}
