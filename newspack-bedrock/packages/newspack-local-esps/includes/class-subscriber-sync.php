<?php
/**
 * Subscriber sync and merge tag helper.
 * Handles local merge tags, attribute syncing, and segment management
 * for all local ESP providers.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs;

defined( 'ABSPATH' ) || exit;

class Subscriber_Sync {

    /**
     * Hook into Newpack's merge tag system.
     */
    public static function init() {
        add_filter( 'newspack_newsletters_tracking_email_address_tag', [ __CLASS__, 'get_email_merge_tag' ] );
        add_filter( 'newspack_newsletters_tracking_merge_tag', [ __CLASS__, 'get_merge_tag' ], 10, 2 );
        add_action( 'newspack_newsletters_newsletter_rendered', [ __CLASS__, 'inject_local_merge_tags' ], 10, 2 );
    }

    /**
     * Provide the email merge tag for the active local provider.
     *
     * Newpack uses this to embed the recipient's email in the tracking pixel URL.
     * ESP merge tags (e.g. *|EMAIL|*) are replaced at send time by the ESP.
     * For local providers that don't support merge tags, we inject the email
     * directly at send time instead.
     */
    public static function get_email_merge_tag( $tag ) {
        $provider = Plugin::get_active_provider();
        if ( ! $provider ) {
            return $tag;
        }

        $tags = self::get_provider_email_tags();
        $slug = $provider->slug ?? '';

        return $tags[ $slug ] ?? $tag;
    }

    /**
     * Provide arbitrary merge tags for the active local provider.
     */
    public static function get_merge_tag( $tag, $field ) {
        $provider = Plugin::get_active_provider();
        if ( ! $provider ) {
            return $tag;
        }

        $tags = self::get_provider_field_tags();
        $slug = $provider->slug ?? '';

        if ( isset( $tags[ $slug ] ) ) {
            $formatter = $tags[ $slug ];
            return $formatter( $field );
        }

        return $tag;
    }

    /**
     * Email merge tags per provider.
     * These are the tags that get replaced with the recipient's email at send time.
     */
    private static function get_provider_email_tags() {
        return [
            // Amazon SES: no native merge tags; we inject at send time via headers.
            'ses'            => '{{email}}',
            // Generic SMTP: no merge tags; inject at send time.
            'smtp'           => '{{email}}',
            // Resend: no merge tags in email body; inject at send time.
            // We use a custom placeholder that we replace before sending.
            'resend'         => '{{email}}',
            // Plunk: uses {{field}} syntax natively.
            'plunk'          => '{{email}}',
            // Elastic Email: uses [merge_field] syntax.
            'elastic_email'  => '[email]',
            // Mailgun: uses recipient-variables with %recipient.email% syntax.
            'mailgun'        => '%recipient.email%',
            // Mailtrap: no native merge tags; inject at send time.
            'mailtrap'       => '{{email}}',
            // Mail250: SMTP-based; inject at send time.
            'mail250'        => '{{email}}',
            // BillionMail: check API docs for merge syntax.
            'billionmail'    => '{{email}}',
            // Mautic: uses {contactfield=email} syntax.
            'mautic'         => '{contactfield=email}',
            // Listmonk: uses Go template {{ .Subscriber.Email }}.
            'listmonk'       => '{{ .Subscriber.Email }}',
        ];
    }

    /**
     * Field merge tag formatters per provider.
     */
    private static function get_provider_field_tags() {
        return [
            'ses'            => fn( $f ) => '{{' . $f . '}}',
            'smtp'           => fn( $f ) => '{{' . $f . '}}',
            'resend'         => fn( $f ) => '{{' . $f . '}}',
            'plunk'          => fn( $f ) => '{{' . $f . '}}',
            'elastic_email'  => fn( $f ) => '[' . $f . ']',
            'mailgun'        => fn( $f ) => '%recipient.' . $f . '%',
            'mailtrap'       => fn( $f ) => '{{' . $f . '}}',
            'mail250'        => fn( $f ) => '{{' . $f . '}}',
            'billionmail'    => fn( $f ) => '{{' . $f . '}}',
            'mautic'         => fn( $f ) => '{contactfield=' . $f . '}',
            'listmonk'       => fn( $f ) => '{{ .Subscriber.Attrs.' . $f . ' }}',
        ];
    }

    /**
     * Before sending, replace merge tags in HTML for providers that don't
     * natively support them. This is called by each provider's send() method.
     *
     * @param string $html    The newsletter HTML.
     * @param array  $contact The contact data (email, name, metadata).
     * @param string $slug    The provider slug.
     * @return string The HTML with merge tags replaced.
     */
    public static function replace_merge_tags( $html, $contact, $slug ) {
        $placeholders = self::get_provider_email_tags();
        $field_tags   = self::get_provider_field_tags();

        // For providers that don't natively replace merge tags,
        // we do it ourselves before sending.
        $needs_local_replacement = [ 'ses', 'smtp', 'resend', 'mailtrap', 'mail250', 'billionmail' ];

        if ( ! in_array( $slug, $needs_local_replacement, true ) ) {
            return $html; // Provider handles its own merge tags.
        }

        // Replace email
        $email_placeholder = $placeholders[ $slug ] ?? '{{email}}';
        $html = str_replace( $email_placeholder, $contact['email'] ?? '', $html );

        // Replace name
        $name_placeholder = '{{name}}';
        if ( 'ses' === $slug || 'smtp' === $slug || 'mailtrap' === $slug || 'mail250' === $slug || 'billionmail' === $slug ) {
            $name_placeholder = '{{name}}';
        } elseif ( 'resend' === $slug ) {
            $name_placeholder = '{{name}}';
        }
        $html = str_replace( $name_placeholder, $contact['name'] ?? '', $html );

        // Replace custom fields from metadata
        if ( ! empty( $contact['metadata'] ) ) {
            foreach ( $contact['metadata'] as $key => $value ) {
                $html = str_replace( '{{' . $key . '}}', $value, $html );
            }
        }

        return $html;
    }

    /**
     * Sync a contact's attributes/tags/segments to the active provider.
     *
     * @param array  $contact {
     *     @type string   $email    Email address.
     *     @type string   $name     Full name.
     *     @type string[] $tags     Tags to apply.
     *     @type array    $metadata Custom fields.
     *     @type string[] $segments Segment/list IDs to add to.
     * }
     */
    public static function sync_contact( $contact ) {
        $provider = Plugin::get_active_provider();
        if ( ! $provider || ! $provider->is_configured() ) {
            return false;
        }

        $slug = $provider->slug ?? '';

        // Build the request body per provider
        switch ( $slug ) {
            case 'resend':
                return self::sync_contact_resend( $provider, $contact );
            case 'plunk':
                return self::sync_contact_plunk( $provider, $contact );
            case 'elastic_email':
                return self::sync_contact_elastic_email( $provider, $contact );
            case 'mailgun':
                return self::sync_contact_mailgun( $provider, $contact );
            case 'billionmail':
                return self::sync_contact_billionmail( $provider, $contact );
            case 'mautic':
                return self::sync_contact_mautic( $provider, $contact );
            case 'listmonk':
                return self::sync_contact_listmonk( $provider, $contact );
            default:
                // SMTP-based providers don't have contact management.
                return true;
        }
    }

    /**
     * Resend: Contacts API with properties + segments.
     */
    private static function sync_contact_resend( $provider, $contact ) {
        $body = [
            'email'        => $contact['email'],
            'unsubscribed' => false,
        ];

        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $body['firstName'] = $parts[0];
            $body['lastName']  = $parts[1] ?? '';
        }

        if ( ! empty( $contact['metadata'] ) ) {
            $body['properties'] = $contact['metadata'];
        }

        $response = $provider->request( 'https://api.resend.com/audiences/contacts', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        return ! is_wp_error( $response );
    }

    /**
     * Plunk: Contacts API with data object + segments.
     */
    private static function sync_contact_plunk( $provider, $contact ) {
        $body = [
            'email'      => $contact['email'],
            'subscribed' => true,
            'data'       => [],
        ];

        if ( ! empty( $contact['name'] ) ) {
            $body['data']['name'] = $contact['name'];
        }

        if ( ! empty( $contact['metadata'] ) ) {
            $body['data'] = array_merge( $body['data'], $contact['metadata'] );
        }

        if ( ! empty( $contact['tags'] ) ) {
            $body['data']['tags'] = $contact['tags'];
        }

        $response = $provider->request( 'https://api.useplunk.com/v1/contacts', [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        // Add to segments
        if ( ! is_wp_error( $response ) && ! empty( $contact['segments'] ) ) {
            $contact_id = $response['id'] ?? null;
            if ( $contact_id ) {
                foreach ( $contact['segments'] as $segment_id ) {
                    $provider->request( 'https://api.useplunk.com/v1/segments/' . $segment_id . '/contacts', [
                        'method'  => 'POST',
                        'headers' => [
                            'Authorization' => 'Bearer ' . $provider->get_setting( 'api_key' ),
                            'Content-Type'  => 'application/json',
                        ],
                        'body' => wp_json_encode( [ 'contacts' => [ $contact_id ] ] ),
                    ] );
                }
            }
        }

        return ! is_wp_error( $response );
    }

    /**
     * Elastic Email: Contacts API with custom fields + lists.
     */
    private static function sync_contact_elastic_email( $provider, $contact ) {
        $body = [
            'apikey'           => $provider->get_setting( 'api_key' ),
            'email'            => $contact['email'],
            'name'             => $contact['name'] ?? '',
            'isTransactional'  => true,
        ];

        if ( ! empty( $contact['metadata'] ) ) {
            foreach ( $contact['metadata'] as $key => $value ) {
                $body['field[' . $key . ']'] = $value;
            }
        }

        if ( ! empty( $contact['lists'] ) ) {
            $body['listNames'] = implode( ',', $contact['lists'] );
        }

        $response = $provider->request( 'https://api.elasticemail.com/v2/contact/add', [
            'method'  => 'POST',
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body'    => http_build_query( $body ),
        ] );

        return ! is_wp_error( $response );
    }

    /**
     * Mailgun: Mailing Lists API with member variables.
     */
    private static function sync_contact_mailgun( $provider, $contact ) {
        $domain   = $provider->get_setting( 'domain' );
        $region   = $provider->get_setting( 'region' ) ?: 'us';
        $base_url = 'eu' === $region ? 'https://api.eu.mailgun.net' : 'https://api.mailgun.net';

        $body = [
            'address'    => $contact['email'],
            'name'       => $contact['name'] ?? '',
            'subscribed' => 'yes',
            'upsert'     => 'yes',
        ];

        if ( ! empty( $contact['metadata'] ) ) {
            $body['vars'] = wp_json_encode( $contact['metadata'] );
        }

        // Add to a mailing list if specified
        $list_address = ! empty( $contact['segments'] ) ? $contact['segments'][0] : $provider->get_setting( 'default_list' );

        if ( $list_address ) {
            $response = $provider->request( "{$base_url}/v3/lists/{$list_address}/members", [
                'method'  => 'POST',
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode( 'api:' . $provider->get_setting( 'api_key' ) ),
                ],
                'body' => http_build_query( $body ),
            ] );
            return ! is_wp_error( $response );
        }

        return true;
    }

    /**
     * BillionMail: Subscribers API with attributes + lists.
     */
    private static function sync_contact_billionmail( $provider, $contact ) {
        $api_url = rtrim( $provider->get_setting( 'api_url' ), '/' );

        $body = [
            'email'    => $contact['email'],
            'name'     => $contact['name'] ?? '',
            'status'   => 'confirmed',
            'lists'    => $contact['segments'] ?? [],
            'metadata' => $contact['metadata'] ?? [],
        ];

        if ( ! empty( $contact['tags'] ) ) {
            $body['tags'] = $contact['tags'];
        }

        $response = $provider->request( "{$api_url}/api/v1/subscribers", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->get_setting( 'api_key' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        return ! is_wp_error( $response );
    }

    /**
     * Mautic: Contacts API with custom fields + segments.
     */
    private static function sync_contact_mautic( $provider, $contact ) {
        $api_url = rtrim( $provider->get_setting( 'api_url' ), '/' );

        $body = [
            'email' => $contact['email'],
        ];

        if ( ! empty( $contact['name'] ) ) {
            $parts = explode( ' ', $contact['name'], 2 );
            $body['firstname'] = $parts[0];
            $body['lastname']  = $parts[1] ?? '';
        }

        if ( ! empty( $contact['metadata'] ) ) {
            $body = array_merge( $body, $contact['metadata'] );
        }

        $response = $provider->request( "{$api_url}/api/contacts/new", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $provider->get_setting( 'access_token' ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        // Add to segments
        if ( ! is_wp_error( $response ) && ! empty( $contact['segments'] ) ) {
            $contact_id = $response['contact']['id'] ?? null;
            if ( $contact_id ) {
                foreach ( $contact['segments'] as $segment_id ) {
                    $provider->request( "{$api_url}/api/segments/{$segment_id}/contact/{$contact_id}/add", [
                        'method'  => 'POST',
                        'headers' => [
                            'Authorization' => 'Bearer ' . $provider->get_setting( 'access_token' ),
                        ],
                    ] );
                }
            }
        }

        // Apply tags
        if ( ! is_wp_error( $response ) && ! empty( $contact['tags'] ) ) {
            $contact_id = $response['contact']['id'] ?? null;
            if ( $contact_id ) {
                $provider->request( "{$api_url}/api/contacts/{$contact_id}/edit", [
                    'method'  => 'PUT',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $provider->get_setting( 'access_token' ),
                        'Content-Type'  => 'application/json',
                    ],
                    'body' => wp_json_encode( [
                        'tags' => $contact['tags'],
                    ] ),
                ] );
            }
        }

        return ! is_wp_error( $response );
    }

    /**
     * Listmonk: Subscribers API with attribs + lists.
     */
    private static function sync_contact_listmonk( $provider, $contact ) {
        $api_url = rtrim( $provider->get_setting( 'api_url' ), '/' );

        $attribs = (object) [];
        if ( ! empty( $contact['name'] ) ) {
            $attribs->name = $contact['name'];
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
        if ( ! empty( $contact['segments'] ) ) {
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

        $response = $provider->request( "{$api_url}/api/subscribers", [
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( 'api:' . $provider->get_setting( 'api_key' ) ),
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        return ! is_wp_error( $response );
    }
}

