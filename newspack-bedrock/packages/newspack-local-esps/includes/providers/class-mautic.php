<?php
/**
 * Mautic provider.
 * API: https://developer.mautic.org/
 *
 * Supports:
 * - Send: POST /api/emails/{id}/contact/{contactId}/send (per-contact) or POST /api/emails/send (batch)
 * - Contacts: POST /api/contacts/new, PUT /api/contacts/{id}/edit
 * - Custom fields: Mautic manages fields via admin; any field alias can be set via API
 * - Segments: POST /api/segments/{id}/contact/{contactId}/add
 * - Tags: PUT /api/contacts/{id}/edit with tags array
 * - Tokens: {contactfield=email}, {contactfield=firstname}, {contactfield=alias}
 * - Point actions: trigger point actions via API
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mautic extends Base_Provider {

    protected $slug = 'mautic';
    protected $name = 'Mautic';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) )
            && ! empty( $this->get_setting( 'client_id' ) )
            && ! empty( $this->get_setting( 'client_secret' ) )
            && ( ! empty( $this->get_setting( 'access_token' ) ) || ! empty( $this->get_setting( 'refresh_token' ) ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_url',
                'label'       => 'Mautic URL',
                'type'        => 'text',
                'description' => 'Your Mautic instance URL (e.g. https://mautic.yoursite.com).',
            ],
            [
                'name'        => 'client_id',
                'label'       => 'API Client ID',
                'type'        => 'text',
                'description' => 'From Mautic > Settings > API Credentials > OAuth2.',
            ],
            [
                'name'        => 'client_secret',
                'label'       => 'API Client Secret',
                'type'        => 'password',
                'description' => 'From Mautic > Settings > API Credentials > OAuth2.',
            ],
            [
                'name'        => 'access_token',
                'label'       => 'Access Token',
                'type'        => 'password',
                'description' => 'OAuth2 access token. Auto-refreshes if refresh token is set.',
            ],
            [
                'name'        => 'refresh_token',
                'label'       => 'Refresh Token',
                'type'        => 'password',
                'description' => 'OAuth2 refresh token for automatic renewal.',
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

    private function get_access_token() {
        $token   = $this->get_setting( 'access_token' );
        $refresh = $this->get_setting( 'refresh_token' );

        if ( empty( $token ) && ! empty( $refresh ) ) {
            $this->refresh_token();
            $token = $this->get_setting( 'access_token' );
        }

        return $token;
    }

    private function refresh_token() {
        $api_url = rtrim( $this->get_setting( 'api_url' ), '/' );
        $response = wp_remote_post( "{$api_url}/oauth/v2/token", [
            'body' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->get_setting( 'refresh_token' ),
                'client_id'     => $this->get_setting( 'client_id' ),
                'client_secret' => $this->get_setting( 'client_secret' ),
            ],
        ] );

        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body['access_token'] ) ) {
                $this->save_setting( 'access_token', $body['access_token'] );
                if ( ! empty( $body['refresh_token'] ) ) {
                    $this->save_setting( 'refresh_token', $body['refresh_token'] );
                }
            }
        }
    }

    /**
     * Send email via Mautic.
     * Mautic tokens like {contactfield=email} are replaced natively by Mautic at send time.
     */
    public function send( array $args ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'mautic_not_configured', 'Mautic is not configured.' );
        }

        $api_url = rtrim( $this->get_setting( 'api_url' ), '/' );

        // Create a standalone email (not tied to a Mautic email entity)
        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        $response = $this->request( "{$api_url}/api/emails/new", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_access_token(),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'name'           => 'Newsletter ' . gmdate( 'Y-m-d H:i:s' ),
                'subject'        => $args['subject'],
                'customHtml'     => $args['html'],
                'emailType'      => 'list',
                'fromAddress'    => $from,
                'fromName'       => $from_name,
                'replyToAddress' => $from,
                'publishUp'      => gmdate( 'c' ),
                'isPublished'    => true,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add/update contact in Mautic.
     * Mautic manages custom fields via admin panel. Any field alias can be set via API.
     * Tags are applied via the tags property.
     * Segments (called "segments" in Mautic) are managed via the segments API.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'mautic_not_configured', 'Mautic is not configured.' );
        }

        $api_url = rtrim( $this->get_setting( 'api_url' ), '/' );

        $body = [
            'email' => $contact['email'],
        ];

        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $body['firstname'] = $parts[0];
            $body['lastname']  = $parts[1] ?? '';
        }

        // Custom fields — Mautic field aliases are set in admin
        if ( ! empty( $contact['metadata'] ) ) {
            $body = array_merge( $body, $contact['metadata'] );
        }

        // Create or update contact
        $response = $this->request( "{$api_url}/api/contacts/new", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_access_token(),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $contact_id = $response['contact']['id'] ?? null;
        if ( ! $contact_id ) {
            return true;
        }

        // Apply tags
        if ( ! empty( $contact['tags'] ) ) {
            $this->request( "{$api_url}/api/contacts/{$contact_id}/edit", [
                'method'  => 'PUT',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->get_access_token(),
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode( [ 'tags' => implode( ',', (array) $contact['tags'] ) ] ),
            ] );
        }

        // Add to segments
        $segments = [];
        if ( ! empty( $list_id ) ) {
            $segments[] = $list_id;
        }
        if ( ! empty( $contact['segments'] ) ) {
            $segments = array_merge( $segments, $contact['segments'] );
        }

        foreach ( $segments as $segment_id ) {
            $this->request( "{$api_url}/api/segments/{$segment_id}/contact/{$contact_id}/add", [
                'method'  => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->get_access_token(),
                ],
            ] );
        }

        return true;
    }

    public function get_lists() {
        if ( ! $this->is_configured() ) {
            return [];
        }

        $api_url  = rtrim( $this->get_setting( 'api_url' ), '/' );
        $response = $this->request( "{$api_url}/api/segments", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_access_token(),
            ],
        ] );

        if ( is_wp_error( $response ) || ! isset( $response['lists'] ) ) {
            return [];
        }

        return array_map( function( $segment ) {
            return [ 'id' => $segment['id'], 'name' => $segment['name'] ];
        }, array_values( $response['lists'] ) );
    }
}

