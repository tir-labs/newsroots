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
class ProfileBio extends \Govpack\Blocks\ProfileFieldText {

	public string $block_name = 'npe/profile-bio';
	public $field_type        = 'text';
	

	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileBio' );
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
			<?php echo \wp_kses_post( $this->output() ); ?>
		</div>
		<?php
	}

	public function output(): string {

		$excerpt_length        = $this->attributes['bioLength'];
		$excerpt               = (string) $this->get_value();
		$show_more_on_new_line = ! isset( $this->attributes['showMoreOnNewLine'] ) || $this->attributes['showMoreOnNewLine'];
		$more_text             = ! empty( $this->attributes['moreText'] ) ? 
			'<a class="wp-block-govpack-profile-bio__more-link" href="' . esc_url( get_the_permalink( $this->context['postId'] ) ) . '">' . wp_kses_post( $this->attributes['moreText'] ) . '</a>' :
			'';
	
		if ( isset( $excerpt_length ) ) {
			$excerpt = wp_trim_words( $excerpt, $excerpt_length );
		}

		$content = '<p class="wp-block-govpack-profile-bio__excerpt">' . $excerpt;

		if ( $show_more_on_new_line && ! empty( $more_text ) ) {
			$content .= '</p><p class="wp-block-govpack-profile-bio__more-text">' . $more_text;
		} else {
			$content .= $more_text;
		}

		$content .= '</p>';

		return $content;
	}

	public function variations(): array {
		return [];
	}
}
