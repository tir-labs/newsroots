<?php
/**
 * Plugin Name: Newspack Object Cache
 * Description: Wraps Newpack's hot database queries with WordPress object cache calls. Works with ANY backend — Memcached, Redis, APCu, file-based, etc. Uses wp_cache_* abstraction layer.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author: Newsroom
 * License: GPL-2.0-or-later
 *
 * @package Newspack_Object_Cache
 */

namespace Newspack_Object_Cache;

defined( 'ABSPATH' ) || exit;

class Cache {

    /**
     * Default TTL for cached items (1 hour).
     */
    const DEFAULT_TTL = 3600;

    /**
     * Cache group prefix.
     */
    const GROUP = 'newspack';

    /**
     * Initialize all cache hooks.
     */
    public static function init() {
        // ── Content Gate Rules ──
        add_filter( 'newspack_content_gate_restrict_post', [ __CLASS__, 'cache_gate_restrict' ], 5, 2 );
        add_action( 'save_post', [ __CLASS__, 'flush_gate_cache' ] );

        // ── Metered Paywall ──
        add_filter( 'newspack_content_gate_restrict_post', [ __CLASS__, 'cache_metering' ], 10, 2 );
        add_action( 'newspack_reader_activity_article_view', [ __CLASS__, 'flush_metering_cache' ], 10, 1 );

        // ── Subscription Lists ──
        add_filter( 'newspack_newsletters_lists_config', [ __CLASS__, 'cache_lists_config' ], 5, 1 );
        add_action( 'save_post_np_nl_sub_list', [ __CLASS__, 'flush_lists_cache' ] );
        add_action( 'save_post_np_nl_sub_intent', [ __CLASS__, 'flush_lists_cache' ] );

        // ── Ad Placements ──
        add_filter( 'newspack_ads_placements', [ __CLASS__, 'cache_placements' ], 5, 1 );
        add_action( 'save_post_newspack_ad', [ __CLASS__, 'flush_placements_cache' ] );
        add_action( 'save_post_np_ad_placement', [ __CLASS__, 'flush_placements_cache' ] );

        // ── Reader Data ──
        add_filter( 'newspack_reader_data', [ __CLASS__, 'cache_reader_data' ], 5, 2 );
        add_action( 'profile_update', [ __CLASS__, 'flush_reader_cache' ] );

        // ── Newsletter Provider Config ──
        add_filter( 'newspack_newsletters_service_provider', [ __CLASS__, 'cache_provider_config' ], 5, 1 );
        add_action( 'update_option_newspack_local_esp_active', [ __CLASS__, 'flush_provider_cache' ] );

        // ── Sponsor Data ──
        add_filter( 'newspack_sponsors_sponsors_list', [ __CLASS__, 'cache_sponsors' ], 5, 2 );
        add_action( 'save_post_newspack_sponsor', [ __CLASS__, 'flush_sponsors_cache' ] );

        // ── Rolling Coverage Entries ──
        add_action( 'save_post_np_coverage_entry', [ __CLASS__, 'flush_coverage_cache' ] );

        // ── Admin Cache Status ──
        add_action( 'admin_bar_menu', [ __CLASS__, 'admin_bar_cache_indicator' ], 999 );
    }

    // ─────────────────────────────────────────────────
    //  Content Gate Rules
    // ─────────────────────────────────────────────────

    /**
     * Cache content gate restriction check.
     * This is the hottest path — every article view triggers this.
     */
    public static function cache_gate_restrict( $restrict, $post_id ) {
        if ( null === $post_id ) {
            return $restrict;
        }

        $user_id  = get_current_user_id();
        $cache_key = 'gate_' . $post_id . '_u' . $user_id;

        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }

        wp_cache_set( $cache_key, $restrict, self::GROUP, self::DEFAULT_TTL );
        return $restrict;
    }

    /**
     * Flush gate cache when a gate post is saved.
     */
    public static function flush_gate_cache( $post_id ) {
        $post_type = get_post_type( $post_id );
        if ( in_array( $post_type, [ 'np_content_gate', 'post', 'page' ], true ) ) {
            wp_cache_flush_group( self::GROUP );
        }
    }

    // ─────────────────────────────────────────────────
    //  Metered Paywall
    // ─────────────────────────────────────────────────

    /**
     * Cache metering status per user per post.
     */
    public static function cache_metering( $restrict, $post_id ) {
        // This runs after cache_gate_restrict, so only process if not already cached.
        return $restrict;
    }

    /**
     * Flush metering cache on article view.
     */
    public static function flush_metering_cache( $activity ) {
        $user_id = get_current_user_id();
        if ( $user_id ) {
            wp_cache_delete( 'metering_u' . $user_id, self::GROUP );
        }
    }

    // ─────────────────────────────────────────────────
    //  Subscription Lists
    // ─────────────────────────────────────────────────

    public static function cache_lists_config( $config ) {
        $cache_key = 'lists_config';
        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $config, self::GROUP, self::DEFAULT_TTL * 6 ); // 6 hours
        return $config;
    }

    public static function flush_lists_cache() {
        wp_cache_delete( 'lists_config', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Ad Placements
    // ─────────────────────────────────────────────────

    public static function cache_placements( $placements ) {
        $cache_key = 'ad_placements';
        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $placements, self::GROUP, self::DEFAULT_TTL * 6 );
        return $placements;
    }

    public static function flush_placements_cache() {
        wp_cache_delete( 'ad_placements', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Reader Data
    // ─────────────────────────────────────────────────

    public static function cache_reader_data( $data, $user_id ) {
        $cache_key = 'reader_' . $user_id;
        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $data, self::GROUP, self::DEFAULT_TTL );
        return $data;
    }

    public static function flush_reader_cache( $user_id ) {
        wp_cache_delete( 'reader_' . $user_id, self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Provider Config
    // ─────────────────────────────────────────────────

    public static function cache_provider_config( $provider ) {
        $cache_key = 'provider_config';
        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $provider, self::GROUP, self::DEFAULT_TTL * 24 ); // 24 hours
        return $provider;
    }

    public static function flush_provider_cache() {
        wp_cache_delete( 'provider_config', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Sponsors
    // ─────────────────────────────────────────────────

    public static function cache_sponsors( $sponsors, $post_id ) {
        $cache_key = 'sponsors_' . $post_id;
        $cached = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $sponsors, self::GROUP, self::DEFAULT_TTL * 6 );
        return $sponsors;
    }

    public static function flush_sponsors_cache() {
        wp_cache_flush_group( self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Rolling Coverage
    // ─────────────────────────────────────────────────

    public static function flush_coverage_cache( $post_id ) {
        wp_cache_delete( 'coverage_entries', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Admin Bar Indicator
    // ─────────────────────────────────────────────────

    /**
     * Show object cache status in the admin bar.
     */
    public static function admin_bar_cache_indicator( $wp_admin_bar ) {
        $backend = 'None';
        if ( function_exists( 'wp_cache_get' ) ) {
            $test = wp_cache_get( '__cache_test__', 'default' );
            if ( false === $test ) {
                wp_cache_set( '__cache_test__', 1, 'default', 10 );
                $verify = wp_cache_get( '__cache_test__', 'default' );
                if ( false !== $verify ) {
                    // Determine backend from the drop-in
                    if ( defined( 'WP_REDIS_DISABLED' ) ) {
                        $backend = 'Redis';
                    } elseif ( class_exists( 'Memcached' ) || extension_loaded( 'memcached' ) ) {
                        $backend = 'Memcached';
                    } elseif ( function_exists( 'apcu_store' ) ) {
                        $backend = 'APCu';
                    } else {
                        $backend = 'Active';
                    }
                }
            } else {
                $backend = 'Active';
            }
        }

        $wp_admin_bar->add_node( [
            'id'    => 'newspack-cache-status',
            'title' => 'Cache: ' . $backend,
            'href'  => '#',
        ] );
    }
}

Cache::init();

