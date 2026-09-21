<?php
/**
 * Pack bootstrap.
 *
 * @package Newspack_Bedrock_Pack
 */

namespace Newspack_Bedrock_Pack;

defined( 'ABSPATH' ) || exit;

class Plugin {

    public const OPTION_PROMPT = 'newspack_bedrock_pack_needs_prompt';
    public const OPTION_DONE   = 'newspack_bedrock_pack_completed';

    public static function init(): void {
        Admin::init();
        add_filter( 'newspack_managed_plugins', [ self::class, 'register_managed_plugins' ] );
        add_action( 'activated_plugin', [ self::class, 'on_plugin_activated' ], 10, 1 );
        add_action( 'admin_init', [ self::class, 'maybe_redirect_prompt' ] );
    }

    /**
     * @param array<string, array<string, mixed>> $plugins
     * @return array<string, array<string, mixed>>
     */
    public static function register_managed_plugins( array $plugins ): array {
        foreach ( Catalog::plugins() as $slug => $info ) {
            if ( isset( $plugins[ $slug ] ) ) {
                $plugins[ $slug ]['Bundled'] = true;
                continue;
            }
            $plugins[ $slug ] = [
                'Name'        => $info['Name'],
                'Description' => $info['Description'],
                'Author'      => $info['Author'],
                'AuthorURI'   => 'https://github.com/Postdated/NewsRock',
                'PluginURI'   => 'https://github.com/Postdated/NewsRock',
                'Download'    => '',
                'Bundled'     => true,
                'Quiet'       => empty( $info['recommended'] ),
            ];
        }
        return $plugins;
    }

    public static function on_plugin_activated( string $plugin ): void {
        $base = basename( $plugin );
        $dir  = dirname( $plugin );
        if ( 'newspack.php' !== $base && 'newspack-plugin' !== $dir ) {
            return;
        }
        if ( get_option( self::OPTION_DONE ) ) {
            return;
        }
        update_option( self::OPTION_PROMPT, '1', false );
    }

    public static function maybe_redirect_prompt(): void {
        if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        if ( ! get_option( self::OPTION_PROMPT ) ) {
            return;
        }
        if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return;
        }
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( 'newspack-plugin-pack' === $page ) {
            return;
        }
        wp_safe_redirect( admin_url( 'admin.php?page=newspack-plugin-pack' ) );
        exit;
    }
}
