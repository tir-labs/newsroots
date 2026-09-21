<?php

namespace Govpack\Fields\Field;

use Govpack\Profile\Profile;
class Taxonomy extends \Govpack\Fields\Field {


	/**
	 * Field Taxonomy
	 * 
	 * The Group the field belongs to. Controls where the field is output in the admin.
	 */
	public string $taxonomy;

	/**
	 * Properties to include in array
	 * 
	 * Array of property names that will be included when transformed to an array
	 */
	protected array $to_array = [ 'slug', 'label', 'type', 'group', 'taxonomy', 'source' ];

	/**
	 * Construct the profile field
	 */
	public function __construct( string $slug, string $label, string $taxonomy, string $type = 'text' ) {
		parent::__construct( $slug, $label, 'taxonomy' );
		$this->taxonomy = $taxonomy;
		$this->meta_key = null;
		$this->source   = 'taxonomy';
	}
	
	public function raw( Profile $model ) {

		if ( $this->source === 'taxonomy' ) {
			return $model->terms( $this->taxonomy );
		}
		
		return [];
	}
}
