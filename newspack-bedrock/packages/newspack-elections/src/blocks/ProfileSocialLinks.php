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
class ProfileSocialLinks extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'npe/profile-social-links';


	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileSocialLinks' );
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
		<ul 
		<?php
		echo get_block_wrapper_attributes(
			[
				'class' => $this->classnames(
					'wp-block-social-links',
					$this->attribute( 'size' ),
					[
						'has-visible-labels'        => $this->attribute( 'showLabels' ),
						'has-icon-color'            => $this->attribute( 'iconColorValue' ),
						'has-icon-background-color' => $this->attribute( 'iconBackgroundColorValue' ),
					]
				),
			]
		);
		?>
			>
			<?php 
				// $content will be escaped already as passed up from other blocks
				echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			?>
		</ul>
		<?php
	}

	public function output(): string {
		return '';
	}

	public function classnames( string|array ...$args ) {

		$classes = [];

		foreach ( $args as $arg ) {

			if ( is_string( $arg ) && ( $arg !== '' ) ) {
				$classes[] = trim( $arg );
				continue;
			}

			if ( \is_array( $arg ) ) {
				foreach ( $arg as $key => $value ) {
					if ( is_int( $key ) ) {
						$classes[] = trim( $value );
						continue;
					}

					if ( $value === true ) {
						$classes[] = trim( $key );
						continue;
					}
				}
			}
		}
		
		return implode( ' ', $classes );

		/*
		die();

		if ( is_array( ( $classnames ) ) ) {
			$classnames = trim( join( ' ', $classnames ) );
		}

		$selection = [];
		foreach ( $candidates as $key => $value ) {
			if ( is_int( $key ) ) {
				$selection[] = $value;
				continue;
			}

			if ( $value === true ) {
				$selection[] = $key;
				continue;
			}       
		}
		

		return trim(
			$classnames . ' ' . join(
				' ',
				$selection
			)
		); 
		*/
	}
}
