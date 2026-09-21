<?php
/**
 * Mailgun provider.
 * API: https://documentation.mailgun.com/docs/mailgun/api-reference/openapi-final/tag/Messages/
 *
 * Supports:
 * - Send: POST https://api.mailgun.net/v3/{domain}/messages
 * - Recipient variables: per-recipient personalization via "recipient-variables" JSON
 * - Mailing Lists: POST https://api.mailgun.net/v3/lists/{address}/members
 * - Member vars: custom attributes per list member
 * - Merge tags: %recipient.email%, %recipient.name%, %recipient.custom%
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mailgun extends Base_Provider {

    protected $slug = 'mailgun';
    protected $name = 'Mailgun';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'domain' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'description' => 'Your Mailgun private API key from https://app.mailgun.com/app/account/security/api_keys',
            ],
            [
                'name'        => 'domain',
                'label'       => 'Sending Domain',
                'type'        => 'text',
                'description' => 'Your verified Mailgun domain (e.g. mg.yoursite.com).',
            ],
            [
                'name'        => 'region',
                'label'       => 'Region',
                'type'        => 'select',
                'options'     => [
                    'us' => 'US (api.mailgun.net)',
                    'eu' => 'EU (api.eu.mailgun.net)',
                ],
                'description' => 'Mailgun region.',
            ],
            [
                'name'        => 'default_list',
                'label'       => 'Default Mailing List',
                'type'        => 'text',
                'description' => 'Default mailing list address (e.g. news@mg.yoursite.com). Subscribers are added here.',
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

    private function get_base_url() {
        $region = $this->get_setting( 'region' ) ?: 'us';
        return 'eu' === $region ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';
    }

    public function send( array $args ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'mailgun_not_configured', 'Mailgun is not configured.' );
        }

        $domain    = $this->get_setting( 'domain' );
        $base_url  = $this->get_base_url();
        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        $body = [
            'from'    => $from_name ? "{$from_name} <{$from}>" : $from,
            'to'      => $args['to'],
            'subject' => $args['subject'],
            'html'    => $args['html'],
        ];

        // Mailgun recipient variables for per-recipient personalization
        // https://documentation.mailgun.com/docs/mailgun/user-manual/sending-messages/recipient-variables/
        if ( ! empty( $args['recipient_vars'] ) ) {
            $body['recipient-variables'] = wp_json_encode( $args['recipient_vars'] );
        } elseif ( ! empty( $args['contact'] ) ) {
            // Build recipient variables from contact data for merge tags
            $email = $args['contact']['email'];
            $body['recipient-variables'] = wp_json_encode( [
                $email => array_merge(
                    [ 'email' => $email, 'name' => $args['contact']['name'] ?? '' ],
                    $args['contact']['metadata'] ?? []
                ),
            ] );
        }

        $response = $this->request( "{$base_url}/v3/{$domain}/messages", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
            ],
            'body' => http_build_query( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add contact to Mailgun mailing list with member variables.
     * Member vars enable per-subscriber personalization.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'mailgun_not_configured', 'Mailgun is not configured.' );
        }

        $list_address = $list_id ?: $this->get_setting( 'default_list' );
        if ( empty( $list_address ) ) {
            return true;
        }

        $base_url = $this->get_base_url();

        $body = [
            'address'    => $contact['email'],
            'name'       => $contact['name'] ?? '',
            'subscribed' => 'yes',
            'upsert'     => 'yes',
        ];

        // Member vars for personalization
        if ( ! empty( $contact['metadata'] ) ) {
            $body['vars'] = wp_json_encode( $contact['metadata'] );
        }

        // Tags
        if ( ! empty( $contact['tags'] ) ) {
            $body['tags'] = wp_json_encode( (array) $contact['tags'] );
        }

        $response = $this->request( "{$base_url}/v3/lists/{$list_address}/members", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
            ],
            'body' => http_build_query( $body ),
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

        $base_url = $this->get_base_url();
        $response = $this->request( "{$base_url}/v3/lists/pages", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
            ],
        ] );

        if ( is_wp_error( $response ) || ! isset( $response['items'] ) ) {
            return [];
        }

        return array_map( function( $list ) {
            return [ 'id' => $list['address'] ?? '', 'name' => $list['name'] ?? '' ];
        }, $response['items'] );
    }
}

