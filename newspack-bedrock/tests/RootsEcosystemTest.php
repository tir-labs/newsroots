<?php

test('acorn bootloader loads routes and uses wordpress routing', function () {
    $boot = file_get_contents(dirname(__DIR__) . '/web/app/mu-plugins/acorn-bootloader.php');
    expect($boot)->toContain('withRouting');
    expect($boot)->toContain('wordpress: true');
    expect($boot)->toContain("base_path('routes/web.php')");
});

test('routes/web.php exists for acorn routing', function () {
    expect(file_exists(dirname(__DIR__) . '/routes/web.php'))->toBeTrue();
});

test('config/app.php exists for acorn error handling', function () {
    expect(file_exists(dirname(__DIR__) . '/config/app.php'))->toBeTrue();
    $config = require dirname(__DIR__) . '/config/app.php';
    expect($config)->toHaveKey('debug');
});

test('storage directory exists for acorn logging', function () {
    expect(is_dir(dirname(__DIR__) . '/storage'))->toBeTrue();
    expect(is_dir(dirname(__DIR__) . '/storage/logs'))->toBeTrue();
});

test('livewire is required in composer', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);
    expect($composer['require'])->toHaveKey('livewire/livewire');
});

test('app_key placeholder exists in env example', function () {
    $env = file_get_contents(dirname(__DIR__) . '/.env.example');
    expect($env)->toContain('APP_KEY');
});

test('eloquent models follow acorn conventions', function () {
    $models = [
        dirname(__DIR__) . '/packages/newpack-discord-bot-api/src/Models/Post.php',
        dirname(__DIR__) . '/packages/newpack-discord-bot-api/src/Models/User.php',
        dirname(__DIR__) . '/packages/newpack-discord-bot-api/src/Models/PostMeta.php',
        dirname(__DIR__) . '/packages/newpack-discord-bot-api/src/Models/Term.php',
    ];
    foreach ($models as $model) {
        expect(file_exists($model))->toBeTrue();
        $content = file_get_contents($model);
        expect($content)->toContain('Illuminate\\Database\\Eloquent\\Model');
        expect($content)->toContain('$timestamps = false');
    }
    // Post and User must use uppercase ID primary key
    $post = file_get_contents($models[0]);
    expect($post)->toContain("\$primaryKey = 'ID'");
    $user = file_get_contents($models[1]);
    expect($user)->toContain("\$primaryKey = 'ID'");
});

test('discord bot api uses eloquent cache not raw transients', function () {
    $api = file_get_contents(dirname(__DIR__) . '/packages/newpack-discord-bot-api/newpack-discord-bot-api.php');
    expect($api)->toContain('\\Illuminate\\Support\\Facades\\Cache');
    expect($api)->toContain('\\App\\Models\\Post');
    expect($api)->not->toContain('new WP_Query');
});

test('hardcoded wp-content paths are patched for bedrock', function () {
    $util = file_get_contents(dirname(__DIR__) . '/packages/newspack-plugin/includes/util.php');
    // Should use Bedrock /app/plugins/ path, not /wp-content/plugins/
    expect($util)->not->toContain('/wp-content/plugins/newspack-newsletters/');
});
