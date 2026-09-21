<?php


namespace Govpack\Fields;

use Govpack\Abstracts\Registry;
use Govpack\Fields\FieldType;


class ProfileServiceRegistry extends Registry {

	use \Govpack\Instance;

	public function register( mixed $item, string|null $name = null ) {
		
		if ( $name === null ) {
			$name = $item->slug();
		}
		
		if ( $this->isset( $name ) ) {
			throw new \Exception( esc_html( sprintf( 'Duplicate name (%s) Profile Service Registry. Each Name must be unique.', $name ) ) );
		}

		if ( $this->exists( $item ) ) {
			throw new \Exception( esc_html( sprintf( 'Trying to add duplicate Item (%s) to a registry.', $name ) ) );
		}
		
		$this->add( $item, $name );
	}
}
