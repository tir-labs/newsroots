<?php
/**
 * Activate bundled plugins without downloading from the network.
 *
 * @package Newspack_Bedrock_Pack
 */

namespace Newspack_Bedrock_Pack;

defined( 'ABSPATH' ) || exit;

class Installer {

    public static function status( string $slug ): string {
        $installed = self::installed_map();
        if ( ! isset( $installed[ $slug ] ) ) {
            return is_dir( Catalog::packages_dir() . '/' . $slug ) ? 'bundled' : 'missing';
        }
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $installed[ $slug ] ) ? 'active' : 'inactive';
    }

    /**
     * @return array<string, string> slug => plugin file relative to WP_PLUGIN_DIR
     */
    public static function installed_map(): array {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $map = [];
        foreach ( array_keys( get_plugins() ) as $file ) {
            $slug         = dirname( $file );
            $map[ $slug ] = $file;
            if ( '.' === $slug ) {
                $map[ basename( $file, '.php' ) ] = $file;
            }
        }
        return $map;
    }

    /**
     * @param list<string> $slugs
     * @return array<string, true|\WP_Error>
     */
    public static function install_slugs( array $slugs ): array {
        $results = [];
        foreach ( $slugs as $slug ) {
            $slug = sanitize_key( $slug );
            if ( ! isset( Catalog::plugins()[ $slug ] ) ) {
                $results[ $slug ] = new \WP_Error( 'unknown_plugin', 'Unknown bundled plugin.' );
                continue;
            }
            $results[ $slug ] = self::install_one( $slug );
        }
        return $results;
    }

    public static function install_one( string $slug ) {
        $placed = self::ensure_present( $slug );
        if ( is_wp_error( $placed ) ) {
            return $placed;
        }

        $installed = self::installed_map();
        if ( ! isset( $installed[ $slug ] ) ) {
            wp_clean_plugins_cache( true );
            $installed = self::installed_map();
        }
        if ( ! isset( $installed[ $slug ] ) ) {
            return new \WP_Error(
                'not_on_disk',
                sprintf(
                    'Plugin %s is packaged in this repo but is not in web/app/plugins. Run composer require for that package, then activate it here.',
                    $slug
                )
            );
        }

        if ( ! function_exists( 'activate_plugin' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $result = activate_plugin( $installed[ $slug ] );
        return is_wp_error( $result ) ? $result : true;
    }

    /**
     * If Composer did not symlink the package yet, copy it when file mods are allowed.
     */
    public static function ensure_present( string $slug ) {
        $dest = WP_PLUGIN_DIR . '/' . $slug;
        if ( is_dir( $dest ) ) {
            return true;
        }

        $src = Catalog::packages_dir() . '/' . $slug;
        if ( ! is_dir( $src ) ) {
            return new \WP_Error( 'not_packaged', 'This plugin is not in packages/.' );
        }

        if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
            return new \WP_Error(
                'composer_required',
                sprintf(
                    'Bedrock has DISALLOW_FILE_MODS on. Add %s to composer.json and run composer update.',
                    $slug
                )
            );
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        global $wp_filesystem;
        if ( ! $wp_filesystem ) {
            return new \WP_Error( 'fs', 'Could not access the filesystem.' );
        }
        $copied = copy_dir( $src, $dest );
        return $copied ? true : new \WP_Error( 'copy_failed', 'Could not copy the bundled plugin into plugins/.' );
    }
}
