<?php

namespace Govpack\Profile;

use WP_Post;

class Profile {

	public WP_Post $post;
	public int $id;
	public int $ID;

	public array $data;
	public array $values;

	public function __construct( WP_Post $post ) {
		$this->post   = $post;
		$this->values = [];
		$this->ID     = $this->post->ID;
		$this->id     = $this->ID;
	}

	public static function get( $id ) {
		$post = get_post( $id );
		if(!$post){
			return false;
		}
		return new self( $post );
	}

	public function terms( $taxonomy ): array {
		$terms = wp_get_post_terms( $this->ID, $taxonomy );
		return $terms;
	}

	public function data() {

		if ( ! isset( $this->data ) ) {
			$this->data = $this->populate();
		}

		return $this->data;
	}
	
	public function populate(): array {

		$data = [];
	
		foreach ( CPT::fields()->all() as $field ) {
			$data[ $field->slug() ] = $this->raw_val( $field->slug() );
		}

		return $data;
	}

	public function raw_val( string $key ) {
		
		$field                        = CPT::fields()->get( $key );
		$this->data[ $field->slug() ] = $field->raw( $this );

		return $this->data[ $key ];
	}

	public function value( string $key ) {

		if ( ! isset( $this->values[ $key ] ) ) {
			$field                          = CPT::fields()->get( $key );
			$this->values[ $field->slug() ] = $field->value( $this );
		}

		return $this->values[ $key ];
	}

	public function values(): array {

		foreach ( CPT::fields()->all() as $field ) {
			$this->values[ $field->slug() ] = $this->value( $field->slug() );
		}

		return $this->values;
	}

	public function values_for_rest(): array {

		$vals = [];
		foreach ( CPT::fields()->all() as $field ) {
			$vals[ $field->slug() ] = $field->value_for_rest( $this );
		}

		return $vals;
	}

	public function permalink(): string {
		return \get_permalink( $this->post );
	}
}
