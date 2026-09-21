<?php

namespace Govpack\Fields;

class FieldManager extends \Govpack\Abstracts\Registry {

	/**
	 * Field Groups
	 * 
	 * Allowed Groups in which a field can appear.
	 */
	const GROUPS = [
		'ABOUT' => 'about',
	];

	/**
	 * Collection of types used by fields;
	 */
	public FieldTypeRegistry $types;

	
	public function get( string $item ): Field|null {
		return parent::get( $item );
	}

	public function set_types( FieldTypeRegistry $types ) {
		$this->types = $types;
	}

	public function get_types(): array {
		return $this->types->all();
	}

	public function register_fields( Field|array $fields_input ) {
		if ( is_array( $fields_input ) ) {
			foreach ( $fields_input as $field ) {
				$this->register( $field, $field->slug() );
			}
		} else { 
			$this->register( $fields_input, $fields_input->slug() );
		}
	}

	public function get_by_source( string $source ): array {
		return $this->where( 'source', $source )->all();
	}

	public function of_type( $type ): array {
		return $this->filter(
			function ( $field ) use ( $type ) {
				return $field->type->slug === $type;
			}
		)->all();
	}

	public function of_format( $format ): array {
		return $this->filter(
			function ( $field ) use ( $format ) {
				return in_array( $format, $field->type->formats, true );
			}
		)->all();
	}
}
