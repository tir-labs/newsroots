<?php
/**
 * NewsWoo Acorn Provider Loader.
 *
 * MU-plugin that registers NewsWoo's service provider with Acorn.
 * Install this file into web/app/mu-plugins/ alongside acorn-bootloader.php.
 *
 * @package NewsWoo
 */

add_filter('acorn/providers', function (array $providers): array {
    if (class_exists('NewsWoo\\Providers\\NewsWooServiceProvider')) {
        $providers[] = NewsWoo\\Providers\\NewsWooServiceProvider::class;
    }
    return $providers;
});

