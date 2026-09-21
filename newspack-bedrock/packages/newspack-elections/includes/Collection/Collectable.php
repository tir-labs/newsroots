<?php

namespace Govpack\Collection;

abstract class Collectable implements CollectableInterface {

	protected string $slug;

	public function slug(): string {
		return $this->slug;
	}

	abstract public function to_array(): array;
	
	abstract public function to_rest(): array; 
}
