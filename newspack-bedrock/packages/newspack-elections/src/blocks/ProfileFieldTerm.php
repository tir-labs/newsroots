<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Blocks;

use WP_Block;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Register and handle the block.
 */
class ProfileFieldTerm extends \Govpack\Blocks\ProfileField {

	public string $block_name = 'npe/profile-field-term';
	private string $default_variation;
	public $field_type = 'taxonomy';

	public function __construct( $plugin ) {
		$this->plugin            = $plugin;
		$this->default_variation = 'govpack_officeholder_status'; // TODO: reference the const from the taxonomy file.
	}

	

	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/ProfileFieldTerm' );
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
				'isActive'    => [ 'meta_key' ],
				'scope'       => [ 'inserter' ],
				'icon'        => $field->type->variation_icon(),
			];

			$variations[] = $variation;
		}

		return $variations;
	}

	public function get_block_support( $feature = null, $default_value = false ) {
		return _wp_array_get( $this->block_type->supports, $feature, $default_value );
	}

	public function is_allow_field_type_for_block() {
		$supported_types = $this->get_block_support( [ 'gp/field-aware', 'type' ], [] );
		
		if ( ! is_array( $supported_types ) ) {
			$supported_types = [ $supported_types ];
		}
		
		return in_array( $this->get_field()->type->slug, $supported_types, true );
	}
	
	public function create_taxonomy_field_variations(): array {


		$taxonomies = get_object_taxonomies(
			'govpack_profiles', // TODO: reference the const from the post_type file.
			[
				'publicly_queryable' => true,
				'show_in_rest'       => true,
			],
			'objects'
		);
	
		$variations = [];

		// Create and register the eligible taxonomies variations.
		foreach ( $taxonomies as $taxonomy ) {


			if ( $taxonomy->_builtin ) {
				continue;
			}
			$variation = [
				'name'        => $taxonomy->name,
				'title'       => $taxonomy->label,
				'description' => sprintf(
					/* translators: %s: taxonomy's label */
					__( 'Display a list of assigned terms from the taxonomy: %s', 'newspack-elections' ),
					$taxonomy->label
				),
				'attributes'  => [
					'taxonomy' => $taxonomy->name,
				],
				'isActive'    => [ 'taxonomy' ],
				'scope'       => [ 'inserter', 'transform' ],
			];
			// Set the category variation as the default one.
			if ( $this->default_variation === $taxonomy->name ) {
				$variation['isDefault'] = true;
			}
			
			$variations[] = $variation;
			
		}

		return $variations;
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
		<div <?php echo get_block_wrapper_attributes(
				$this->get_new_block_wrapper_attributes()
			); ?>>
			<?php echo wp_kses_post( $this->output() ); ?>
		</div>
		<?php
	}

	public function get_field_by_taxonomy( string $tax ) {
		return \Govpack\Profile\CPT::fields()->where( 'taxonomy', $tax )->all();
	}

	/**
	 * Override the `get_field_key` method from ProfileField. At one point the term block used a taxonomy 
	 * attribute instead of the fieldKey to access terms from a taxonomy.
	 * 
	 * This method now attempts to get the fieldKey as normal for current versions of the block. It then falls 
	 * back to getting a fieldKey from the from the fieldManager by finding a field using the taxonomy
	 */
	public function get_field_key() {

		$field_key = parent::get_field_key();
		
		if ( $field_key !== false ) {
			return $field_key;
		}

		/** 
		 * ToDo:
		 * - Check taxonomy has a value or throw an exception
		 * - Throw an exception if more than 1 Field returned
		 * - Throw an exception if no fields are found
		 */
		
		$taxonomy = $this->attribute( 'taxonomy' );
		if ( ! $taxonomy ) {
			return false;
		}


		$found_fields = $this->get_field_by_taxonomy( $taxonomy );
		$keys         = array_keys( $found_fields );
		$field_key    = $keys[0];
		
		return $field_key;
	}

	public function output(): string {
		
		$terms = $this->get_value();

		$output        = [];
		$separator     = $this->attribute( 'separator' );
		$term_limit    = $this->attribute( 'termLimit' );
		$display_links = $this->attribute( 'displayLinks' );

		if ( empty( $terms ) ) {
			return '';
		}

		$terms = array_slice( $terms, 0, $term_limit );

		foreach ( $terms as $term ) {
			
			$output[] = $display_links ? $this->term_link( $term ) : $this->term_span( $term );
		}

		$output = implode( $separator, $output );

		return $output;
	}

	public function get_term_classes(WP_Term $term, string $context = "") : string {

		$root_class = "npe-term";
		$classes = [];
		$classes[] = $root_class;
		$classes[] = sprintf("%s--%s", $root_class, $term->taxonomy );
		$classes[] = sprintf("%s--%s", $root_class, $term->term_id );
		$classes[] = sprintf("%s--%s", $root_class, $term->slug );

		if($term->parent !== 0){
			$classes[] = sprintf("%s--is-child-term", $root_class );
			$classes[] = sprintf("%s--parent-%s", $root_class, $term->parent );
		}
		
		if( $context === "link") {
			$classes[] = sprintf("%s--is-link", $root_class );
		}

		$classes = apply_filters( "newspack_elections_block_term_classes", $classes, $term, $this );
		$classes = implode(" ", $classes);
		return $classes;
	}

	public function term_span( WP_Term $term ): string {
		return sprintf('<span %s>%s</span>', self::array_to_html_attributes([
			"class" => $this->get_term_classes($term)
		]), $term->name);
	}

	public function term_link( WP_Term $term ): string {
		return sprintf( '<a %s>%s</a>', self::array_to_html_attributes([
			"class" => $this->get_term_classes($term, "link"),
			"href" => get_term_link( $term ),
		]) , $term->name );
	}
}
