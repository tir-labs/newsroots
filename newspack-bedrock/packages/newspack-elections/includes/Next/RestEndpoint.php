<?php 
namespace Govpack\Next;

use WP_REST_Request;
use WP_REST_Server;
use WP_Error;

abstract class RestEndpoint {

	public string $namespace;
	public string $route;
	public bool $override = false;

	public string $methods;
	protected mixed $schema;
	
	public function __construct() {

		// set the HTTP Methods to a sane default.
		$this->readable();
	}

	public function readable() {
		$this->methods( WP_REST_Server::READABLE );
		return $this;
	}

	public function creatable() {
		$this->methods( WP_REST_Server::CREATABLE );
		return $this;
	}

	public function editable() {
		$this->methods( WP_REST_Server::EDITABLE );
		return $this;
	}

	public function deleteable() {
		$this->methods( WP_REST_Server::DELETABLE );
		return $this;
	}

	public function methods( string $methods ): self {
		$this->methods = $methods;
		return $this;
	}
	
	public function args(): array {

		$args = [
			'methods'             => $this->methods,
			'callback'            => $this->callbacks( 'callback' ),
			'permission_callback' => $this->callbacks( 'permission' ),
			'validate_callback'   => $this->callbacks( 'validate' ),
			'sanitize_callback'   => $this->callbacks( 'sanitize' ),
		];

		return $args;
	}

	private function in_allowed_callbacks( string $callback ): bool {

		$allowed_callback_names = [
			'callback', 
			'permission',
			'validate',
			'sanitize',
		];

		return in_array( $callback, $allowed_callback_names, true );
	}

	public function callbacks( string $callback ) {

		if ( ! $this->in_allowed_callbacks( $callback ) ) {
			return null;
		}

		if ( ! method_exists( $this, $callback ) ) {
			return null;
		}

		return [ $this, $callback ];
	}

	public function schema(): array|null {
		
		return [
			'description' => 'Profile Fields',
			'readonly'    => true,
			'items'       => [
				'type'       => 'object',
				'properties' => [
					'slug'  => [
						'type' => 'string',
					],
					'label' => [
						'type' => 'string',
					],
				],
			],
			'context'     => [ 'view', 'edit', 'embed' ],
			
		];
	}


	abstract public function callback( \WP_REST_Request $request ): \WP_REST_Response|WP_Error;
	abstract public function sanitize( string $value, \WP_REST_Request $request, string $param ): WP_Error|bool;
	abstract public function validate( \WP_REST_Request $request ): WP_Error|bool;
	abstract public function permission( \WP_REST_Request $request ): WP_Error|bool;
}
