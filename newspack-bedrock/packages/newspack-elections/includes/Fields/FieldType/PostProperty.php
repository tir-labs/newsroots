<?php

namespace Govpack\Fields\FieldType;

class PostProperty extends \Govpack\Fields\FieldType {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'post_property';

	
	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Post Property';

	/**
	 * Output Formats
	 * 
	 * Formats the field can be output as
	 */
	public array $formats = [];

	/**
	 * Should Create Block Variations
	 * 
	 * Flag that controls if a field type should be used to create block variations
	 */
	public bool $should_create_block_variations = false;

	/**
	 * Type Icon
	 * 
	 * An Icon to use for the Variation
	 */
	public function variation_icon(): string {
		return '';
	}
}
