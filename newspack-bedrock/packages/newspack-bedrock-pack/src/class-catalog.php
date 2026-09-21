<?php
/**
 * Bundled companion plugin catalog.
 *
 * @package Newspack_Bedrock_Pack
 */

namespace Newspack_Bedrock_Pack;

defined( 'ABSPATH' ) || exit;

class Catalog {

    /**
     * Plugins packaged with Newspack Bedrock, excluding the base newspack-plugin.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function plugins(): array {
        return [
            'newspack-blocks' => [
                'Name'        => 'Newspack Blocks',
                'Description' => 'Gutenberg blocks for news publishers. Standard block markup, Sage-ready.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'newspack-newsletters' => [
                'Name'        => 'Newspack Newsletters',
                'Description' => 'Newsletter authoring in the block editor.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'newspack-popups' => [
                'Name'        => 'Newspack Campaigns',
                'Description' => 'Prompts, overlays, and inline campaigns.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'newspack-ads' => [
                'Name'        => 'Newspack Ads',
                'Description' => 'Ad placements for the newsroom.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'newspack-sponsors' => [
                'Name'        => 'Newspack Sponsors',
                'Description' => 'Sponsored and underwritten content.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'co-authors-plus' => [
                'Name'        => 'Co-Authors Plus',
                'Description' => 'Multiple bylines and guest authors.',
                'Author'      => 'Automattic',
                'recommended' => true,
            ],
            'slim-seo' => [
                'Name'        => 'Slim SEO',
                'Description' => 'Lightweight SEO. Meta AI uses the WordPress AI plugin.',
                'Author'      => 'eLightUp',
                'recommended' => true,
            ],
            'onesignal-free-web-push-notifications' => [
                'Name'        => 'OneSignal Push Notifications',
                'Description' => 'Web push notifications for readers.',
                'Author'      => 'OneSignal',
                'recommended' => false,
            ],
            'prevent-direct-access' => [
                'Name'        => 'Prevent Direct Access',
                'Description' => 'Stop public and bot access to private media files.',
                'Author'      => 'BWPS',
                'recommended' => true,
            ],
            'newspack-local-esps' => [
                'Name'        => 'Newspack Local ESPs',
                'Description' => 'Local newsletter providers (SES, SMTP, Resend, Mailgun, Listmonk, and more).',
                'Author'      => 'Postdated',
                'recommended' => true,
            ],
            'newsroom-speed-cache' => [
                'Name'        => 'Newsroom Speed Cache',
                'Description' => 'Memcached wrappers for Newspack hot paths.',
                'Author'      => 'Postdated',
                'recommended' => true,
            ],
            'newsroom-image-downloader' => [
                'Name'        => 'Newsroom Image Downloader',
                'Description' => 'Sideload external images into the media library.',
                'Author'      => 'Postdated',
                'recommended' => false,
            ],
            'newspack-rolling-coverage' => [
                'Name'        => 'Newspack Rolling Coverage',
                'Description' => 'Live coverage with Slack and Discord.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-story-budget' => [
                'Name'        => 'Newspack Story Budget',
                'Description' => 'Assignments and story planning.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-elections' => [
                'Name'        => 'Newspack Elections',
                'Description' => 'Election candidate profiles.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-multibranded-site' => [
                'Name'        => 'Newspack Multibranded Site',
                'Description' => 'Brand sections of the site independently.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-cache-cozy' => [
                'Name'        => 'Newspack Cache Cozy',
                'Description' => 'Cache helpers for Newspack.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-post-image-downloader' => [
                'Name'        => 'Newspack Post Image Downloader',
                'Description' => 'WP-CLI image sideload tools.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'newspack-revisions-enhanced' => [
                'Name'        => 'Newspack Revisions Enhanced',
                'Description' => 'Richer revision history.',
                'Author'      => 'Automattic',
                'recommended' => false,
            ],
            'fifu-premium' => [
                'Name'        => 'Featured Image from URL',
                'Description' => 'Use external or local featured images.',
                'Author'      => 'FIFU',
                'recommended' => false,
            ],
            'flux-media-optimizer' => [
                'Name'        => 'Flux Media Optimizer',
                'Description' => 'Local AVIF/WebP optimization (no media leaves the server).',
                'Author'      => 'Flux Plugins',
                'recommended' => false,
            ],
            'newpack-discord-bot-api' => [
                'Name'        => 'Newpack Discord Bot API',
                'Description' => 'REST API for the Discord coverage bot.',
                'Author'      => 'Postdated',
                'recommended' => false,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array {
        return array_keys( self::plugins() );
    }

    /**
     * @return list<string>
     */
    public static function recommended_slugs(): array {
        $out = [];
        foreach ( self::plugins() as $slug => $plugin ) {
            if ( ! empty( $plugin['recommended'] ) ) {
                $out[] = $slug;
            }
        }
        return $out;
    }

    /**
     * Bedrock packages directory (repo root / packages).
     */
    public static function packages_dir(): string {
        if ( defined( 'WP_CONTENT_DIR' ) ) {
            $candidate = dirname( WP_CONTENT_DIR, 2 ) . '/packages';
            if ( is_dir( $candidate ) ) {
                return $candidate;
            }
        }
        return dirname( __DIR__, 2 );
    }
}
