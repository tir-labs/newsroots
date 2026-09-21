<?php

test('plugins use plugin_dir_url for Sage compatibility instead of WP_CONTENT_URL', function () {
    $root = dirname(__DIR__) . '/packages';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    $hardcoded_paths = [];

    // Third-party plugins we don't control - skip vendor dirs and known plugins
    $skip_patterns = [
        '/vendor/',
        '/prevent-direct-access/',
        '/fifu-premium/',
        '/flux-media-optimizer/',
        '/onesignal-free-web-push-notifications/',
        '/co-authors-plus/',
    ];

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $skip = false;
        foreach ($skip_patterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            // Skip comment lines
            if (strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            if (strpos($line, 'WP_CONTENT_URL') !== false || strpos($line, '/wp-content/plugins') !== false) {
                $hardcoded_paths[] = $path . ': ' . trim($line);
                break;
            }
        }
    }
    expect($hardcoded_paths)->toBeEmpty(
        'Hardcoded /wp-content/ paths in code break Sage/Bedrock. Use plugin_dir_url() instead. Found: ' . implode(', ', array_slice($hardcoded_paths, 0, 5))
    );
});

test('plugin packages are compatible with Roots Acorn and Laravel components', function () {
    $root = dirname(__DIR__) . '/packages';
    $composer_files = glob($root . '/*/composer.json');

    foreach ($composer_files as $file) {
        $json = json_decode(file_get_contents($file), true);
        if (isset($json['require']['illuminate/database']) || isset($json['require']['roots/acorn'])) {
            expect($json['require'])->toHaveKey('roots/acorn', 'Plugins using Laravel components must require roots/acorn');
        }
    }

    expect(file_exists(dirname(__DIR__) . '/web/app/mu-plugins/acorn-bootloader.php'))->toBeTrue();
});
