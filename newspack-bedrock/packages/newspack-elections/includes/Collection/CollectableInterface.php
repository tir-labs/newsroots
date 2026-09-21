<?php

namespace Govpack\Collection;

interface CollectableInterface {

	public function slug(): string;

	public function to_array(): array;

	public function to_rest(): array;
}
