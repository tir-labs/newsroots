<?php

namespace Govpack\Fields\FieldType;

class Text extends \Govpack\Fields\FieldType {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'text';

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Text';

	/**
	 * Output Formats
	 * 
	 * Formats the field can be output as
	 */
	public array $formats = [ 'text' ];

	/**
	 * Default Block
	 * 
	 * Block used to output the field by default
	 */
	public ?string $default_block = 'npe/profile-field-text';

	/**
	 * Type Icon
	 * 
	 * An Icon to use for the Variation
	 */
	public function variation_icon(): string {
		return 'text';
	}
}
