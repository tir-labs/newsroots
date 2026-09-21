<?php

namespace Govpack\Abstracts;

use Govpack\Collection\Collection;

abstract class Registry extends Collection {


	public function register( mixed $item, string|null $name = null ) {

		if ( $this->exists( $item ) ) {
			$label = is_a( $item, '\Govpack\Collection\Collectable' ) ? $item->slug() : $item;
			throw new \Exception( esc_html( sprintf( 'Trying to add duplicate Item (%s) to a registry.', $label ) ) );
		}
		
		$this->add( $item, $name );
	}

	public function add( mixed $item, string|null $name = null ) {
		if ( ! $name ) {
			$this->collection[] = $item;
		} else {
			$this->collection[ $name ] = $item;
		}
	}


	public function exists( mixed $item ): bool {


		// if the passed item is used as a key on the items collection, check it exists and return true
		if ( ( is_string( $item ) || \is_int( $item ) ) && $this->isset( $item ) ) {
			return true;
		}

		// if the passed item exists with the array, true return
		if ( in_array( $item, $this->all(), true ) ) {
			return true;
		}

		// no matches available so false
		return false;
	}   

	public function isset( mixed $key ): bool {
		$items = $this->all();
		return isset( $items[ $key ] );
	}
}   
