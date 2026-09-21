<?php

namespace Govpack\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class ProfileFieldsRestController extends GovpackRestController {

	
	public function __construct() {
		$this->namespace = 'govpack/v1';
		$this->rest_base = 'fields';
	}
	
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<slug>[\S]+)',
			[
				'args'   => [
					'slug' => [
						'description' => __( 'An alphanumeric identifier for the field.', 'newspack-elections' ),
						'type'        => 'string',
					],
				],
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
					//'args'                => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Checks if a given request has access to get items.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ): bool|WP_Error {
		return true;
	}


	/**
	 * Checks if a given request has access to get a specific item.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ): bool|WP_Error {
		return true;
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ): WP_REST_Response|WP_Error {
		
		$slug = $request->get_param( 'slug' );

		$field = \Govpack\Profile\CPT::fields()->get( $slug );

		if ( $field === false ) {
			return new WP_Error(
				'rest_gp_field_not_found',
				__( 'Field Not Found.', 'newspack-elections' ),
				[ 'status' => 404 ]
			);
		} 

		return rest_ensure_response(
			$field->to_rest()
		);
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		$registered    = $this->get_collection_params();
		$prepared_args = [];

		$parameter_mappings = [
			'slug' => 'slug',
		];

		foreach ( $parameter_mappings as $api_param => $field_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$prepared_args[ $field_param ] = $request[ $api_param ];
			}
		}


		$fields = array_values(
			\array_filter(
				\Govpack\Profile\CPT::fields()->to_rest(),
				function ( $field ) use ( $prepared_args ) {
					// Loop each parameter, 
					// if it doesn't exist within the field then return false to filter it out
					// then check the value, if it doesn't match then return false and filter
					// hopefully this should remove all possible non-inclusions so the 
					// final return true is all thats included
					foreach ( $prepared_args as $param => $value ) {
			
						if ( ! isset( $field[ $param ] ) ) {
							return false;
						}
				
						if ( ! \in_array( $field[ $param ], $value, true ) ) {
							return false;
						}
					}

					return true;
				}
			)
		);


		return rest_ensure_response( $fields );
	}
	
	/**
	 * Retrieves the field's schema, conforming to JSON Schema.
	 *
	 * @since 4.7.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'profile-field',
			'type'       => 'object',
			'properties' => [
				'slug'        => [
					'description' => __( 'Unique identifier for the field.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'label'       => [
					'description' => __( 'Human readable name for the field.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'type'        => [
					'description' => __( 'Field Type Slug.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'group'       => [
					'description' => __( 'Field Group Slug.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'meta_key'    => [
					'description' => __( 'Meta Key used to store field value.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'source'      => [
					'description' => __( 'Source of Field Data.', 'newspack-elections' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'allow_block' => [
					'description' => __( 'Can this field Create a block variation.', 'newspack-elections' ),
					'type'        => 'boolean',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
			],
		];
		

		return $this->add_additional_fields_schema( $this->schema );
	}

	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		$query_params['slug'] = [
			'description' => __( 'Limit result set to fields with one or more specific slugs.', 'newspack-elections' ),
			'type'        => 'array',
			'items'       => [
				'type' => 'string',
			],
		];

		/**
		 * Filters REST API collection parameters for the users controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_User_Query parameter.  Use the
		 * `rest_user_query` filter to set WP_User_Query arguments.
		 *
		 * @since 4.7.0
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'rest_gp_field_collection_params', $query_params );
	}
}   
