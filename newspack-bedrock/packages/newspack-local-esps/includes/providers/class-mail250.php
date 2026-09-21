<?php
/**
 * Mail250 provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mail250 extends Base_Provider {

    protected $slug = 'mail250';
    protected $name = 'Mail250';
    protected $type = 'smtp';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'username' ) ) && ! empty( $this->get_setting( 'password' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'username',
                'label'       => 'SMTP Username',
                'type'        => 'text',
                'description' => 'Your Mail250 SMTP username (from the SMTP credentials page).',
            ],
            [
                'name'        => 'password',
                'label'       => 'SMTP Password',
                'type'        => 'password',
                'description' => 'Your Mail250 SMTP password.',
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
            return new \WP_Error( 'mail250_not_configured', 'Mail250 is not configured.' );
        }

        $from      = $args['from'] ?? $this->get_setting( 'from_email' );
        $from_name = $args['from_name'] ?? $this->get_setting( 'from_name' );

        add_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ] );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from}>",
        ];

        $result = wp_mail( $args['to'], $args['subject'], $args['html'], $headers );

        remove_action( 'phpmailer_init', [ $this, 'configure_phpmailer' ] );

        if ( ! $result ) {
            return new \WP_Error( 'mail250_send_failed', 'Failed to send via Mail250.' );
        }

        return true;
    }

    public function configure_phpmailer( $phpmailer ) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = 'smtp.mail250.com';
        $phpmailer->Port       = 587;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Username   = $this->get_setting( 'username' );
        $phpmailer->Password   = $this->get_setting( 'password' );
        $phpmailer->SMTPAuth   = true;
        $phpmailer->CharSet    = 'UTF-8';
    }
}

