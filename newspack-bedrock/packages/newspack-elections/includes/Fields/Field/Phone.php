<?php

namespace Govpack\Fields\Field;

use Govpack\Fields\FieldType;

class Phone extends Link {

	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {
		parent::__construct( $slug, $label, 'phone' );
		$this->set_display_icon("phone");
	}
}
