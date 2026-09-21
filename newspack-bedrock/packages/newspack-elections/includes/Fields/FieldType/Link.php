<?php

namespace Govpack\Fields\FieldType;

class Link extends \Govpack\Fields\FieldType {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'link';

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Link';

	/**
	 * Output Formats
	 * 
	 * Formats the field can be output as
	 */
	public array $formats = [ 
		[
			'value' => 'label',
			'label' => 'Label',
		],
		[
			'value' => 'url',
			'label' => 'URL',
		],
		[
			'value' => 'icon',
			'label' => 'Icon',
		],
	];

	/**
	 * Default Block
	 * 
	 * Block used to output the field by default
	 */
	public ?string $default_block = 'npe/profile-field-link';


	public function variation_icon(): string {
		return 'admin-links';
	}

	public function value( $value ) {
		return $value;
	}

	public function to_array(): array {
		return [
			...parent::to_array(),
			'formats' => $this->formats,
		];
	}
}
