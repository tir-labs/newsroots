<?php

namespace Govpack\Fields;

use Govpack\Collection\CollectableInterface;

class Service implements CollectableInterface {

	protected string $slug;
	protected string $label;
	protected string $icon_slug;
	protected string $color;

	public function __construct( ?string $slug = null, ?string $label = null ) {

		if ( $slug ) {
			$this->slug = $slug;
		}

		if ( $label ) {
			$this->label = $label;
		}
	}

	public function icon_markup(): string {

		$plugin = \Govpack\Govpack::instance();
		$icon   = $plugin->icons()->get( $this->get_icon_slug() );

		if ( $icon === '' ) {
			return "";
		}

		return $icon;
	}

	public function icon() : string {
		return $this->icon_markup();
	}

	public function get_icon_slug(): string {
		return isset( $this->icon_slug ) ? $this->icon_slug : $this->slug;
	}

	public function slug(): string {
		return $this->slug;
	}

	public function color(): string {
		return $this->color;
	}

	public function set_color( string $color ): string {
		$this->color = $color;
		return $this->color;
	}


	public function to_array(): array {
		return [];
	}

	public function to_rest(): array {
		return [];
	}
}
