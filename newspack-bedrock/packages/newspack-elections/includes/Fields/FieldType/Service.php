<?php

namespace Govpack\Fields\FieldType;

class Service extends Link {

	/**
	 * Type Slug
	 * 
	 * Machine readable name to reference the field
	 */
	public string $slug = 'service';

	/**
	 * Type Label
	 * 
	 * Human readable label for types
	 */
	public string $label = 'Service';

	public function __construct() {
		
		$this->formats = [
			...$this->formats,
			[
				'value' => 'icon',
				'label' => 'Icon',
			],
		];
	}

	public function variation_icon(): string {
		return 'admin-links';
	}

	public function value( $value ) {
		return $value;
	}
}
