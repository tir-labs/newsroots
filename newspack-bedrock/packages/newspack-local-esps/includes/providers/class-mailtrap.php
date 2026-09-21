<?php
/**
 * Mailtrap provider.
 * API: https://api-docs.mailtrap.io/docs/mailtrap-api-docs/
 *
 * Supports:
 * - Send: POST https://send.api.mailtrap.io/api/send
 * - Batch: POST https://bulk.api.mailtrap.io/api/batches
 * - Contacts: POST https://bulk.api.mailtrap.io/api/contacts
 * - Custom variables: {{variable}} syntax in email body
 * - Lists/segments: via bulk API
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mailtrap extends Base_Provider {

    protected $slug = 'mailtrap';
    protected $name = 'Mailtrap';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_key',
                'label'       => 'API Token',
                'type'        => 'password',
                'description' => 'Your Mailtrap API token from https://mailtrap.io/api-tokens',
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
            return new \WP_Error( 'mailtrap_not_configured', 'Mailtrap is not configured.' );
        }

        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        $body = [
            'from'    => [ 'email' => $from, 'name' => $from_name ],
            'to'      => [ [ 'email' => $args['to'] ] ],
            'subject' => $args['subject'],
            'html'    => $args['html'],
        ];

        // Mailtrap supports custom variables for personalization
        if ( ! empty( $args['contact'] ) ) {
            $body['custom_variables'] = [
                'email' => $args['contact']['email'] ?? '',
                'name'  => $args['contact']['name'] ?? '',
            ];
            if ( ! empty( $args['contact']['metadata'] ) ) {
                $body['custom_variables'] = array_merge(
                    $body['custom_variables'],
                    $args['contact']['metadata']
                );
            }
        }

        $response = $this->request( 'https://send.api.mailtrap.io/api/send', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add contact to Mailtrap contacts.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return true;
        }

        $body = [
            'email'      => $contact['email'],
            'name'       => $contact['name'] ?? '',
            'is_active'  => true,
        ];

        if ( ! empty( $contact['metadata'] ) ) {
            $body['custom_variables'] = $contact['metadata'];
        }

        if ( ! empty( $list_id ) ) {
            $body['list_ids'] = [ (int) $list_id ];
        }

        $response = $this->request( 'https://bulk.api.mailtrap.io/api/contacts', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
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

        $response = $this->request( 'https://bulk.api.mailtrap.io/api/lists', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
            ],
        ] );

        if ( is_wp_error( $response ) || ! is_array( $response ) ) {
            return [];
        }

        return array_map( function( $list ) {
            return [ 'id' => $list['id'] ?? '', 'name' => $list['name'] ?? '' ];
        }, $response );
    }
}

