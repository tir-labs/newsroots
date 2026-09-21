<?php
/**
 * Elastic Email provider.
 * API: https://api.elasticemail.com/v2/
 *
 * Supports:
 * - Send: POST https://api.elasticemail.com/v2/email/send
 * - Contacts: POST https://api.elasticemail.com/v2/contact/add
 * - Custom fields: field[name]=value in contact API
 * - Lists: listNames parameter
 * - Merge tags: [merge_field] syntax natively
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class ElasticEmail extends Base_Provider {

    protected $slug = 'elastic_email';
    protected $name = 'Elastic Email';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'description' => 'Your Elastic Email API key from https://elasticemail.com/account#/settings/new/manage-api',
            ],
            [
                'name'        => 'from_email',
                'label'       => 'From Email',
                'type'        => 'text',
                'description' => 'Verified sender email.',
            ],
            [
                'name'        => 'from_name',
                'label'       => 'From Name',
                'type'        => 'text',
                'description' => 'Sender display name.',
            ],
        ];
    }

    public function send( array $args ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'elastic_not_configured', 'Elastic Email is not configured.' );
        }

        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        // Elastic Email uses [merge_field] syntax natively
        $response = $this->request( 'https://api.elasticemail.com/v2/email/send', [
            'method'  => 'POST',
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body' => http_build_query( [
                'apikey'          => $this->get_setting( 'api_key' ),
                'from'            => $from,
                'fromName'        => $from_name,
                'to'              => $args['to'],
                'subject'         => $args['subject'],
                'bodyHtml'        => $args['html'],
                'isTransactional' => true,
                'merge_email'     => '1',
                'merge_name'      => '1',
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add contact with custom fields and lists.
     * Elastic Email uses field[name]=value for custom attributes.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'elastic_not_configured', 'Elastic Email is not configured.' );
        }

        $body = [
            'apikey'  => $this->get_setting( 'api_key' ),
            'email'   => $contact['email'],
            'name'    => $contact['name'] ?? '',
        ];

        // Custom fields use field[name]=value syntax
        if ( ! empty( $contact['metadata'] ) ) {
            foreach ( $contact['metadata'] as $key => $value ) {
                $body[ 'field[' . $key . ']' ] = $value;
            }
        }

        // Tags
        if ( ! empty( $contact['tags'] ) ) {
            $body['tags'] = implode( ',', (array) $contact['tags'] );
        }

        // Lists
        if ( ! empty( $list_id ) ) {
            $body['listNames'] = $list_id;
        } elseif ( ! empty( $contact['segments'] ) ) {
            $body['listNames'] = implode( ',', $contact['segments'] );
        }

        $response = $this->request( 'https://api.elasticemail.com/v2/contact/add', [
            'method'  => 'POST',
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body'    => http_build_query( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    public function get_lists() {
        if ( ! $this->is_configured() ) {
            return [];
        }

        $response = $this->request( 'https://api.elasticemail.com/v2/list/list?' . http_build_query( [
            'apikey' => $this->get_setting( 'api_key' ),
        ] ) );

        if ( is_wp_error( $response ) || ! is_array( $response ) ) {
            return [];
        }

        return array_map( function( $list ) {
            return [ 'id' => $list['ListName'] ?? '', 'name' => $list['ListName'] ?? '' ];
        }, $response );
    }
}

