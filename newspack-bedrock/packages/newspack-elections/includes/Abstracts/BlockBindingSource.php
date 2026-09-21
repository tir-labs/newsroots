<?php

namespace Govpack\Abstracts;

abstract class BlockBindingSource {

	private string $source_name;
	private string $label;

	private \WP_Block_Bindings_Source|bool $source;

	public function __construct( string $source_name, string $label ) {
		$this->source_name = $source_name;
		$this->label       = $label;
	}

	public function source(): \WP_Block_Bindings_Source|bool {
		return $this->source;
	}

	public function register() {
		$this->source = register_block_bindings_source(
			$this->source_name,
			$this->properties()
		);

		return $this->source;
	}

	public function properties(): array {
		return [
			'label'              => $this->label,
			'get_value_callback' => [ $this, 'callback' ],
			'uses_context'       => $this->contexts(),
		];
	}

	public function contexts(): array {
		return [];
	}

	/**
	 * @param array $source_args Array containing source arguments used to look up the override value, i.e. {"key": "foo"}.
	 * @param WP_Block $block_instance The block instance.
	 * @param string $attribute_name The name of an attribute.
	 */
	abstract public function callback( array $source_args, \WP_Block $block_instance, string $attribute_name ): mixed;
}
