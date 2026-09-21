<?php
/**
 * Resend provider.
 * API: https://resend.com/docs/api-reference/emails/send-email
 * Contacts: https://resend.com/docs/api-reference/contacts
 *
 * Supports:
 * - Send: POST https://api.resend.com/emails
 * - Contacts: POST https://api.resend.com/audiences/{audience_id}/contacts
 * - Properties: Custom fields on contacts (firstName, lastName, etc.)
 * - Segments: Dynamic audience filtering via Resend dashboard
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Resend extends Base_Provider {

    protected $slug = 'resend';
    protected $name = 'Resend';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'description' => 'Your Resend API key (re_...). Get it from https://resend.com/api-keys',
            ],
            [
                'name'        => 'audience_id',
                'label'       => 'Audience ID',
                'type'        => 'text',
                'description' => 'Resend Audience ID for subscriber management. Get from https://resend.com/audiences',
            ],
            [
                'name'        => 'from_email',
                'label'       => 'From Email',
                'type'        => 'text',
                'description' => 'Verified sender email address.',
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
            return new \WP_Error( 'resend_not_configured', 'Resend is not configured.' );
        }

        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );
        $html      = $args['html'];

        // Replace local merge tags before sending
        if ( ! empty( $args['contact'] ) ) {
            $html = \Newspack_Local_ESPs\Subscriber_Sync::replace_merge_tags( $html, $args['contact'], 'resend' );
        }

        $body = [
            'from'    => $from_name ? "{$from_name} <{$from}>" : $from,
            'to'      => [ $args['to'] ],
            'subject' => $args['subject'],
            'html'    => $html,
        ];

        // Resend supports tags on emails for filtering
        if ( ! empty( $args['tags'] ) ) {
            $body['tags'] = [];
            foreach ( $args['tags'] as $key => $value ) {
                $body['tags'][] = [ 'name' => $key, 'value' => $value ];
            }
        }

        $response = $this->request( 'https://api.resend.com/emails', [
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
     * Add contact to Resend Audience.
     * Resend supports custom properties on contacts and dynamic segments.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        $audience_id = $list_id ?: $this->get_setting( 'audience_id' );
        if ( ! $this->is_configured() || empty( $audience_id ) ) {
            return true;
        }

        $body = [
            'email'        => $contact['email'],
            'unsubscribed' => false,
        ];

        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $body['firstName'] = $parts[0];
            $body['lastName']  = $parts[1] ?? '';
        }

        // Resend supports custom properties on contacts
        if ( ! empty( $contact['metadata'] ) ) {
            $body['properties'] = $contact['metadata'];
        }

        $response = $this->request( "https://api.resend.com/audiences/{$audience_id}/contacts", [
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
        // Resend calls them "audiences"
        $response = $this->request( 'https://api.resend.com/audiences', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
            ],
        ] );

        if ( is_wp_error( $response ) || ! is_array( $response ) ) {
            return [];
        }

        $audiences = $response['data'] ?? $response;
        return array_map( function( $a ) {
            return [ 'id' => $a['id'] ?? '', 'name' => $a['name'] ?? '' ];
        }, $audiences );
    }
}

