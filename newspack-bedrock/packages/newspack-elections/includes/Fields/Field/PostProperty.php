<?php

namespace Govpack\Fields\Field;

use Govpack\Fields\FieldType;
use Govpack\Profile\Profile;

class PostProperty extends \Govpack\Fields\Field {

	/**
	 * Field Source
	 * 
	 * Where the profile sources the data. 1 of meta, term, post
	 */
	public string $source = 'post';


	/**
	 * Source Key 
	 * 
	 * Key to use as locator for the source
	 */
	public null|string $source_key = null;


	/**
	 * Properties to include in array
	 * 
	 * Array of property names that will be included when transformed to an array
	 */
	protected array $to_array = [ 'slug', 'label', 'type', 'group', 'source_key', 'source' ];

	public function key( string $key ): self {
		$this->source_key = $key;
		return $this;
	}

	public function __construct( string $slug, string $label, FieldType|string|null $type = null ) {
	
		parent::__construct( $slug, $label, 'post_property' );
	}


	public function raw( Profile $model ) {

		if ( $this->source !== 'post' ) {
			return 'incompatible source';
		}

		if ( \method_exists( $this, $this->source_key ) ) {
			return call_user_func( [ $this, $this->source_key ], $model );
		}

		return trim( $model->post->{$this->source_key} );
	}

	public function post_excerpt( $model ) {

		$excerpt = trim( $model->post->{$this->source_key} );
		$excerpt = apply_filters( 'get_the_excerpt', $excerpt, $model->post );
		$excerpt = apply_filters( 'the_excerpt', $excerpt );

		return $excerpt;
	}

	public function post_title( $model ) {
		$value = trim( $model->post->{$this->source_key} );
		$value = get_the_title( $model->post );
		return $value;
	}



	public function value_for_rest( Profile $model ) {

		$value = $this->raw( $model );

		if ( $this->source_key === 'post_excerpt' ) {

			$data = [
				'raw'       => $value,
				'rendered'  => post_password_required( $model->post ) ? '' : $value,
				'protected' => (bool) $model->post->post_password,
			];

			return $data;
		}

		if ( $this->source_key === 'post_title' ) {

			$data = [
				'raw'       => $value,
				'protected' => (bool) $model->post->post_password,
			];

			add_filter( 'protected_title_format', [ $this, 'protected_title_format' ] );
			add_filter( 'private_title_format', [ $this, 'protected_title_format' ] );

			$data['rendered'] = get_the_title( $model->post->ID );

			remove_filter( 'protected_title_format', [ $this, 'protected_title_format' ] );
			remove_filter( 'private_title_format', [ $this, 'protected_title_format' ] );

			return $data;
		}
		
		return $value;
	}
}
