<?php

namespace Govpack\Fields\Field;

use Govpack\Fields\FieldType;

class Email extends Link {

	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {

		parent::__construct( $slug, $label, 'email' );

		$this->set_display_icon("email");
	}
}
