<?php
/**
 * Plugin Name: Acorn Bootloader
 * Description: Boots Roots Acorn so Laravel components work inside this Bedrock site.
 * Author: Roots / Postdated
 * License: MIT
 */

use Roots\Acorn\Application;

add_action(
    'after_setup_theme',
    static function (): void {
        if (!class_exists(Application::class)) {
            return;
        }

        if (function_exists('\\Roots\\app')) {
            try {
                \Roots\app();
                return;
            } catch (\Throwable $e) {
                unset($e);
            }
        }

        Application::configure()
            ->withExceptions(function ($exceptions) {
                // Log all exceptions to storage/logs/
            })
            ->withRouting(web: base_path('routes/web.php'), wordpress: true)
            ->boot();
    },
    0
);
