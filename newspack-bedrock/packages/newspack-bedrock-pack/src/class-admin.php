<?php
/**
 * Admin prompt and REST installer.
 *
 * @package Newspack_Bedrock_Pack
 */

namespace Newspack_Bedrock_Pack;

defined( 'ABSPATH' ) || exit;

class Admin {

    public static function init(): void {
        add_action( 'admin_menu', [ self::class, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue' ] );
        add_action( 'rest_api_init', [ self::class, 'register_rest' ] );
        add_action( 'admin_notices', [ self::class, 'notice' ] );
        add_action( 'admin_post_newspack_bedrock_pack_skip', [ self::class, 'handle_skip' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            'Newspack plugin pack',
            'Newspack pack',
            'activate_plugins',
            'newspack-plugin-pack',
            [ self::class, 'render_page' ],
            'dashicons-screenoptions',
            59
        );
    }

    public static function enqueue( string $hook ): void {
        if ( 'toplevel_page_newspack-plugin-pack' !== $hook ) {
            return;
        }
        $url = plugin_dir_url( NEWSPACK_BEDROCK_PACK_FILE );
        wp_enqueue_style(
            'newspack-bedrock-pack',
            $url . 'assets/admin.css',
            [],
            '1.1.0'
        );
        wp_enqueue_script(
            'newspack-bedrock-pack',
            $url . 'assets/admin.js',
            [],
            '1.1.0',
            true
        );
        wp_localize_script(
            'newspack-bedrock-pack',
            'newspackBedrockPack',
            [
                'rest'  => esc_url_raw( rest_url( 'newspack-bedrock-pack/v1/install' ) ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
            ]
        );
    }

    public static function register_rest(): void {
        register_rest_route(
            'newspack-bedrock-pack/v1',
            '/install',
            [
                'methods'             => 'POST',
                'callback'            => [ self::class, 'rest_install' ],
                'permission_callback' => static function () {
                    return current_user_can( 'activate_plugins' );
                },
            ]
        );
    }

    public static function rest_install( \WP_REST_Request $request ) {
        $slugs = $request->get_param( 'slugs' );
        if ( ! is_array( $slugs ) ) {
            return new \WP_Error( 'invalid', 'slugs must be an array.', [ 'status' => 400 ] );
        }
        $slugs  = array_values( array_filter( array_map( 'sanitize_key', $slugs ) ) );
        $raw    = Installer::install_slugs( $slugs );
        $out    = [];
        $failed = false;
        foreach ( $raw as $slug => $result ) {
            if ( is_wp_error( $result ) ) {
                $failed         = true;
                $out[ $slug ] = [ 'ok' => false, 'error' => $result->get_error_message() ];
            } else {
                $out[ $slug ] = [ 'ok' => true ];
            }
        }
        if ( ! $failed ) {
            delete_option( Plugin::OPTION_PROMPT );
            update_option( Plugin::OPTION_DONE, '1', false );
        }
        return rest_ensure_response(
            [
                'results' => $out,
                'status'  => self::status_payload(),
            ]
        );
    }

    public static function handle_skip(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'newspack-bedrock-pack' ) );
        }
        check_admin_referer( 'newspack_bedrock_pack_skip' );
        delete_option( Plugin::OPTION_PROMPT );
        update_option( Plugin::OPTION_DONE, '1', false );
        wp_safe_redirect( admin_url( 'plugins.php' ) );
        exit;
    }

    public static function notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'newspack-plugin-pack' === $page || get_option( Plugin::OPTION_DONE ) ) {
            return;
        }
        $pending = 0;
        foreach ( Catalog::slugs() as $slug ) {
            if ( 'active' !== Installer::status( $slug ) ) {
                $pending++;
            }
        }
        if ( $pending < 1 ) {
            return;
        }
        echo '<div class="notice notice-info"><p>';
        echo esc_html( sprintf(
            /* translators: %d is a plugin count */
            _n(
                'Newspack Bedrock includes %d companion plugin. Choose which ones to install.',
                'Newspack Bedrock includes %d companion plugins. Choose which ones to install.',
                $pending,
                'newspack-bedrock-pack'
            ),
            $pending
        ) );
        echo ' <a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=newspack-plugin-pack' ) ) . '">';
        echo esc_html__( 'Review plugin pack', 'newspack-bedrock-pack' );
        echo '</a></p></div>';
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to manage plugins.', 'newspack-bedrock-pack' ) );
        }
        delete_option( Plugin::OPTION_PROMPT );
        $rows = self::status_payload();
        $skip = wp_nonce_url( admin_url( 'admin-post.php?action=newspack_bedrock_pack_skip' ), 'newspack_bedrock_pack_skip' );
        echo '<div class="wrap nbp-wrap">';
        echo '<h1>' . esc_html__( 'Install Newspack companion plugins', 'newspack-bedrock-pack' ) . '</h1>';
        echo '<p>' . esc_html__( 'Newspack is the base plugin. Everything else in this Bedrock repo is optional. Recommended plugins are checked. Assets load with plugin_dir_url() so they work with Sage and Bedrock (/app, not /wp-content).', 'newspack-bedrock-pack' ) . '</p>';
        echo '<form id="nbp-form">';
        echo '<table class="widefat striped nbp-table"><thead><tr>';
        echo '<td class="check-column"><input type="checkbox" id="nbp-all" /></td>';
        echo '<th>' . esc_html__( 'Plugin', 'newspack-bedrock-pack' ) . '</th>';
        echo '<th>' . esc_html__( 'Status', 'newspack-bedrock-pack' ) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ( $rows as $row ) {
            $checked = ( $row['recommended'] && 'active' !== $row['status'] ) ? ' checked' : '';
            $disabled = ( 'active' === $row['status'] ) ? ' disabled' : '';
            echo '<tr>';
            echo '<th class="check-column"><input type="checkbox" class="nbp-slug" name="slugs[]" value="' . esc_attr( $row['slug'] ) . '"' . $checked . $disabled . ' /></th>';
            echo '<td><strong>' . esc_html( $row['name'] ) . '</strong>';
            if ( $row['recommended'] ) {
                echo ' <span class="nbp-pill">' . esc_html__( 'Recommended', 'newspack-bedrock-pack' ) . '</span>';
            }
            echo '<p class="description">' . esc_html( $row['description'] ) . '</p></td>';
            echo '<td class="nbp-status" data-slug="' . esc_attr( $row['slug'] ) . '">' . esc_html( $row['status_label'] ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p class="nbp-actions">';
        echo '<button type="submit" class="button button-primary button-hero">' . esc_html__( 'Install selected plugins', 'newspack-bedrock-pack' ) . '</button> ';
        echo '<a class="button button-hero" href="' . esc_url( $skip ) . '">' . esc_html__( 'Skip for now', 'newspack-bedrock-pack' ) . '</a>';
        echo '</p><p id="nbp-message" class="nbp-message" hidden></p></form></div>';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function status_payload(): array {
        $rows = [];
        $labels = [
            'active'   => __( 'Active', 'newspack-bedrock-pack' ),
            'inactive' => __( 'Installed, not active', 'newspack-bedrock-pack' ),
            'bundled'  => __( 'Packaged, needs install', 'newspack-bedrock-pack' ),
            'missing'  => __( 'Not packaged', 'newspack-bedrock-pack' ),
        ];
        foreach ( Catalog::plugins() as $slug => $info ) {
            $status = Installer::status( $slug );
            $rows[] = [
                'slug'         => $slug,
                'name'         => $info['Name'],
                'description'  => $info['Description'],
                'recommended'  => ! empty( $info['recommended'] ),
                'status'       => $status,
                'status_label' => $labels[ $status ] ?? $status,
            ];
        }
        return $rows;
    }
}
