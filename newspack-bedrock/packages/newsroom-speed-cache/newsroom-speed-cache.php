<?php
/**
 * Plugin Name: Newsroom Speed Cache
 * Plugin URI:  https://github.com/newsroom/speed-cache
 * Description: Makes your newsroom blazing fast. Wraps Newpack's heaviest database queries with instant object cache lookups. Uses WordPress object cache (Memcached on Roots Trellis/Bedrock).
 * Version:     1.0.0
 * Requires PHP: 8.3
 * Requires at least: 6.5
 * Author:      Newsroom
 * Author URI:  https://newsroom.dev
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: newsroom-speed-cache
 * Domain Path: /languages
 * Requires Plugins: newspack-plugin
 *
 * @package Newsroom_Speed_Cache
 */

namespace Newsroom_Speed_Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Speed_Cache {

    /**
     * Plugin version.
     */
    const VERSION = '1.0.0';

    /**
     * Default TTL for cached items (1 hour).
     */
    const DEFAULT_TTL = HOUR_IN_SECONDS;

    /**
     * Cache group prefix.
     */
    const GROUP = 'newsroom';

    /**
     * Singleton instance.
     *
     * @var Speed_Cache|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Speed_Cache
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Check if object cache is available.
     *
     * @return bool
     */
    public static function has_object_cache() {
        static $has_cache = null;
        if ( null === $has_cache ) {
            $test_key = '__newsroom_cache_test__';
            wp_cache_set( $test_key, 1, 'default', 10 );
            $has_cache = false !== wp_cache_get( $test_key, 'default' );
            wp_cache_delete( $test_key, 'default' );
        }
        return $has_cache;
    }

    /**
     * Detect which object cache backend is in use.
     *
     * @return string Backend name.
     */
    public static function detect_backend() {
        if ( ! self::has_object_cache() ) {
            return 'none';
        }

        // Check for known drop-ins
        if ( class_exists( 'Memcached' ) || extension_loaded( 'memcached' ) ) {
            return 'memcached';
        }

        return 'active';
    }

    /**
     * Get human-readable backend name.
     *
     * @return string
     */
    public static function get_backend_label() {
        $backends = [
            'none'            => 'Not Found',
            'memcached'       => 'Memcached',
            'active'          => 'Active',
        ];

        $backend = self::detect_backend();
        return $backends[ $backend ] ?? 'Unknown';
    }

    /**
     * Initialize all cache hooks.
     */
    private function init_hooks() {
        if ( ! self::has_object_cache() ) {
            add_action( 'admin_notices', [ __CLASS__, 'no_cache_notice' ] );
            return;
        }

        // Content Gate (hottest path)
        add_filter( 'newspack_content_gate_restrict_post', [ $this, 'cache_gate_check' ], 5, 2 );
        add_action( 'save_post', [ $this, 'flush_on_post_save' ], 10, 2 );

        // Metering
        add_action( 'newspack_reader_activity_article_view', [ $this, 'flush_metering' ], 10, 1 );

        // Subscription Lists
        add_filter( 'newspack_newsletters_lists_config', [ $this, 'cache_lists_config' ], 5, 1 );
        add_action( 'save_post_np_nl_sub_list', [ $this, 'flush_lists' ] );
        add_action( 'save_post_np_nl_sub_intent', [ $this, 'flush_lists' ] );
        add_action( 'newspack_newsletters_provider_credentials_changed', [ $this, 'flush_lists' ] );

        // Ad Placements
        add_filter( 'newspack_ads_placements', [ $this, 'cache_placements' ], 5, 1 );
        add_action( 'save_post_newspack_ad', [ $this, 'flush_placements' ] );

        // Reader Data
        add_filter( 'newspack_reader_data', [ $this, 'cache_reader' ], 5, 2 );
        add_action( 'profile_update', [ $this, 'flush_reader' ] );

        // Sponsors
        add_filter( 'newspack_sponsors_sponsors_list', [ $this, 'cache_sponsors' ], 5, 2 );
        add_action( 'save_post_newspack_sponsor', [ $this, 'flush_sponsors' ] );

        // Provider Config
        add_action( 'update_option_newspack_local_esp_active', [ $this, 'flush_provider' ] );

        // Rolling Coverage
        add_action( 'save_post_np_coverage_entry', [ $this, 'flush_coverage' ] );

        // Admin bar indicator
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_indicator' ], 999 );

        // Settings page
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
    }

    // ─────────────────────────────────────────────────
    //  Content Gate (hottest path)
    // ─────────────────────────────────────────────────

    /**
     * Cache the content gate restriction check.
     * Runs on every single article page view — this is the #1 performance bottleneck.
     */
    public function cache_gate_check( $restrict, $post_id ) {
        if ( null === $post_id || ! is_singular() ) {
            return $restrict;
        }

        $user_id    = get_current_user_id();
        $cache_key  = 'gate_' . $post_id . '_u' . $user_id;
        $cached     = wp_cache_get( $cache_key, self::GROUP );

        if ( false !== $cached ) {
            return (bool) $cached;
        }

        wp_cache_set( $cache_key, $restrict ? 1 : 0, self::GROUP, self::DEFAULT_TTL );
        return $restrict;
    }

    /**
     * Flush gate cache when any post is saved.
     */
    public function flush_on_post_save( $post_id, $post ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }
        $type = $post->post_type ?? '';
        if ( in_array( $type, [ 'np_content_gate', 'post', 'page', 'np_coverage_entry' ], true ) ) {
            // Flush the entire group — gate rules can reference any post
            wp_cache_flush_group( self::GROUP );
        }
    }

    // ─────────────────────────────────────────────────
    //  Metered Paywall
    // ─────────────────────────────────────────────────

    /**
     * Flush metering cache when a reader views an article.
     */
    public function flush_metering( $activity ) {
        $user_id = get_current_user_id();
        if ( $user_id ) {
            wp_cache_delete( 'metering_u' . $user_id, self::GROUP );
        }
    }

    // ─────────────────────────────────────────────────
    //  Subscription Lists
    // ─────────────────────────────────────────────────

    /**
     * Cache the lists config query.
     */
    public function cache_lists_config( $config ) {
        $cached = wp_cache_get( 'lists_config', self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( 'lists_config', $config, self::GROUP, 6 * HOUR_IN_SECONDS );
        return $config;
    }

    public function flush_lists() {
        wp_cache_delete( 'lists_config', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Ad Placements
    // ─────────────────────────────────────────────────

    public function cache_placements( $placements ) {
        $cached = wp_cache_get( 'ad_placements', self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( 'ad_placements', $placements, self::GROUP, 6 * HOUR_IN_SECONDS );
        return $placements;
    }

    public function flush_placements() {
        wp_cache_delete( 'ad_placements', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Reader Data
    // ─────────────────────────────────────────────────

    public function cache_reader( $data, $user_id ) {
        $cache_key = 'reader_' . $user_id;
        $cached    = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $data, self::GROUP, self::DEFAULT_TTL );
        return $data;
    }

    public function flush_reader( $user_id ) {
        wp_cache_delete( 'reader_' . $user_id, self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Sponsors
    // ─────────────────────────────────────────────────

    public function cache_sponsors( $sponsors, $post_id ) {
        $cache_key = 'sponsors_' . $post_id;
        $cached    = wp_cache_get( $cache_key, self::GROUP );
        if ( false !== $cached ) {
            return $cached;
        }
        wp_cache_set( $cache_key, $sponsors, self::GROUP, 6 * HOUR_IN_SECONDS );
        return $sponsors;
    }

    public function flush_sponsors() {
        wp_cache_flush_group( self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Provider Config
    // ─────────────────────────────────────────────────

    public function flush_provider() {
        wp_cache_delete( 'provider_config', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Rolling Coverage
    // ─────────────────────────────────────────────────

    public function flush_coverage() {
        wp_cache_delete( 'coverage_entries', self::GROUP );
    }

    // ─────────────────────────────────────────────────
    //  Admin Notices
    // ─────────────────────────────────────────────────

    /**
     * Show a notice if no object cache backend is detected.
     */
    public static function no_cache_notice() {
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Newsroom Speed Cache:</strong> No object cache backend detected. ';
        echo 'Install the Memcached PECL extension and the Bedrock object-cache drop-in. ';
        echo '';
        echo 'This project uses Memcached only (Roots Trellis).';
        echo '</p></div>';
    }

    // ─────────────────────────────────────────────────
    //  Admin Bar Indicator
    // ─────────────────────────────────────────────────

    public function admin_bar_indicator( $wp_admin_bar ) {
        $backend = self::get_backend_label();
        $color   = self::has_object_cache() ? '#46b450' : '#dc3232';

        $wp_admin_bar->add_node( [
            'id'    => 'newsroom-cache',
            'title' => '<span style="color:' . $color . ';font-weight:bold;">⚡</span> Cache: ' . esc_html( $backend ),
            'href'  => admin_url( 'options-general.php?page=newsroom-speed-cache' ),
        ] );
    }

    // ─────────────────────────────────────────────────
    //  Settings Page
    // ─────────────────────────────────────────────────

    public function add_settings_page() {
        add_options_page(
            'Newsroom Speed Cache',
            'Speed Cache',
            'manage_options',
            'newsroom-speed-cache',
            [ $this, 'render_settings_page' ]
        );
    }

    public function render_settings_page() {
        $backend    = self::detect_backend();
        $has_cache  = self::has_object_cache();
        $label      = self::get_backend_label();

        // Get cache stats if available
        $stats = [];
        foreach ( [ 'gate', 'reader', 'lists_config', 'ad_placements', 'sponsors', 'provider_config', 'coverage_entries' ] as $key ) {
            $stats[ $key ] = wp_cache_get( $key, self::GROUP ) !== false ? 'cached' : 'miss';
        }
        ?>
        <div class="wrap">
            <h1>⚡ Newsroom Speed Cache</h1>

            <div style="max-width:800px;">
                <div style="background:<?php echo $has_cache ? '#f0fdf4' : '#fef2f2'; ?>; border:1px solid <?php echo $has_cache ? '#86efac' : '#fca5a5'; ?>; border-radius:8px; padding:20px; margin:20px 0;">
                    <h2 style="margin-top:0;">
                        <?php if ( $has_cache ) : ?>
                            ✅ Object Cache: <?php echo esc_html( $label ); ?>
                        <?php else : ?>
                            ❌ No Object Cache Detected
                        <?php endif; ?>
                    </h2>
                    <?php if ( ! $has_cache ) : ?>
                        <p>Your newsroom is hitting the database on every page view. Install an object cache backend for a massive speed boost:</p>
                        <ul>
                            <li><strong><a href="https://wordpress.org/plugins/memcached/" target="_blank">Memcached</a></strong> — Best for shared hosting. Drop-in replacement.</li>
                            <li><strong>APCu</strong> — In-memory, single-server only.</li>
                        </ul>
                        <p>After installing a backend, this plugin will automatically start caching.</p>
                    <?php else : ?>
                        <p>All Newpack hot paths are being cached. Your readers will see faster page loads immediately.</p>
                    <?php endif; ?>
                </div>

                <h2>What's Cached</h2>
                <table class="widefat" style="max-width:600px;">
                    <thead>
                        <tr>
                            <th>Operation</th>
                            <th>Cache Key</th>
                            <th>TTL</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Content Gate Check</td>
                            <td><code>gate_{post}_u{user}</code></td>
                            <td>1 hour</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                        <tr>
                            <td>Subscription Lists</td>
                            <td><code>lists_config</code></td>
                            <td>6 hours</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                        <tr>
                            <td>Ad Placements</td>
                            <td><code>ad_placements</code></td>
                            <td>6 hours</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                        <tr>
                            <td>Reader Data</td>
                            <td><code>reader_{user_id}</code></td>
                            <td>1 hour</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                        <tr>
                            <td>Sponsors per Post</td>
                            <td><code>sponsors_{post_id}</code></td>
                            <td>6 hours</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                        <tr>
                            <td>Provider Config</td>
                            <td><code>provider_config</code></td>
                            <td>24 hours</td>
                            <td><?php echo $has_cache ? '✅ Active' : '—'; ?></td>
                        </tr>
                    </tbody>
                </table>

                <h2>How It Works</h2>
                <p>Newsroom Speed Cache wraps Newpack's heaviest database queries with instant object cache lookups. When a reader visits an article:</p>
                <ol>
                    <li>The plugin checks Memcached/Redis first (microseconds)</li>
                    <li>If found, it returns the cached result — no database query</li>
                    <li>If not found, it queries the database, caches the result, and returns it</li>
                    <li>When content is updated (post saved, ad changed, etc.), the cache is flushed automatically</li>
                </ol>
                <p>Works with <strong>any</strong> WordPress object cache backend: Memcached, or any custom drop-in.</p>

                <h2>Cache Backend Info</h2>
                <table class="form-table" style="max-width:400px;">
                    <tr>
                        <th>Backend</th>
                        <td><?php echo esc_html( $label ); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?php echo $has_cache ? '<span style="color:green;">Connected</span>' : '<span style="color:red;">Not Connected</span>'; ?></td>
                    </tr>
                    <tr>
                        <th>Cache Group</th>
                        <td><code><?php echo esc_html( self::GROUP ); ?></code></td>
                    </tr>
                    <tr>
                        <th>PHP Extension</th>
                        <td>
                            <?php
                            $exts = [];
                            if ( extension_loaded( 'memcached' ) ) $exts[] = 'memcached';
                            if ( extension_loaded( 'redis' ) ) $exts[] = 'redis';
                            if ( extension_loaded( 'apcu' ) ) $exts[] = 'apcu';
                            echo $exts ? implode( ', ', $exts ) : 'None detected';
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}

Speed_Cache::instance();

