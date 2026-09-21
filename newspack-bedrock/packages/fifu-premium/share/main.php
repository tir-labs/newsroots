<?php

if (!defined('FIFU_FACEBOOK_ID')) {
    define('FIFU_FACEBOOK_ID', 1);
}

if (!defined('FIFU_INSTAGRAM_ID')) {
    define('FIFU_INSTAGRAM_ID', 2);
}

if (!defined('FIFU_X_ID')) {
    define('FIFU_X_ID', 3);
}

if (!function_exists('fifu_as_provider_to_id')) {

    function fifu_as_provider_to_id($provider) {
        switch (strtolower((string) $provider)) {
            case 'facebook':
                return FIFU_FACEBOOK_ID;
            case 'instagram':
                return FIFU_INSTAGRAM_ID;
            case 'x':
            case 'twitter':
                return FIFU_X_ID;
            default:
                return 0;
        }
    }

}

if (!function_exists('fifu_as_get_post_slug')) {

    function fifu_as_get_post_slug($post_id) {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return '';
        }
        $slug = (string) get_post_field('post_name', $post_id);
        if (function_exists('sanitize_title')) {
            $slug = sanitize_title($slug);
        } else {
            $slug = trim($slug);
        }
        if ($slug === '') {
            $slug = 'post-' . $post_id;
        }
        return $slug;
    }

}

if (!function_exists('fifu_as_parse_shared_ids')) {

    function fifu_as_parse_shared_ids($value) {
        $ids = [];
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!is_numeric($item)) {
                    continue;
                }
                $id = (int) $item;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
            return array_values($ids);
        }
        $parts = preg_split('/[^0-9]+/', (string) $value);
        if (!is_array($parts)) {
            return [];
        }
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $id = (int) $part;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

}

if (!function_exists('fifu_as_get_shared_on_state')) {

    function fifu_as_get_shared_on_state($post_id) {
        $post_id = (int) $post_id;
        $current_slug = fifu_as_get_post_slug($post_id);
        if ($post_id <= 0) {
            return ['slug' => $current_slug, 'ids' => []];
        }

        $raw = get_post_meta($post_id, 'fifu_shared_on', true);
        if ($raw === '' || $raw === null) {
            return ['slug' => $current_slug, 'ids' => []];
        }

        $stored_slug = '';
        $ids = [];

        if (is_array($raw)) {
            $stored_slug = isset($raw['slug']) ? (string) $raw['slug'] : '';
            $ids = fifu_as_parse_shared_ids(isset($raw['ids']) ? $raw['ids'] : []);
        } else {
            $raw = trim((string) $raw);
            if ($raw !== '' && strpos($raw, ':') !== false) {
                list($maybe_slug, $list) = explode(':', $raw, 2);
                $stored_slug = (string) $maybe_slug;
                $ids = fifu_as_parse_shared_ids($list);
            } else {
                $ids = fifu_as_parse_shared_ids($raw);
            }
        }

        if (function_exists('sanitize_title')) {
            $stored_slug = sanitize_title($stored_slug);
        } else {
            $stored_slug = trim($stored_slug);
        }

        if ($stored_slug !== '' && $current_slug !== '' && $stored_slug !== $current_slug) {
            delete_post_meta($post_id, 'fifu_shared_on');
            return ['slug' => $current_slug, 'ids' => []];
        }

        if ($stored_slug === '') {
            $stored_slug = $current_slug;
        }

        return [
            'slug' => $stored_slug,
            'ids' => array_values(array_unique(array_map('intval', $ids))),
        ];
    }

}

if (!function_exists('fifu_as_store_shared_on_state')) {

    function fifu_as_store_shared_on_state($post_id, array $state) {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return false;
        }

        $slug = isset($state['slug']) ? (string) $state['slug'] : '';
        $ids = isset($state['ids']) ? $state['ids'] : [];

        if (function_exists('sanitize_title')) {
            $slug = sanitize_title($slug);
        } else {
            $slug = trim($slug);
        }
        if ($slug === '') {
            $slug = fifu_as_get_post_slug($post_id);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($value) {
                            return $value > 0;
                        })));

        if ($ids === []) {
            delete_post_meta($post_id, 'fifu_shared_on');
            return true;
        }

        update_post_meta($post_id, 'fifu_shared_on', [
            'slug' => $slug,
            'ids' => $ids,
        ]);
        return true;
    }

}

if (!function_exists('fifu_as_get_shared_on_ids')) {

    function fifu_as_get_shared_on_ids($post_id) {
        $state = fifu_as_get_shared_on_state($post_id);
        return $state['ids'];
    }

}

if (!function_exists('fifu_as_has_shared_on_provider')) {

    function fifu_as_has_shared_on_provider($post_id, $provider) {
        $provider_id = fifu_as_provider_to_id($provider);
        if ($provider_id <= 0) {
            return false;
        }

        $ids = fifu_as_get_shared_on_ids($post_id);
        return in_array($provider_id, $ids, true);
    }

}

if (!function_exists('fifu_as_mark_shared_on_provider')) {

    function fifu_as_mark_shared_on_provider($post_id, $provider) {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return false;
        }

        $provider_id = fifu_as_provider_to_id($provider);
        if ($provider_id <= 0) {
            return false;
        }

        $state = fifu_as_get_shared_on_state($post_id);
        if (in_array($provider_id, $state['ids'], true)) {
            return true;
        }

        $state['ids'][] = $provider_id;
        return fifu_as_store_shared_on_state($post_id, $state);
    }

}

if (!function_exists('fifu_as_log_share_attempt')) {

    function fifu_as_log_share_attempt($provider, $post_id, $link = '', $status = 'attempt', array $extra = []) {
        return;
    }

}

if (!function_exists('fifu_as_normalize_log_value')) {

    function fifu_as_normalize_log_value($value, $depth = 0) {
        if ($depth > 6) {
            return '[depth-exceeded]';
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return fifu_as_normalize_log_value($decoded, $depth + 1);
                }
            }
            return $value;
        }

        if (is_null($value) || is_bool($value) || is_numeric($value)) {
            return $value;
        }

        if ($value instanceof WP_Error) {
            $data = $value->get_error_data();
            return [
                'code' => $value->get_error_code(),
                'message' => $value->get_error_message(),
                'data' => fifu_as_normalize_log_value($data, $depth + 1),
            ];
        }

        if ($value instanceof JsonSerializable) {
            return fifu_as_normalize_log_value($value->jsonSerialize(), $depth + 1);
        }

        if ($value instanceof Traversable) {
            $normalized = [];
            foreach ($value as $k => $v) {
                $normalized[$k] = fifu_as_normalize_log_value($v, $depth + 1);
            }
            return $normalized;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $k => $v) {
                $normalized[$k] = fifu_as_normalize_log_value($v, $depth + 1);
            }
            return $normalized;
        }

        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                return fifu_as_normalize_log_value($value->toArray(), $depth + 1);
            }
            if (method_exists($value, 'to_array')) {
                return fifu_as_normalize_log_value($value->to_array(), $depth + 1);
            }
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            $vars = get_object_vars($value);
            if (!empty($vars)) {
                return fifu_as_normalize_log_value($vars, $depth + 1);
            }
            $encoded = function_exists('wp_json_encode') ? wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if (is_string($encoded)) {
                $decoded = json_decode($encoded, true);
                if (is_array($decoded)) {
                    return fifu_as_normalize_log_value($decoded, $depth + 1);
                }
                return $encoded;
            }
            return sprintf('[object %s]', get_class($value));
        }

        return function_exists('wp_json_encode') ? wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

}

if (!function_exists('fifu_as_assess_worker_response')) {

    function fifu_as_assess_worker_response($provider, $worker) {
        $normalized = fifu_as_normalize_log_value($worker);

        if (!is_array($normalized)) {
            $error = new WP_Error(
                    'fifu_as_publish_invalid',
                    sprintf('Unexpected response from %s worker.', ucfirst($provider)),
                    [
                'status' => 502,
                'provider' => $provider,
                'worker_response' => $normalized,
                    ]
            );
            return [
                'normalized' => $normalized,
                'error' => $error,
            ];
        }

        $status_flag = '';
        if (isset($normalized['status']) && is_string($normalized['status'])) {
            $status_flag = strtolower($normalized['status']);
        }

        $publish = [];
        if (isset($normalized['publish']) && is_array($normalized['publish'])) {
            $publish = $normalized['publish'];
        }

        $failed = false;
        if (in_array($status_flag, ['error', 'fail', 'failed'], true)) {
            $failed = true;
        }
        if (!$failed && isset($normalized['error_code']) && $normalized['error_code']) {
            $failed = true;
        }
        if (!$failed && $publish) {
            if (array_key_exists('ok', $publish) && $publish['ok'] === false) {
                $failed = true;
            }
            if (!$failed && isset($publish['status']) && is_numeric($publish['status']) && (int) $publish['status'] >= 400) {
                $failed = true;
            }
            if (!$failed && isset($publish['error_code']) && $publish['error_code']) {
                $failed = true;
            }
            if (!$failed && isset($publish['stage']) && in_array(strtolower((string) $publish['stage']), ['error', 'fail', 'failed'], true)) {
                $failed = true;
            }
        }

        if (!$failed) {
            return [
                'normalized' => $normalized,
                'error' => null,
            ];
        }

        $message = fifu_as_extract_worker_error_message($normalized);
        if ($message === '') {
            $message = sprintf('Failed to share on %s.', ucfirst($provider));
        }

        $status_code = 409;
        if ($publish && isset($publish['status']) && is_numeric($publish['status'])) {
            $status_code = max(400, (int) $publish['status']);
        } elseif (isset($normalized['status_code']) && is_numeric($normalized['status_code'])) {
            $status_code = max(400, (int) $normalized['status_code']);
        }

        $error = new WP_Error(
                'fifu_as_publish_failed',
                $message,
                [
            'status' => $status_code,
            'provider' => $provider,
            'worker_response' => $normalized,
                ]
        );

        return [
            'normalized' => $normalized,
            'error' => $error,
        ];
    }

}

if (!function_exists('fifu_as_extract_worker_error_message')) {

    function fifu_as_extract_worker_error_message(array $data) {
        $queue = [$data];
        $message_keys = [
            'error_user_msg',
            'error_message',
            'message',
            'detail',
            'description',
            'reason',
            'error',
            'body',
        ];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if (!is_array($current)) {
                continue;
            }

            foreach ($message_keys as $key) {
                if (!array_key_exists($key, $current)) {
                    continue;
                }
                $value = $current[$key];
                if (is_string($value)) {
                    $trimmed = trim($value);
                    if ($trimmed !== '') {
                        return $trimmed;
                    }
                    continue;
                }
                if (is_array($value)) {
                    $queue[] = $value;
                }
            }

            foreach ($current as $value) {
                if (is_array($value)) {
                    $queue[] = $value;
                }
            }
        }

        return '';
    }

}

/**
 * FIFU Auto-Share — REST routes (provider-agnostic dispatcher)
 * Namespace: fifu-premium/v2
 *
 * Endpoints used by the minimal client (test.js):
 *  - POST /fifu-premium/v2/social/{provider}/auth/start
 *  - POST /fifu-premium/v2/social/{provider}/auth/finalize
 *  - GET  /fifu-premium/v2/social/{provider}/status
 *
 * Extend switch-cases to add Instagram/Threads later.
 */
// NOTE: No require/include; loader ensures helpers/providers are loaded.

add_action('rest_api_init', function () {
    $ns = 'fifu-premium/v2';

    /**
     * POST /social/{provider}/auth/start
     * Called by: client JS when the user initiates connection.
     * Why: fetch OAuth URL from Worker to open the provider login in a new tab.
     */
    register_rest_route($ns, '/social/(?P<provider>[a-z0-9_-]+)/auth/start', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            $provider = sanitize_key($req['provider']);
            switch ($provider) {
                case 'facebook':
                    return fifu_as_facebook_auth_start($req);
                case 'instagram':
                    return fifu_as_instagram_auth_start($req);
                case 'x':
                case 'twitter':
                    return fifu_as_x_auth_start($req);
                // case 'threads':   return fifu_as_threads_auth_start($req);
                default:
                    return new WP_Error('fifu_as_unknown_provider', 'Provider not supported', ['status' => 400]);
            }
        },
    ]);

    /**
     * POST /social/{provider}/auth/finalize
     * Called by: client JS after the auth tab posts tempToken via postMessage.
     * Why: exchange tempToken with Worker; persist tokens/account/pages.
     */
    register_rest_route($ns, '/social/(?P<provider>[a-z0-9_-]+)/auth/finalize', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            $provider = sanitize_key($req['provider']);
            switch ($provider) {
                case 'facebook':
                    return fifu_as_facebook_auth_finalize($req);
                case 'instagram':
                    return fifu_as_instagram_auth_finalize($req);
                case 'x':
                case 'twitter':
                    return fifu_as_x_auth_finalize($req);
                // case 'threads':   return fifu_as_threads_auth_finalize($req);
                default:
                    return new WP_Error('fifu_as_unknown_provider', 'Provider not supported', ['status' => 400]);
            }
        },
    ]);

    /**
     * GET /social/{provider}/status
     * Called by: client JS on load and after finalize.
     * Why: display current connection/account/page snapshot.
     */
    register_rest_route($ns, '/social/(?P<provider>[a-z0-9_-]+)/status', [
        'methods' => 'GET',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            $provider = sanitize_key($req['provider']);
            switch ($provider) {
                case 'facebook':
                    return fifu_as_facebook_status($req);
                case 'instagram':
                    return fifu_as_instagram_status($req);
                case 'x':
                case 'twitter':
                    return fifu_as_x_status($req);
                // case 'threads':   return fifu_as_threads_status($req);
                default:
                    return new WP_Error('fifu_as_unknown_provider', 'Provider not supported', ['status' => 400]);
            }
        },
    ]);

    /**
     * POST /social/{provider}/disconnect
     * Called by: client JS when user clicks disconnect.
     * Why: clear stored snapshot and revoke tokens (provider-specific handler).
     */
    register_rest_route($ns, '/social/(?P<provider>[a-z0-9_-]+)/disconnect', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            $provider = sanitize_key($req['provider']);
            switch ($provider) {
                case 'facebook':
                    return fifu_as_facebook_disconnect($req);
                case 'instagram':
                    return fifu_as_instagram_disconnect($req);
                case 'x':
                case 'twitter':
                    return fifu_as_x_disconnect($req);
                // case 'threads':   return fifu_as_threads_disconnect($req);
                default:
                    return new WP_Error('fifu_as_unknown_provider', 'Provider not supported', ['status' => 400]);
            }
        },
    ]);

    /**
     * POST /share/facebook
     * Called by: admin/test harness share form.
     * Why: send stored credentials + post payload to Worker to publish.
     */
    register_rest_route($ns, '/share/facebook', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            return fifu_as_facebook_share($req);
        },
    ]);

    register_rest_route($ns, '/share/instagram', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            return fifu_as_instagram_share($req);
        },
    ]);

    register_rest_route($ns, '/share/x', [
        'methods' => 'POST',
        'permission_callback' => 'fifu_as_can_manage',
        'callback' => function (WP_REST_Request $req) {
            return fifu_as_x_share($req);
        },
    ]);
});

function fifu_as_post_has_shareable_media($post_id) {
    if (has_post_thumbnail($post_id)) {
        return true;
    }

    if (function_exists('fifu_main_image_url')) {
        $main_url = fifu_main_image_url($post_id, true);
        if (is_string($main_url) && trim($main_url) !== '') {
            return true;
        }
    }

    if (function_exists('fifu_get_image_urls')) {
        $urls = fifu_get_image_urls($post_id);
        if (is_array($urls)) {
            foreach ($urls as $url) {
                if (is_string($url) && trim($url) !== '') {
                    return true;
                }
            }
        }
    }

    $meta_url = get_post_meta($post_id, 'fifu_image_url', true);
    if (is_string($meta_url) && trim($meta_url) !== '') {
        return true;
    }

    return false;
}

/**
 * Auto-share newly published posts that have a featured image.
 */
add_action('save_post', 'fifu_as_auto_share_on_save_post', 999, 3);
add_action('fifu_as_run_auto_share', 'fifu_as_auto_share_execute', 10, 1);

function fifu_as_auto_share_on_save_post($post_id, $post, $update) {
    $post_id = (int) $post_id;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!($post instanceof WP_Post)) {
        $post = get_post($post_id);
        if (!($post instanceof WP_Post)) {
            return;
        }
    }

    if ($post->post_type !== 'post') {
        return;
    }

    if ($post->post_status !== 'publish') {
        return;
    }

    $prepared_post = fifu_as_get_post_ready_for_auto_share($post_id, $post);
    if (!($prepared_post instanceof WP_Post)) {
        return;
    }

    static $processed = [];
    if (isset($processed[$post_id])) {
        return;
    }
    $processed[$post_id] = true;

    if (!wp_next_scheduled('fifu_as_run_auto_share', [$post_id])) {
        // run shortly after save to avoid blocking the editor request
        wp_schedule_single_event(time() + 5, 'fifu_as_run_auto_share', [$post_id]);
    }
}

function fifu_as_get_post_ready_for_auto_share($post_id, $post = null) {
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return null;
    }
    if (!($post instanceof WP_Post)) {
        $post = get_post($post_id);
        if (!($post instanceof WP_Post)) {
            return null;
        }
    }
    if ($post->post_type !== 'post') {
        return null;
    }
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return null;
    }
    $has_media = fifu_as_post_has_shareable_media($post_id);
    if (!$has_media) {
        return null;
    }
    if (!class_exists('WP_REST_Request')) {
        return null;
    }

    return $post;
}

function fifu_as_auto_share_execute($post_id) {
    if (fifu_is_off('fifu_auto_share')) {
        return;
    }

    $post = fifu_as_get_post_ready_for_auto_share($post_id);
    if (!($post instanceof WP_Post)) {
        return;
    }

    $post_id = (int) $post->ID;

    $original_user = get_current_user_id();
    $target_user = (int) $post->post_author;
    if ($target_user > 0 && $original_user !== $target_user) {
        wp_set_current_user($target_user);
    }

    $providers = [
        'facebook' => 'fifu_as_facebook_share',
        'instagram' => 'fifu_as_instagram_share',
        'x' => 'fifu_as_x_share',
    ];

    foreach ($providers as $provider => $callback) {
        if (!function_exists($callback)) {
            continue;
        }

        $connection = fifu_as_get_connection_details($provider);
        if (empty($connection['connected'])) {
            continue;
        }

        if (function_exists('fifu_as_has_shared_on_provider') && fifu_as_has_shared_on_provider($post_id, $provider)) {
            continue;
        }

        $request = new WP_REST_Request('POST', '/fifu-premium/v2/share/' . $provider);
        $request->set_body(wp_json_encode([
            'postId' => $post_id,
            'post_id' => $post_id,
        ]));
        $request->set_header('Content-Type', 'application/json');

        $result = call_user_func($callback, $request);
        if (is_wp_error($result)) {
            continue;
        }
    }

    if ($target_user > 0 && $original_user !== $target_user) {
        wp_set_current_user($original_user);
    }
}

