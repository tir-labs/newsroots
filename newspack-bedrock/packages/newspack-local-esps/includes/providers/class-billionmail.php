<?php
/**
 * BillionMail provider (self-hosted).
 *
 * Supports:
 * - Send: POST /api/v1/send
 * - Subscribers: POST /api/v1/subscribers with metadata, tags, lists
 * - Lists: GET /api/v1/lists
 * - Merge tags: {{field}} syntax
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class BillionMail extends Base_Provider {

    protected $slug = 'billionmail';
    protected $name = 'BillionMail';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) ) && ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_url',
                'label'       => 'BillionMail URL',
                'type'        => 'text',
                'description' => 'Your BillionMail instance URL (e.g. https://mail.yoursite.com).',
            ],
            [
                'name'        => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'description' => 'Your BillionMail API key.',
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
            return new \WP_Error( 'billionmail_not_configured', 'BillionMail is not configured.' );
        }

        $api_url   = rtrim( $this->get_setting( 'api_url' ), '/' );
        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        // Replace merge tags for BillionMail's {{field}} syntax
        $html = $args['html'];
        if ( ! empty( $args['contact'] ) ) {
            $html = \Newspack_Local_ESPs\Subscriber_Sync::replace_merge_tags( $html, $args['contact'], 'billionmail' );
        }

        $response = $this->request( "{$api_url}/api/v1/send", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'from'    => [ 'email' => $from, 'name' => $from_name ],
                'to'      => [ $args['to'] ],
                'subject' => $args['subject'],
                'html'    => $html,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }

    /**
     * Add subscriber with metadata, tags, and lists.
     */
    public function add_contact( array $contact, $list_id = '' ) {
        if ( ! $this->is_configured() ) {
            return new \WP_Error( 'billionmail_not_configured', 'BillionMail is not configured.' );
        }

        $api_url = rtrim( $this->get_setting( 'api_url' ), '/' );

        $body = [
            'email'    => $contact['email'],
            'name'     => $contact['name'] ?? '',
            'status'   => 'confirmed',
            'metadata' => $contact['metadata'] ?? [],
        ];

        // Tags
        if ( ! empty( $contact['tags'] ) ) {
            $body['tags'] = (array) $contact['tags'];
        }

        // Lists
        if ( ! empty( $list_id ) ) {
            $body['lists'] = [ (int) $list_id ];
        } elseif ( ! empty( $contact['segments'] ) ) {
            $body['lists'] = array_map( 'intval', $contact['segments'] );
        }

        $response = $this->request( "{$api_url}/api/v1/subscribers", [
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

        $api_url  = rtrim( $this->get_setting( 'api_url' ), '/' );
        $response = $this->request( "{$api_url}/api/v1/lists", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_setting( 'api_key' ),
            ],
        ] );

        if ( is_wp_error( $response ) || ! is_array( $response ) ) {
            return [];
        }

        return array_map( function( $list ) {
            return [ 'id' => $list['id'], 'name' => $list['name'] ];
        }, $response );
    }
}

