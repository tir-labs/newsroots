<?php

namespace Govpack\Fields;

use Govpack\Collection\CollectableInterface;
use Govpack\Collection\Collectable;

abstract class FieldType extends Collectable implements CollectableInterface {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug;

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label;

	/**
	 * Should Create Block Variations
	 * 
	 * Flag that controls if a field type should be used to create block variations
	 */
	public bool $should_create_block_variations = true;

	/**
	 * Default Block
	 * 
	 * Block used to output the field by default
	 */
	public ?string $default_block;

	/**
	 * Default Display Icon
	 * 
	 * Block used to output the field by default
	 */
	public ?string $display_icon = "";


	public function get_variation_inner_blocks(): array {

		if ( ! $this->should_create_block_variations ) {
			return [];
		}
		
		if ( $this->default_block ) {
			return [
				[ $this->default_block ],
			];
		}

		return [
			[ 'core/paragraph', [] ],
		];
	}

	abstract public function variation_icon(): string;


	public function value( $value ) {
		return $value;
	}

	public function format( $value ) {
		return $value;
	}

	public function __toString() {
		return $this->slug;
	}

	public function to_array(): array {
		return [
			'slug'  => $this->slug,
			'label' => $this->label,
			'block' => $this->default_block ?? false,
			'icon' => $this->display_icon
		];
	}

	public function to_rest(): array {
		return $this->to_array();
	}
}
