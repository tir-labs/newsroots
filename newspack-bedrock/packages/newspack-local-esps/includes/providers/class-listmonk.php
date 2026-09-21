<?php
/**
 * Listmonk provider.
 * API: https://listmonk.app/docs/apis/
 *
 * Supports:
 * - Send: POST /api/campaigns (create + start)
 * - Subscribers: POST /api/subscribers with attribs
 * - Lists: GET /api/lists
 * - Templates: Go templates: {{ .Subscriber.Email }}, {{ .Subscriber.Attrs.name }}
 * - Subscriber attribs: arbitrary JSON attributes per subscriber
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Listmonk extends Base_Provider {

    protected $slug = 'listmonk';
    protected $name = 'Listmonk';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) ) && ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_url',
                'label'       => 'Listmonk URL',
                'type'        => 'text',
                'description' => 'Your Listmonk instance URL (e.g. https://listmonk.yoursite.com).',
            ],
            [
                'name'        => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'description' => 'Your Listmonk API key (Settings > API tokens).',
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

    /**
     * Listmonk uses Go templates natively:
     * {{ .Subscriber.Email }} - subscriber email
     * {{ .Subscriber.Attrs.name }} - custom attribute
     * {{ .Subscriber.FirstName }} - first name
     * {{ .Subscriber.LastName }} - last name
     */
    public function send( array $args ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'listmonk_not_configured', 'Listmonk is not configured.' );
        }

        $api_url   = rtrim( $this->get_setting( 'api_url' ), '/' );
        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        // Create campaign
        $campaign_body = [
            'name'         => 'Newsletter ' . gmdate( 'Y-m-d H:i:s' ),
            'subject'      => $args['subject'],
            'lists'        => [],
            'from_email'   => "{$from_name} <{$from}>",
            'type'         => 'regular',
            'content_type' => 'richtext',
            'body'         => $args['html'],
            'send_at'      => gmdate( 'c' ),
            'status'       => 'scheduled',
        ];

        // Add to specific lists if provided
        if ( ! empty( $args['list_ids'] ) ) {
            $campaign_body['lists'] = array_map( 'intval', $args['list_ids'] );
        }

        $response = $this->request( "{$api_url}/api/campaigns", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $campaign_body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Start the campaign immediately
        if ( ! empty( $response['data']['id'] ) ) {
            $campaign_id = $response['data']['id'];
            $this->request( "{$api_url}/api/campaigns/{$campaign_id}/status", [
                'method'  => 'PUT',
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode( [ 'status' => 'running' ] ),
            ] );
        }

        return true;
    }

    /**
     * Add subscriber with attribs and lists.
     * Listmonk stores custom data in the 'attribs' JSON object.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'listmonk_not_configured', 'Listmonk is not configured.' );
        }

        $api_url = rtrim( $this->get_setting( 'api_url' ), '/' );

        // Build attribs object
        $attribs = new \stdClass();
        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $attribs->firstName = $parts[0];
            $attribs->lastName  = $parts[1] ?? '';
            $attribs->name      = $contact['name'];
        }

        if ( ! empty( $contact['metadata'] ) ) {
            foreach ( $contact['metadata'] as $key => $value ) {
                $attribs->$key = $value;
            }
        }

        if ( ! empty( $contact['tags'] ) ) {
            $attribs->tags = $contact['tags'];
        }

        $list_ids = [];
        if ( ! empty( $list_id ) ) {
            $list_ids = [ (int) $list_id ];
        } elseif ( ! empty( $contact['segments'] ) ) {
            $list_ids = array_map( 'intval', $contact['segments'] );
        }

        $body = [
            'email'        => $contact['email'],
            'name'         => $contact['name'] ?? '',
            'status'       => 'enabled',
            'lists'        => $list_ids,
            'preconfirmed' => true,
            'attribs'      => $attribs,
        ];

        $response = $this->request( "{$api_url}/api/subscribers", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
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

        $api_url  = rtrim( $this->get_setting( 'api_url' ), '/' );
        $response = $this->request( "{$api_url}/api/lists", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $this->get_setting( 'api_key' ) ),
            ],
        ] );

        if ( is_wp_error( $response ) || ! isset( $response['data']['results'] ) ) {
            return [];
        }

        return array_map( function( $list ) {
            return [ 'id' => $list['id'], 'name' => $list['name'] ];
        }, $response['data']['results'] );
    }
}

