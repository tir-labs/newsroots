<?php

namespace Govpack\Fields\FieldType;

class Phone extends Link {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'phone';

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Phone';

	public array $formats = [ 
		[
			'value' => 'label',
			'label' => 'Label',
		],
		[
			'value' => 'url',
			'label' => 'Number',
		],
		[
			'value' => 'icon',
			'label' => 'Icon',
		],
	];
	
	public ?string $display_icon = "phone";

	public function variation_icon(): string {
		return 'phone';
	}

	public function value( $value ) {
		return $value;
	}
}
