<?php

namespace Govpack\Abstracts;

use WP_Block_Supports;
use WP_Block_Type;

abstract class BlockSupport {

	public static function register() {
		$self = new static();
		WP_Block_Supports::get_instance()->register(
			$self->full_name(),
			[
				'register_attribute' => [ $self, 'attributes' ],
				'apply'              => [ $self, 'apply' ],
			]
		);
	}

	abstract public function name(): string;

	

	/**
	 * Registers the example attribute for block types that support it.
	 *
	 * @param WP_Block_Type $block_type Block Type.
	 */
	abstract public function attributes( WP_Block_Type $block_type ): void;

	/**
	 * Add the example attribute to the output.
	 *
	 * @param WP_Block_Type $block_type Block Type.
	 * @param array         $block_attributes Block attributes.
	 *
	 * @return array Block example.
	 */
	abstract public function apply( WP_Block_Type $block_type, array $block_attributes ): array;

	public function full_name(): string {
		return sprintf( '%s/%s', $this->prefix(), $this->name() );
	}

	public function prefix(): string {
		return 'gp';
	}

	

	public function is_supported_by_block_type( WP_Block_Type $block_type ): bool {
		return block_has_support( $block_type, $this->full_name() );
	}
}   
