<?php
/**
 * Generic SMTP provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class SMTP extends Base_Provider {

    protected $slug = 'smtp';
    protected $name = 'Generic SMTP';
    protected $type = 'smtp';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'host' ) ) && ! empty( $this->get_setting( 'port' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'host',
                'label'       => 'SMTP Host',
                'type'        => 'text',
                'description' => 'e.g. smtp.gmail.com, smtp.sendgrid.net',
            ],
            [
                'name'        => 'port',
                'label'       => 'SMTP Port',
                'type'        => 'text',
                'description' => 'e.g. 587 (TLS), 465 (SSL), 25 (plain)',
            ],
            [
                'name'        => 'encryption',
                'label'       => 'Encryption',
                'type'        => 'select',
                'options'     => [
                    'tls'  => 'TLS',
                    'ssl'  => 'SSL',
                    'none' => 'None',
                ],
                'description' => 'Connection encryption type.',
            ],
            [
                'name'        => 'username',
                'label'       => 'Username',
                'type'        => 'text',
                'description' => 'SMTP authentication username.',
            ],
            [
                'name'        => 'password',
                'label'       => 'Password',
                'type'        => 'password',
                'description' => 'SMTP authentication password.',
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
            return new \WP_Error( 'smtp_not_configured', 'SMTP is not configured.' );
        }

        $from       = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name  = $args['from_name'] ?? $this->get_setting( 'from_name' );

        // Use PHPMailer via WordPress
        add_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ] );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from}>",
        ];

        $result = wp_mail( $args['to'], $args['subject'], $args['html'], $headers );

        remove_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ] );

        if ( ! $result ) {
            return new \WP_Error( 'smtp_send_failed', 'Failed to send email via SMTP.' );
        }

        return true;
    }

    /**
     * Configure PHPMailer for SMTP.
     */
    public function configure_phpmailer( $phpmailer ) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = $this->get_setting( 'host' );
        $phpmailer->Port       = (int) $this->get_setting( 'port' );
        $phpmailer->Username   = $this->get_setting( 'username' );
        $phpmailer->Password   = $this->get_setting( 'password' );
        $phpmailer->SMTPAuth   = ! empty( $this->get_setting( 'username' ) );

        $encryption = $this->get_setting( 'encryption' );
        if ( 'ssl' === $encryption ) {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ( 'tls' === $encryption ) {
            $phpmailer->SMTPSecure = 'tls';
        }

        $phpmailer->CharSet = 'UTF-8';
    }
}

