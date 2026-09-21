<?php

namespace Govpack\Collection;

use IteratorAggregate;
interface CollectionInterface {

	public function get( string $item ): CollectableInterface|null;

	public function register( Collectable $item );

	public function add( Collectable $item );

	public function exists( string $item );

	public function all();

	public function to_array(): array;

	public function to_rest(): array;
}
