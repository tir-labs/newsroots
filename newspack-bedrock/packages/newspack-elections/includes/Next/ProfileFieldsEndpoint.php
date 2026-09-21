<?php 
namespace Govpack\Next;

use Govpack\Profile\CPT;
use WP_Error;
use WP_REST_Request;


class ProfileFieldsEndpoint extends \Govpack\Next\RestEndpoint {


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

	public function callback( \WP_REST_Request $request ): \WP_REST_Response {  

		return rest_ensure_response(
			array_map(
				function ( $item ) {
					return array_merge(
						$item,
						[
							'id'   => $item['slug'],
							'type' => $item['type']->slug,
						]
					);
				},
				CPT::fields()->to_array() 
			) 
		);
	}

	public function validate( \WP_REST_Request $request ): WP_Error|bool {
		return true;
	}

	public function sanitize( string $value, WP_REST_Request $request, string $param ): WP_Error|bool {
		return true;
	}

	public function permission( WP_REST_Request $request ): WP_Error|bool {
		return true;
	}
}
