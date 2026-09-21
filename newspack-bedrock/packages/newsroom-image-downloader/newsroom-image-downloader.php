<?php
/**
 * Plugin Name: Newsroom Image Downloader
 * Plugin URI:  https://newsroom.dev
 * Description: Download external images into your WordPress media library from the admin dashboard. Scans posts for external image URLs and imports them locally. Web-based wrapper around the image download logic.
 * Version:     1.0.0
 * Requires PHP: 8.3
 * Requires at least: 6.5
 * Author:      Newsroom
 * License:     GPL-2.0-or-later
 * Text Domain: newsroom-image-downloader
 *
 * @package Newsroom_Image_Downloader
 */

namespace Newsroom_Image_Downloader;

defined( 'ABSPATH' ) || exit;

class Downloader {

    const VERSION = '1.0.0';
    const BATCH_SIZE = 20;
    const OPTION_LAST_SCAN = 'newsroom_img_downloader_last_scan';
    const OPTION_SCAN_RESULTS = 'newsroom_img_downloader_scan_results';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'wp_ajax_newsroom_img_scan', [ __CLASS__, 'ajax_scan' ] );
        add_action( 'wp_ajax_newsroom_img_download', [ __CLASS__, 'ajax_download' ] );
        add_action( 'wp_ajax_newsroom_img_download_single', [ __CLASS__, 'ajax_download_single' ] );
        add_action( 'wp_ajax_newsroom_img_get_logs', [ __CLASS__, 'ajax_get_logs' ] );
    }

    public static function add_menu() {
        add_media_page(
            'Image Downloader',
            'Image Downloader',
            'manage_options',
            'newsroom-image-downloader',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( 'media_page_newsroom-image-downloader' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'newsroom-image-downloader',
            plugin_dir_url( __FILE__ ) . 'assets/admin.css',
            [],
            self::VERSION
        );
        wp_enqueue_script(
            'newsroom-image-downloader',
            plugin_dir_url( __FILE__ ) . 'assets/admin.js',
            [ 'jquery' ],
            self::VERSION,
            true
        );
        wp_localize_script( 'newsroom-image-downloader', 'newsroomImgDownloader', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'newsroom_img_downloader' ),
            'strings' => [
                'scanning'    => __( 'Scanning posts for external images...', 'newsroom-image-downloader' ),
                'downloading' => __( 'Downloading images...', 'newsroom-image-downloader' ),
                'complete'    => __( 'Complete!', 'newsroom-image-downloader' ),
                'error'       => __( 'An error occurred.', 'newsroom-image-downloader' ),
            ],
        ] );
    }

    // ─────────────────────────────────────────────────
    //  AJAX: Scan for external images
    // ─────────────────────────────────────────────────

    public static function ajax_scan() {
        check_ajax_referer( 'newsroom_img_downloader' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $post_types = isset( $_POST['post_types'] ) ? sanitize_text_field( wp_unslash( $_POST['post_types'] ) ) : 'post,page';
        $post_types = array_map( 'trim', explode( ',', $post_types ) );

        $args = [
            'post_type'      => $post_types,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ];

        $post_ids = get_posts( $args );
        $results  = [];
        $total_images = 0;

        foreach ( $post_ids as $post_id ) {
            $content = get_post_field( 'post_content', $post_id );
            $images  = self::extract_external_images( $content, $post_id );

            if ( ! empty( $images ) ) {
                $results[ $post_id ] = [
                    'title'  => get_the_title( $post_id ),
                    'edit'   => get_edit_post_link( $post_id ),
                    'images' => $images,
                ];
                $total_images += count( $images );
            }
        }

        update_option( self::OPTION_SCAN_RESULTS, $results, false );
        update_option( self::OPTION_LAST_SCAN, current_time( 'mysql' ), false );

        wp_send_json_success( [
            'posts_with_external' => count( $results ),
            'total_images'        => $total_images,
            'results'             => $results,
        ] );
    }

    // ─────────────────────────────────────────────────
    //  AJAX: Download all external images
    // ─────────────────────────────────────────────────

    public static function ajax_download() {
        check_ajax_referer( 'newsroom_img_downloader' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $results = get_option( self::OPTION_SCAN_RESULTS, [] );
        if ( empty( $results ) ) {
            wp_send_json_error( 'No scan results found. Run a scan first.' );
        }

        $default_host = isset( $_POST['default_host'] ) ? esc_url_raw( wp_unslash( $_POST['default_host'] ) ) : '';
        $dry_run      = ! empty( $_POST['dry_run'] );
        $downloaded   = 0;
        $errors       = 0;
        $log          = [];

        foreach ( $results as $post_id => $data ) {
            $content = get_post_field( 'post_content', $post_id );
            $modified = false;

            foreach ( $data['images'] as $img ) {
                $src = $img['src'];

                // Make relative URLs absolute
                if ( $default_host && ! self::is_absolute_url( $src ) ) {
                    $src = rtrim( $default_host, '/' ) . '/' . ltrim( $src, '/' );
                }

                $sideloaded = self::sideload_image( $src, $post_id, $dry_run );

                if ( is_wp_error( $sideloaded ) ) {
                    $errors++;
                    $log[] = [
                        'status'  => 'error',
                        'post_id' => $post_id,
                        'url'     => $src,
                        'message' => $sideloaded->get_error_message(),
                    ];
                } else {
                    // Replace the external URL with the local URL in post content
                    if ( ! $dry_run && isset( $sideloaded['url'] ) ) {
                        $content = str_replace( $src, $sideloaded['url'], $content );
                        $modified = true;
                    }
                    $downloaded++;
                    $log[] = [
                        'status'  => 'success',
                        'post_id' => $post_id,
                        'url'     => $src,
                        'local'   => $sideloaded['url'] ?? '',
                    ];
                }
            }

            // Update the post content with local URLs
            if ( $modified && ! $dry_run ) {
                wp_update_post( [
                    'ID'           => $post_id,
                    'post_content' => $content,
                ] );
            }
        }

        wp_send_json_success( [
            'downloaded' => $downloaded,
            'errors'     => $errors,
            'dry_run'    => $dry_run,
            'log'        => $log,
        ] );
    }

    // ─────────────────────────────────────────────────
    //  AJAX: Download a single image
    // ─────────────────────────────────────────────────

    public static function ajax_download_single() {
        check_ajax_referer( 'newsroom_img_downloader' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $url     = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( empty( $url ) ) {
            wp_send_json_error( 'No URL provided.' );
        }

        $sideloaded = self::sideload_image( $url, $post_id );

        if ( is_wp_error( $sideloaded ) ) {
            wp_send_json_error( $sideloaded->get_error_message() );
        }

        // Replace in post content
        if ( $post_id && ! empty( $sideloaded['url'] ) ) {
            $content = get_post_field( 'post_content', $post_id );
            $content = str_replace( $url, $sideloaded['url'], $content );
            wp_update_post( [
                'ID'           => $post_id,
                'post_content' => $content,
            ] );
        }

        wp_send_json_success( [
            'original'  => $url,
            'local'     => $sideloaded['url'] ?? '',
            'attach_id' => $sideloaded['id'] ?? 0,
        ] );
    }

    // ─────────────────────────────────────────────────
    //  AJAX: Get download logs
    // ─────────────────────────────────────────────────

    public static function ajax_get_logs() {
        check_ajax_referer( 'newsroom_img_downloader' );
        wp_send_json_success( [
            'last_scan' => get_option( self::OPTION_LAST_SCAN, 'Never' ),
            'results'   => get_option( self::OPTION_SCAN_RESULTS, [] ),
        ] );
    }

    // ─────────────────────────────────────────────────
    //  Core: Extract external images from HTML
    // ─────────────────────────────────────────────────

    public static function extract_external_images( $html, $post_id = 0 ) {
        $images = [];

        // Match img src attributes
        if ( preg_match_all( '/<img[^>]+src=["\']([^"\'>]+)["\']/i', $html, $matches ) ) {
            foreach ( $matches[1] as $src ) {
                if ( self::is_external_url( $src ) ) {
                    $images[] = [
                        'src'  => $src,
                        'type' => 'img_src',
                    ];
                }
            }
        }

        // Match srcset attributes
        if ( preg_match_all( '/srcset=["\']([^"\'>]+)["\']/i', $html, $srcset_matches ) ) {
            foreach ( $srcset_matches[1] as $srcset ) {
                $parts = explode( ',', $srcset );
                foreach ( $parts as $part ) {
                    $url = trim( explode( ' ', trim( $part ) )[0] );
                    if ( self::is_external_url( $url ) ) {
                        $images[] = [
                            'src'  => $url,
                            'type' => 'srcset',
                        ];
                    }
                }
            }
        }

        // Match background images
        if ( preg_match_all( '/background(?:-image)?\s*:\s*url\(["\']?([^"\')]+)["\']?\)/i', $html, $bg_matches ) ) {
            foreach ( $bg_matches[1] as $src ) {
                if ( self::is_external_url( $src ) ) {
                    $images[] = [
                        'src'  => $src,
                        'type' => 'background',
                    ];
                }
            }
        }

        // Deduplicate by URL
        $seen = [];
        $unique = [];
        foreach ( $images as $img ) {
            if ( ! isset( $seen[ $img['src'] ] ) ) {
                $seen[ $img['src'] ] = true;
                $unique[] = $img;
            }
        }

        return $unique;
    }

    // ─────────────────────────────────────────────────
    //  Core: Sideload an image into the media library
    // ─────────────────────────────────────────────────

    public static function sideload_image( $url, $post_id = 0, $dry_run = false ) {
        if ( ! self::is_valid_url( $url ) ) {
            return new \WP_Error( 'invalid_url', 'Invalid URL: ' . $url );
        }

        if ( $dry_run ) {
            return [ 'url' => $url, 'id' => 0, 'dry_run' => true ];
        }

        // Check if already downloaded
        $existing = self::get_existing_attachment( $url );
        if ( $existing ) {
            return [
                'url' => wp_get_attachment_url( $existing ),
                'id'  => $existing,
            ];
        }

        // Download the file
        $tmp = download_url( $url, 30 );
        if ( is_wp_error( $tmp ) ) {
            return $tmp;
        }

        // Build the file array
        $file_array = [
            'name'     => basename( wp_parse_url( $url, PHP_URL_PATH ) ),
            'tmp_name' => $tmp,
        ];

        // Make sure the filename has an extension
        if ( ! pathinfo( $file_array['name'], PATHINFO_EXTENSION ) ) {
            $type = wp_check_filetype( $file_array['name'] );
            if ( empty( $type['ext'] ) ) {
                // Try to detect from mime type
                $finfo = finfo_open( FILEINFO_MIME_TYPE );
                $mime  = finfo_file( $finfo, $tmp );
                finfo_close( $finfo );

                $ext_map = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp',
                    'image/svg'  => 'svg',
                ];
                $ext = $ext_map[ $mime ] ?? 'jpg';
                $file_array['name'] .= '.' . $ext;
            }
        }

        // Sideload into media library
        $attach_id = media_handle_sideload( $file_array, $post_id );

        if ( is_wp_error( $attach_id ) ) {
            @unlink( $tmp );
            return $attach_id;
        }

        // Store the original URL as meta for deduplication
        update_post_meta( $attach_id, '_newsroom_original_url', $url );

        return [
            'url' => wp_get_attachment_url( $attach_id ),
            'id'  => $attach_id,
        ];
    }

    // ─────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────

    public static function is_external_url( $url ) {
        if ( ! self::is_absolute_url( $url ) ) {
            return false;
        }
        $host = wp_parse_url( $url, PHP_URL_HOST );
        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        return $host && $host !== $site_host;
    }

    public static function is_absolute_url( $url ) {
        return preg_match( '/^https?:\/\//i', $url );
    }

    public static function is_valid_url( $url ) {
        return filter_var( $url, FILTER_VALIDATE_URL ) && preg_match( '/^https?:\/\//i', $url );
    }

    public static function get_existing_attachment( $url ) {
        global $wpdb;
        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_newsroom_original_url' AND meta_value = %s LIMIT 1",
                $url
            )
        );
        return $attachment_id ? (int) $attachment_id : null;
    }

    // ─────────────────────────────────────────────────
    //  Admin Page
    // ─────────────────────────────────────────────────

    public static function render_page() {
        $last_scan    = get_option( self::OPTION_LAST_SCAN, 'Never' );
        $scan_results = get_option( self::OPTION_SCAN_RESULTS, [] );
        $total_images = 0;
        foreach ( $scan_results as $data ) {
            $total_images += count( $data['images'] ?? [] );
        }
        ?>
        <div class="wrap">
            <h1>📥 Newsroom Image Downloader</h1>
            <p>Download external images into your WordPress media library. Scans all published posts for external image URLs and imports them locally.</p>

            <div class="newsroom-img-status" style="background:#f0f7ff; border:1px solid #c3daf0; border-radius:8px; padding:16px; margin:16px 0;">
                <strong>Last Scan:</strong> <?php echo esc_html( $last_scan ); ?> &nbsp;|&nbsp;
                <strong>Posts with external images:</strong> <?php echo count( $scan_results ); ?> &nbsp;|&nbsp;
                <strong>Total external images:</strong> <?php echo $total_images; ?>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; max-width:900px;">
                <!-- Scan Panel -->
                <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:20px;">
                    <h2 style="margin-top:0;">🔍 Step 1: Scan</h2>
                    <p>Scan your posts for external image URLs.</p>
                    <p>
                        <label for="scan-post-types"><strong>Post Types:</strong></label><br>
                        <input type="text" id="scan-post-types" value="post,page" class="regular-text" placeholder="post,page" />
                    </p>
                    <button id="btn-scan" class="button button-primary">Scan for External Images</button>
                    <div id="scan-status" style="margin-top:10px;"></div>
                </div>

                <!-- Download Panel -->
                <div style="background:#fff; border:1px solid #ddd; border-radius:8px; padding:20px;">
                    <h2 style="margin-top:0;">⬇️ Step 2: Download</h2>
                    <p>Download all found images into your media library and replace URLs in post content.</p>
                    <p>
                        <label for="default-host"><strong>Default Host</strong> (for relative URLs):</label><br>
                        <input type="text" id="default-host" value="" class="regular-text" placeholder="https://oldsite.com" />
                    </p>
                    <p>
                        <label><input type="checkbox" id="dry-run" value="1" /> Dry run (preview only, no changes)</label>
                    </p>
                    <button id="btn-download" class="button button-secondary">Download All Images</button>
                    <div id="download-status" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- Results Table -->
            <div id="scan-results" style="margin-top:20px; max-width:900px;">
                <?php if ( ! empty( $scan_results ) ) : ?>
                    <h2>Scan Results</h2>
                    <table class="widefat striped" id="results-table">
                        <thead>
                            <tr>
                                <th>Post</th>
                                <th>External Images</th>
                                <th style="width:100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $scan_results as $post_id => $data ) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url( $data['edit'] ); ?>" target="_blank"><?php echo esc_html( $data['title'] ); ?></a>
                                        <span style="color:#999;">(#<?php echo $post_id; ?>)</span>
                                    </td>
                                    <td>
                                        <ul style="margin:0; padding-left:16px;">
                                            <?php foreach ( $data['images'] as $img ) : ?>
                                                <li style="word-break:break-all;">
                                                    <code style="font-size:11px;"><?php echo esc_html( $img['src'] ); ?></code>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td>
                                        <button class="button button-small btn-download-single" data-post-id="<?php echo esc_attr( $post_id ); ?>">Download</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Download Log -->
            <div id="download-log" style="margin-top:20px; max-width:900px; display:none;">
                <h2>Download Log</h2>
                <div id="log-output" style="background:#1e1e1e; color:#d4d4d4; padding:16px; border-radius:8px; max-height:400px; overflow-y:auto; font-family:monospace; font-size:12px;"></div>
            </div>
        </div>
        <?php
    }
}

Downloader::init();

