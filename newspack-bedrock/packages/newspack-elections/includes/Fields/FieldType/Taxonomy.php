<?php

namespace Govpack\Fields\FieldType;

class Taxonomy extends \Govpack\Fields\FieldType {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'taxonomy';


	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Taxonomy';

	/**
	 * Output Formats
	 * 
	 * Formats the field can be output as
	 */
	public array $formats = [ 'term' ];

	/**
	 * Default Block
	 * 
	 * Block used to output the field by default
	 */
	public ?string $default_block = 'npe/profile-field-term';

	/**
	 * Type Icon
	 * 
	 * An Icon to use for the Variation
	 */
	public function variation_icon(): string {
		return 'tag';
	}

	public function get_value_for_model() {
	}
}
