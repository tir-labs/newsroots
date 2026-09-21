<?php

namespace Govpack\Collection;

use ArrayIterator;
use ArrayObject;
use Traversable;

abstract class Collection implements CollectionInterface {

	public array $collection;

	public function __construct( $items = [] ) {

		$this->collection = [];

		if ( ! empty( $items ) ) {
			$this->collection = $items;
		}
	}

	public function get( string $item ): null|CollectableInterface {
		if ( $this->exists( $item ) ) {
			return $this->collection[ $item ];
		}

		return null;
	}

	public function add( mixed $item, string|null $name = null ) {
		$this->collection[ $item->slug() ] = $item;
	}

	public function exists( Collectable|string $type ): bool {
		$slug = ( is_a( $type, Collectable::class ) ? $type->slug() : $type );
		return isset( $this->collection[ $slug ] );
	}

	public function all(): array {
		return $this->collection;
	}

	public function to_array(): array {
		$arr = [];

		foreach ( $this->collection as $item ) {
			$arr[] = $item->to_array();
		}

		return $arr;
	}

	public function to_rest(): array {
		
		return \array_map(
			function ( $item ) {
				return $item->to_rest();
			}, 
			array_values( $this->all() ) 
		);
	}

	public function create( $items ) {
		$calling_class = get_class( $this );
		$collection    = new $calling_class( $items );
		return $collection;
	}

	public function filter( callable $callback ): self {
		$filtered = array_filter( $this->collection, $callback );
		return $this->create( $filtered );
	}

	public function where( string $prop, mixed $value ): self {
		return $this->filter(
			function ( $item ) use ( $prop, $value ) {
				if ( ! isset( $item->$prop ) ) {
					return false;
				}
				return $item->$prop === $value;
			}
		);
	}

	public function count(): int {
		return count( $this->collection );
	}

	public function length(): int {
		return $this->count();
	}
}
