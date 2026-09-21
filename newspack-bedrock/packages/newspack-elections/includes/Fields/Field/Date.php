<?php

namespace Govpack\Fields\Field;

use Govpack\Fields\FieldType;

class Date extends \Govpack\Fields\Field {

	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {
		parent::__construct( $slug, $label, 'date' );
	}
}
