<?php

test('bedrock required files exist', function () {
    $root = dirname(__DIR__);
    expect(file_exists($root . '/web/wp-config.php'))->toBeTrue();
    expect(file_exists($root . '/web/index.php'))->toBeTrue();
    expect(file_exists($root . '/config/application.php'))->toBeTrue();
    expect(file_exists($root . '/config/environments/development.php'))->toBeTrue();
    expect(file_exists($root . '/.env.example'))->toBeTrue();
    expect(file_exists($root . '/web/app/object-cache.php'))->toBeTrue();
    expect(file_exists($root . '/web/app/mu-plugins/acorn-bootloader.php'))->toBeTrue();
    expect(file_exists($root . '/web/app/mu-plugins/bedrock-autoloader.php'))->toBeTrue();
    expect(file_exists($root . '/wp-cli.yml'))->toBeTrue();
    expect(file_exists($root . '/phpunit.xml.dist'))->toBeTrue();
});

test('composer follows official Bedrock + WP Packages standards', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);
    expect($composer['require']['php'])->toBe('>=8.3');
    expect($composer['require']['roots/wordpress'])->toBe('7.1.1');
    expect($composer['extra']['wordpress-install-dir'])->toBe('web/wp');
    expect($composer['extra']['installer-paths'])->toHaveKey('web/app/plugins/{$name}/');
    expect($composer['extra']['installer-paths'])->not->toHaveKey('web/app/plugins/${name}/');
    $urls = array_column($composer['repositories'], 'url');
    expect($urls)->toContain('https://repo.wp-packages.org');
    expect($urls)->toContain('packages/*');
    expect($composer['require'])->toHaveKey('roots/acorn');
    expect($composer['require'])->toHaveKey('roots/bedrock-autoloader');
    expect($composer['require'])->toHaveKey('wp-plugin/co-authors-plus');
    expect($composer['require'])->toHaveKey('wp-theme/twentytwentyfive');
    expect($composer['require'])->toHaveKey('automattic/newspack-blocks');
    expect($composer['scripts']['post-autoload-dump'])->toContain('Roots\\Acorn\\ComposerScripts::postAutoloadDump');
});

test('plugin packages are wordpress-plugin composer packages under packages/', function () {
    $root = dirname(__DIR__);
    $slugs = [
        'newsroom-speed-cache',
        'newspack-local-esps',
        'newsroom-image-downloader',
        'newpack-discord-bot-api',
        'newspack-plugin',
        'newspack-blocks',
        'newspack-newsletters',
        'flux-media-optimizer',
    ];
    foreach ($slugs as $plugin) {
        $dir = $root . '/packages/' . $plugin;
        expect(file_exists($dir . '/composer.json'))->toBeTrue();
        $json = json_decode(file_get_contents($dir . '/composer.json'), true);
        expect($json['type'])->toBe('wordpress-plugin');
        expect($json['require']['php'])->toBe('>=8.3');
        expect($json['extra']['installer-name'])->toBe($plugin);
        expect($json['require'])->toHaveKey('composer/installers');
    }
});

test('gitignore treats Composer-installed plugins as unmanaged source', function () {
    $gitignore = file_get_contents(dirname(__DIR__) . '/.gitignore');
    expect($gitignore)->toContain('web/app/plugins/*');
    expect($gitignore)->toContain('!web/app/plugins/.gitkeep');
    expect($gitignore)->toContain('/vendor');
    expect($gitignore)->toContain('web/wp');
});

test('env example is memcached-only', function () {
    $env = file_get_contents(dirname(__DIR__) . '/.env.example');
    expect($env)->toContain('MEMCACHED_HOST');
    expect($env)->toContain('WP_SITEURL');
    expect($env)->not->toContain('REDIS_HOST');
});

test('wp-config does not bootstrap wordpress itself besides requiring application.php', function () {
    $src = file_get_contents(dirname(__DIR__) . '/web/wp-config.php');
    expect($src)->toContain("dirname(__DIR__) . '/vendor/autoload.php'");
    expect($src)->toContain("dirname(__DIR__) . '/config/application.php'");
    expect($src)->toContain('wp-settings.php');
});

test('newspack blocks ship standard gutenberg block.json files', function () {
    $root = dirname(__DIR__) . '/packages/newspack-blocks';
    $nested = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === 'block.json') {
            $nested[] = $file->getPathname();
        }
    }
    expect($nested)->not->toBeEmpty();
    $names = [];
    foreach ($nested as $file) {
        $sample = json_decode(file_get_contents($file), true);
        expect($sample)->toHaveKey('name');
        $names[] = $sample['name'];
    }
    expect($names)->toContain('newspack-blocks/checkout-button');
});
