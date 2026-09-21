<?php
/**
 * Plunk provider.
 * API: https://docs.useplunk.com/
 *
 * Supports:
 * - Send: POST https://api.useplunk.com/v1/send
 * - Events: POST https://api.useplunk.com/v1/track
 * - Contacts: subscriber data object with custom fields
 * - Segments: Dynamic/static segments via Plunk dashboard
 * - Merge tags: {{email}}, {{firstName}}, etc. natively
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Plunk extends Base_Provider {

    protected $slug = 'plunk';
    protected $name = 'Plunk';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_key',
                'label'       => 'Secret API Key',
                'type'        => 'password',
                'description' => 'Your Plunk secret API key from https://app.useplunk.com/settings',
            ],
            [
                'name'        => 'from_email',
                'label'       => 'From Email',
                'type'        => 'text',
                'description' => 'Sender email address.',
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
            return new \WP_Error( 'plunk_not_configured', 'Plunk is not configured.' );
        }

        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        // Plunk uses {{field}} merge tags natively — no replacement needed.
        $response = $this->request( 'https://api.useplunk.com/v1/send', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'to'      => $args['to'],
                'subject' => $args['subject'],
                'body'    => $args['html'],
                'from'    => $from,
                'name'    => $from_name,
                'subscribed' => true,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add contact to Plunk with data object.
     * Plunk stores custom fields in the 'data' object on each contact.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'plunk_not_configured', 'Plunk is not configured.' );
        }

        $body = [
            'email'      => $contact['email'],
            'subscribed' => true,
            'data'       => [],
        ];

        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $body['data']['firstName'] = $parts[0];
            $body['data']['lastName']  = $parts[1] ?? '';
            $body['data']['name']      = $contact['name'];
        }

        // Custom fields go in the data object
        if ( ! empty( $contact['metadata'] ) ) {
            $body['data'] = array_merge( $body['data'], $contact['metadata'] );
        }

        // Tags stored as array in data
        if ( ! empty( $contact['tags'] ) ) {
            $body['data']['tags'] = $contact['tags'];
        }

        $response = $this->request( 'https://api.useplunk.com/v1/contacts', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        // Track subscription event for segmentation
        if ( ! is_wp_error( $response ) ) {
            $event_body = [
                'event' => 'subscribed',
                'email' => $contact['email'],
            ];
            if ( ! empty( $contact['metadata'] ) ) {
                $event_body['data'] = $contact['metadata'];
            }

            $this->request( 'https://api.useplunk.com/v1/track', [
                'method'  => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode( $event_body ),
            ] );
        }

        return ! is_wp_error( $response );
    }
}

