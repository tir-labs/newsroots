<?php

namespace Govpack\Fields;

use Govpack\Fields\FieldType;
use Govpack\Fields\FieldTypeRegistry;
use Govpack\Profile\Profile;

class Field extends \Govpack\Collection\Collectable implements \Govpack\Collection\CollectableInterface {

	/**
	 * Field Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug;

	/**
	 * Field Label
	 * 
	 * Human readable label for fields
	 */
	public string $label;

	/**
	 * Field Type
	 * 
	 * Type of field. Used for determining the input view and what blocks can output this field.
	 */
	public FieldType $type;

	/**
	 * Field Group
	 * 
	 * The Group the field belongs to. Controls where the field is output in the admin.
	 */
	public null|string $group = null;

	/**
	 * Field Source
	 * 
	 * Where the profile sources the data. 1 of meta, term, post
	 */
	public string $source = 'meta';

	/**
	 * Meta Key 
	 * 
	 * Metakey to use as a source
	 */
	public null|string $meta_key = null;

	/**
	 * Default Value  
	 * 
	 * Metakey to use as a source
	 */
	public null|string $default = '';

	/**
	 * Default Display Icon
	 * 
	 * Block used to output the field by default
	 */
	public ?string $display_icon = null;


	/**
	 * Properties to include in array
	 * 
	 * Array of property names that will be included when transformed to an array
	 */
	protected array $to_array = [ 'slug', 'label', 'type', 'group', 'meta_key', 'source', 'allow_block', 'display_icon' ];

	/**
	 * Allow Block Variation
	 * 
	 * Boolean that controls of a field can be represented as a block. Eg Twitter fields exist for 
	 * backwards compatability but shouldn't be used anymore
	 */
	protected bool $allow_block = true;


	/**
	 * Own Block
	 * 
	 * Allows a field to specify which block to use for output.
	 * eg a piece of profile data that has a dedicated block
	 */
	protected string $block;

	/**
	 * Construct the profile field
	 */
	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {
		$this->slug     = $slug;
		$this->label    = $label;
		$this->meta_key = $this->slug;

		$this->set_type( $type );
	}

	public function slug(): string {
		return $this->slug;
	}

	public function set_type( FieldType|string|null $type ) {

		if ( is_a( $type, '\Govpack\Field\FieldType' ) ) {
			$this->type = $type;
		} elseif ( is_string( $type ) ) {

			$this->type = FieldTypeRegistry::instance()->get( $type );
		}

		if ( ! isset( $this->type ) ) {
			$this->type = FieldTypeRegistry::instance()->get( 'text' );
		}
	}

	public function set_display_icon( string $icon_slug ) : self {
		$this->display_icon = $icon_slug;
		return $this;
	}

	public function group( string $group ): self {
		$this->group = $group;
		return $this;
	}

	public function meta( string $meta_key ): self {
		$this->meta_key = $meta_key;
		return $this;
	}

	public function disable_block( $disable = true ): self {
		$this->allow_block = ! $disable;
		return $this;
	}

	public function block( string $block ): self {
		$this->block = $block;
		return $this;
	}
				
	public function variation_icon(): string|null {
		return $this->type->variation_icon();
	}

	public function display_icon(): string|null {

		return $this->get_icon_slug();
	}

	public function icon_markup(): string|null {

		$plugin = \Govpack\Govpack::instance();
		$icon   = $plugin->icons()->get( $this->get_icon_slug() );

		if ( $icon === '' ) {
			return null;
		}

		return $icon;
	}

	public function get_icon_slug(): string {
		if($this->display_icon){
			return $this->display_icon;
		}

		return $this->type->display_icon;
	}

	public function to_array(): array {

		$val = [];

		foreach ( $this->to_array as $key ) {
			if ( ! property_exists( $this, $key ) ) {
				continue;
			}

			if ( empty( $this->$key ) ) {
				continue;
			}

			$val[ $key ] = $this->$key;
		}

		return $val;
	}

	public function to_rest(): array {
		$data         = $this->to_array();
		$data['type'] = $this->type->slug;

		return $data;
	}

	public function format( $raw_value ) {
		return $this->type->format( $raw_value );
	}

	public function raw( Profile $model ) {

		if ( $this->source === 'meta' ) {
			return trim( $model->post->__get( $this->meta_key ) );
		}

	
		return 'unknown source';
	}

	public function value( Profile $model ) {
		return $this->format( $this->raw( $model ) );
	}

	public function value_for_rest( Profile $model ) {
		return $this->raw( $model );
	}

	public function is_block_enabled(): bool {
		return $this->allow_block;
	}

	public function has_own_block(): bool {
		return isset( $this->block );
	}

	public function get_variation_inner_blocks(): array {
		if ( $this->has_own_block() ) {
			return [
				[ $this->block ],
			];
		}

		return $this->type->get_variation_inner_blocks();
	}


}
