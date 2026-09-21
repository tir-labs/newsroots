<?php 
namespace Govpack;

use PO;
use WP_REST_Server;

use Govpack\Next\RestEndpoint;

class RestRoute {

	public string $namespace;
	public string $route;
	public bool $override    = false;
	public bool $allow_batch = false;
	public array $endpoints;

	protected mixed $schema;
	
	public function __construct( string $route, string|null $rest_namespace = null, bool $override = false ) {

		if ( is_null( $rest_namespace ) ) {
			$this->namespace = 'govpack/v1';
		} else {
			$this->namespace = $rest_namespace;
		}

		$this->route       = $route;
		$this->override    = $override;
		$this->endpoints   = [];
		$this->allow_batch = false;
	}

	public function namespace( string $rest_namespace ): self {
		$this->namespace = $rest_namespace;
		return $this;
	}

	public function override( bool $override ): self {
		$this->override = $override;
		return $this;
	}

	public function route( string $route ): self {
		$this->route = $route;
		return $this;
	}

	public function endpoint( RestEndpoint $endpoint ): self {
		$this->endpoints[] = $endpoint;
		return $this;
	}
	
	public function register(): void {

		register_rest_route(
			$this->namespace,
			$this->route_with_slash(),
			$this->args(),
			$this->override
		);
	}

	private function route_with_slash() {
		if ( strpos( $this->route, '/' ) === 0 ) {
			return $this->route;
		}
		
		return '/' . $this->route;
	}

	public function args(): array {

		$args = [
			'allow_batch' => $this->allow_batch,
		];

		foreach ( $this->endpoints as $endpoint ) {
			$args[] = $endpoint->args();
		}

		if ( $this->has_schema() ) {
			$args['schema'] = $this->schema();
		}

		return $args;
	}

	private function has_schema(): bool {
		return true;
	}

	private function in_allowed_callbacks( string $callback ): bool {

		$allowed_callback_names = [
			'get_callback', 
			'update_callback',
		];

		return in_array( $callback, $allowed_callback_names, true );
	}

	public function callback( string $callback ) {

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
}
