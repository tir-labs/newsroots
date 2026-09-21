<?php

namespace Govpack;

class Permalinks {

	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var Govpack\Govpack The single instance of the class
	 */
	protected static $instance = null;

	private string $option_name = 'govpack_permalinks';

	private array $permalinks = [];

	public function __construct() {
	}

	/**
	 * Returns static instance of class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function permalinks(): array {
		if ( empty( $this->permalinks ) ) {
			$this->permalinks = $this->get_permalinks();
		}

		return $this->permalinks;
	}

	private function defaults(): array {
		return [
			'profile_base' => '',
		];
	}

	public function get_permalinks(): array {
		return (array) get_option( $this->option_name, $this->defaults() );
	}

	
	public function update_permalinks(): void {
		update_option( $this->option_name, $this->permalinks );
	}
}
