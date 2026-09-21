<?php

namespace Govpack\Fields\FieldType;

class Email extends Link {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'email';

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Email';

	public ?string $display_icon = "email";

	public array $formats = [ 
		[
			'value' => 'label',
			'label' => 'Label',
		],
		[
			'value' => 'url',
			'label' => 'Email',
		],
		[
			'value' => 'icon',
			'label' => 'Icon',
		],
	];

	public function variation_icon(): string {
		return 'email';
	}

	public function value( $value ) {
		return $value;
	}
}
