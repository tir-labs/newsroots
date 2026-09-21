<?php
/**
 * Amazon SES provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class SES extends Base_Provider {

    protected $slug = 'ses';
    protected $name = 'Amazon SES';
    protected $type = 'api';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'access_key' ) )
            && ! empty( $this->get_setting( 'secret_key' ) )
            && ! empty( $this->get_setting( 'region' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'access_key',
                'label'       => 'Access Key ID',
                'type'        => 'text',
                'description' => 'Your AWS IAM Access Key ID.',
            ],
            [
                'name'        => 'secret_key',
                'label'       => 'Secret Access Key',
                'type'        => 'password',
                'description' => 'Your AWS IAM Secret Access Key.',
            ],
            [
                'name'        => 'region',
                'label'       => 'Region',
                'type'        => 'select',
                'options'     => [
                    'us-east-1'      => 'US East (N. Virginia)',
                    'us-east-2'      => 'US East (Ohio)',
                    'us-west-1'      => 'US West (N. California)',
                    'us-west-2'      => 'US West (Oregon)',
                    'eu-west-1'      => 'EU (Ireland)',
                    'eu-central-1'   => 'EU (Frankfurt)',
                    'eu-south-1'     => 'EU (Milan)',
                    'ap-southeast-1' => 'Asia Pacific (Singapore)',
                    'ap-southeast-2' => 'Asia Pacific (Sydney)',
                    'ap-northeast-1' => 'Asia Pacific (Tokyo)',
                    'sa-east-1'      => 'South America (São Paulo)',
                ],
                'description' => 'AWS region where SES is configured.',
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
            return new \WP_Error( 'ses_not_configured', 'Amazon SES is not configured.' );
        }

        // Build AWS SES v2 API request
        $region    = $this->get_setting( 'region' );
        $endpoint  = "https://email.{$region}.amazonaws.com/v2/email/outbound-emails";
        $access_key = $this->get_setting( 'access_key' );
        $secret_key = $this->get_setting( 'secret_key' );
        $from       = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name  = $args['from_name'] ?? $this->get_setting( 'from_name' );

        $body = [
            'Destination' => [
                'ToAddresses' => [ $args['to'] ],
            ],
            'Content' => [
                'Simple' => [
                    'Subject' => [
                        'Data' => $args['subject'],
                    ],
                    'Body' => [
                        'Html' => [
                            'Data' => $args['html'],
                        ],
                    ],
                ],
            ],
            'FromEmailAddress' => $from_name ? "{$from_name} <{$from}>" : $from,
        ];

        // AWS Signature v4
        $date       = gmdate( 'Ymd\THis\Z' );
        $date_short = gmdate( 'Ymd' );
        $payload    = wp_json_encode( $body );
        $host       = "email.{$region}.amazonaws.com";

        $canonical_request = "POST\n/v2/email/outbound-emails\n\nhost:{$host}\nx-amz-date:{$date}\n\nhost;x-amz-date\n" . hash( 'sha256', $payload );
        $string_to_sign    = "AWS4-HMAC-SHA256\n{$date}\n{$date_short}/{$region}/ses/aws4_request\n" . hash( 'sha256', $canonical_request );

        $k_date    = hash_hmac( 'sha256', $date_short, "AWS4{$secret_key}", true );
        $k_region  = hash_hmac( 'sha256', $region, $k_date, true );
        $k_service = hash_hmac( 'sha256', 'ses', $k_region, true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
        $signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

        $auth_header = "AWS4-HMAC-SHA256 Credential={$access_key}/{$date_short}/{$region}/ses/aws4_request, SignedHeaders=host;x-amz-date, Signature={$signature}";

        $response = $this->request( $endpoint, [
            'method'  => 'POST',
            'headers' => [
                'Content-Type'  => 'application/json',
                'X-Amz-Date'    => $date,
                'Authorization' => $auth_header,
            ],
            'body' => $payload,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return true;
    }
}

