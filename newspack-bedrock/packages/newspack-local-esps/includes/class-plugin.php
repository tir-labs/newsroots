<?php
/**
 * Local ESP plugin bootstrap.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs;

use Newspack_Local_ESPs\Providers\Base_Provider;
use Newspack_Local_ESPs\Providers\BillionMail;
use Newspack_Local_ESPs\Providers\ElasticEmail;
use Newspack_Local_ESPs\Providers\Listmonk;
use Newspack_Local_ESPs\Providers\Mail250;
use Newspack_Local_ESPs\Providers\Mailgun;
use Newspack_Local_ESPs\Providers\Mailtrap;
use Newspack_Local_ESPs\Providers\Mautic;
use Newspack_Local_ESPs\Providers\Plunk;
use Newspack_Local_ESPs\Providers\Resend;
use Newspack_Local_ESPs\Providers\SES;
use Newspack_Local_ESPs\Providers\SMTP;

defined('ABSPATH') || exit;

class Plugin {

    public const VERSION = '1.1.0';

    /** @var array<string, class-string<Base_Provider>> */
    private static array $provider_classes = [
        'ses' => SES::class,
        'smtp' => SMTP::class,
        'resend' => Resend::class,
        'plunk' => Plunk::class,
        'elastic_email' => ElasticEmail::class,
        'mailgun' => Mailgun::class,
        'mailtrap' => Mailtrap::class,
        'mail250' => Mail250::class,
        'billionmail' => BillionMail::class,
        'mautic' => Mautic::class,
        'listmonk' => Listmonk::class,
    ];

    public static function init(): void {
        Admin::init();
        Subscriber_Sync::init();
        add_action('plugins_loaded', [self::class, 'register_newsletters_provider'], 20);
    }

    public static function get_provider_definitions(): array {
        return [
            'ses' => ['name' => 'Amazon SES', 'description' => 'Amazon Simple Email Service API', 'type' => 'api', 'class' => SES::class],
            'smtp' => ['name' => 'Generic SMTP', 'description' => 'Any SMTP server, including WP Mail SMTP relays', 'type' => 'smtp', 'class' => SMTP::class],
            'resend' => ['name' => 'Resend', 'description' => 'Resend transactional API', 'type' => 'api', 'class' => Resend::class],
            'plunk' => ['name' => 'Plunk', 'description' => 'Self-hostable Plunk API', 'type' => 'api', 'class' => Plunk::class],
            'elastic_email' => ['name' => 'Elastic Email', 'description' => 'Elastic Email API', 'type' => 'api', 'class' => ElasticEmail::class],
            'mailgun' => ['name' => 'Mailgun', 'description' => 'Mailgun messages API', 'type' => 'api', 'class' => Mailgun::class],
            'mailtrap' => ['name' => 'Mailtrap', 'description' => 'Mailtrap sending API', 'type' => 'api', 'class' => Mailtrap::class],
            'mail250' => ['name' => 'Mail250', 'description' => 'Mail250 SMTP credentials', 'type' => 'smtp', 'class' => Mail250::class],
            'billionmail' => ['name' => 'BillionMail', 'description' => 'BillionMail API', 'type' => 'api', 'class' => BillionMail::class],
            'mautic' => ['name' => 'Mautic', 'description' => 'Self-hosted Mautic REST API', 'type' => 'api', 'class' => Mautic::class],
            'listmonk' => ['name' => 'Listmonk', 'description' => 'Self-hosted Listmonk API', 'type' => 'api', 'class' => Listmonk::class],
        ];
    }

    public static function get_provider(string $slug): ?Base_Provider {
        $class = self::$provider_classes[$slug] ?? null;
        return $class ? $class::instance() : null;
    }

    public static function get_active_provider(): ?Base_Provider {
        $slug = (string) get_option('newspack_local_esp_active', '');
        return $slug !== '' ? self::get_provider($slug) : null;
    }

    public static function register_newsletters_provider(): void {
        $provider = self::get_active_provider();
        if (!$provider || !$provider->is_configured()) {
            return;
        }
        add_filter(
            'newspack_newsletters_service_provider',
            static function ($current) use ($provider) {
                return $current ?: $provider;
            }
        );
    }
}
