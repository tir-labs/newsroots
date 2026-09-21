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
class ProfileSocialLink extends \Govpack\Blocks\ProfileFieldText {

	public string $block_name = 'npe/profile-social-link';
	public $field_type        = 'service';
	
	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileSocialLink' );
	}

	public function handle_render( array $attributes, string $content, WP_Block $block ) {
		
		$link    = $this->get_value();
		$field   = $this->get_field();
		$service = $field->service();
		$icon    = $service->icon();

		$open_in_new_tab = isset( $block->context['npe/openInNewTab'] ) ? $block->context['npe/openInNewTab'] : false;

		$rel = trim( isset( $attributes['rel'] ) ? $attributes['rel'] : '' );
	
		$text = ! empty( $attributes['label'] ) ? trim( $attributes['label'] ) : '';
		$text = $text ? $text : $this->get_field()->label;


		$show_labels = array_key_exists( 'showLabels', $block->context ) ? $block->context['npe/showLabels'] : false;
		$icon        = $service->icon();
		

		// Don't render a link if there is no URL set.
		if ( !isset($link['url']) || (! $link['url'])) {
			return '';
		}

		$url = $link['url'];

		/**
		 * Prepend emails with `mailto:` if not set.
		 * The `is_email` returns false for emails with schema.
		 */
		if ( is_email( $url ) ) {
			$url = 'mailto:' . antispambot( $url );
		}

		/**
		 * Prepend URL with https:// if it doesn't appear to contain a scheme
		 * and it's not a relative link or a fragment.
		 */
		if ( ! wp_parse_url( $url, PHP_URL_SCHEME ) && ! str_starts_with( $url, '//' ) && ! str_starts_with( $url, '#' ) ) {
			$url = 'https://' . $url;
		}

		if ( $open_in_new_tab ) {
			$rel = trim( $rel . ' noopener nofollow' );
		}

		
		$styles = [
			'color'            => $service->color() ?? $this->context( 'iconColorValue' ) ?? false,
			'background-color' => $this->context( 'iconBackgroundColorValue' ?? false ),
		];

	

		?>
		<li 
		<?php
		echo get_block_wrapper_attributes(
			[
				'class' => 'wp-block-social-link wp-social-link',
				'style' => self::style_array_to_string( $styles ),
			]
		);
		?>
		>
			<a 
				href="<?php echo \esc_url( $url ); ?>" 
				class="wp-block-social-link-anchor" 
				rel="<?php echo esc_attr( $rel ); ?>"
				<?php echo ( $open_in_new_tab ? 'target="_blank"' : '' ); ?>
			>
				<?php echo \esc_svg( $icon ); ?>
				<span class="wp-block-social-link-label <?php echo ( $show_labels ? '' : ' screen-reader-text' ); ?>">
					<?php echo esc_html( $text ); ?>
				</span>
			</a>
		</li>
		<?php
	}

	public static function style_array_to_string( array|string $styles ): string {
		
		if ( is_string( $styles ) ) {
			return $styles;
		}

		$style = '';

		// remove false or null values
		$styles = array_filter( $styles );

		// return empty string if no styles
		if ( empty( $styles ) ) {
			return '';
		}

		$style = trim(
			implode(
				' ', 
				array_map(
					function ( $rule, $value ) {
						return sprintf( '%s: %s;', $rule, $value );
					}, 
					array_keys( $styles ), 
					array_values( $styles )
				)
			)
		);

		return $style;
	}

	public function variations(): array {
		return [];
	}

	public function create_field_variations(): array {
		return [];
	}
}
