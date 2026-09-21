<?php

/**
 * FIFU Auto-Share — Facebook-specific thin handlers
 * Worker does OAuth & data fetching. PHP proxies and stores.
 */
// NOTE: No require/include here; loader wires common helpers.

/**
 * Route: POST /fifu-premium/v2/social/facebook/auth/start
 * Called by: client JS (user clicks "Continue with Facebook").
 * Why: get OAuth authorization URL + state from the Worker to open a new tab.
 * Flow: JS → PHP(route) → Worker(/v2/oauth/facebook/start?site&partial_key) → JS opens authUrl in a tab.
 */
function fifu_as_facebook_auth_start(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);

    $payload = [
        'provider' => 'facebook',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'intent' => $body['intent'] ?? 'login',
        // Report plugin version to Worker
        'version' => fifu_as_version(),
    ];

    // Worker v2 endpoint; common helper appends ?site=…&partial_key=…
    $wk = fifu_as_worker_request('POST', '/v2/oauth/facebook/start', $payload);
    if (is_wp_error($wk))
        return $wk;

    return fifu_as_json([
        'authUrl' => isset($wk['authUrl']) ? esc_url_raw($wk['authUrl']) : '',
        'state' => isset($wk['state']) ? sanitize_text_field($wk['state']) : '',
        'popupOrigin' => isset($wk['popupOrigin']) ? esc_url_raw($wk['popupOrigin']) : '',
    ]);
}

/**
 * Route: POST /fifu-premium/v2/social/facebook/auth/finalize
 * Called by: client JS after the auth tab posts a message with tempToken.
 * Why: exchange tempToken+state with Worker for long-lived tokens + pages; store snapshot.
 * Flow: JS(tempToken) → PHP(route) → Worker(/v2/oauth/facebook/finalize?site&partial_key) → PHP stores → JS updates UI.
 */
function fifu_as_facebook_auth_finalize(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);
    $token = isset($body['tempToken']) ? sanitize_text_field($body['tempToken']) : '';
    $state = isset($body['state']) ? sanitize_text_field($body['state']) : '';

    if (!$token) {
        return new WP_Error('fifu_as_bad_request', 'Missing tempToken', ['status' => 400]);
    }

    $payload = [
        'provider' => 'facebook',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'tempToken' => $token,
        'state' => $state,
        'version' => fifu_as_version(),
    ];

    $wk = fifu_as_worker_request('POST', '/v2/oauth/facebook/finalize', $payload);
    if (is_wp_error($wk)) {
        return $wk;
    }

    $raw_pages = isset($wk['pages']) && is_array($wk['pages']) ? $wk['pages'] : [];
    $inferred_pages = [];
    if (empty($raw_pages)) {
        $inferred_pages = fifu_as_extract_pages_from_granular_scopes($wk['granular_scopes'] ?? []);
    }
    $pages_to_store = !empty($raw_pages) ? $raw_pages : $inferred_pages;

    $store = [
        'accountName' => isset($wk['accountName']) ? (string) $wk['accountName'] : '',
        'pages' => $pages_to_store,
        'token' => $wk['token'] ?? [],
    ];
    fifu_as_store_connection('facebook', $store);

    return fifu_as_json([
        'connected' => !empty($store['token']['access_token']),
        'accountName' => $store['accountName'],
        'pages' => $store['pages'],
    ]);
}

/**
 * Route: GET /fifu-premium/v2/social/facebook/status
 * Called by: client JS on load and after finalize.
 * Why: provide a safe connection snapshot to display in UI.
 * Flow: JS → PHP(route) → user meta snapshot → JS labels/status.
 */
function fifu_as_facebook_status(WP_REST_Request $req) {
    return fifu_as_json(fifu_as_get_status_snapshot('facebook'));
}

/**
 * Route: POST /fifu-premium/v2/social/facebook/disconnect
 * Called by: client JS when user clicks "Disconnect".
 * Why: clear stored snapshot so UI returns to not connected state.
 */
function fifu_as_facebook_disconnect(WP_REST_Request $req) {
    fifu_as_clear_connection('facebook');
    return fifu_as_json([
        'connected' => false,
        'accountName' => null,
        'pages' => [],
    ]);
}

/**
 * Route: POST /fifu-premium/v2/share/facebook
 * Called by: admin tools/test harness when user submits post ID to share.
 * Why: send post payload + stored credentials to the Worker so it can publish.
 */
function fifu_as_facebook_share(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);

    $post_id = isset($body['postId']) ? absint($body['postId']) : 0;
    if (!$post_id && isset($body['post_id'])) {
        $post_id = absint($body['post_id']);
    }
    if (!$post_id) {
        return new WP_Error('fifu_as_missing_post', 'Post ID is required', ['status' => 400]);
    }

    $post_payload = fifu_build_post_share_payload($post_id);
    if (!$post_payload) {
        return new WP_Error('fifu_as_post_not_found', 'Post not found', ['status' => 404]);
    }

    $connection = fifu_as_get_connection_details('facebook');
    if (empty($connection['connected'])) {
        return new WP_Error('fifu_as_facebook_not_connected', 'Facebook connection required', ['status' => 409]);
    }

    if (function_exists('fifu_as_has_shared_on_provider') && fifu_as_has_shared_on_provider($post_id, 'facebook')) {
        return fifu_as_json([
            'ok' => true,
            'already_shared' => true,
            'provider' => 'facebook',
        ]);
    }

    $requested_page_id = '';
    if (!empty($body['pageId'])) {
        $requested_page_id = sanitize_text_field((string) $body['pageId']);
    } elseif (!empty($body['page_id'])) {
        $requested_page_id = sanitize_text_field((string) $body['page_id']);
    }

    $token_pages_raw = isset($connection['token']['pages']) ? fifu_as_normalize_pages($connection['token']['pages']) : [];
    $token_page_raw = [];
    if (!empty($connection['token']['page']) && is_array($connection['token']['page'])) {
        $token_page_raw = fifu_as_normalize_pages([$connection['token']['page']]);
    }
    $selected_page = null;

    foreach ($connection['pages'] as $page) {
        if (!empty($requested_page_id) && isset($page['id']) && (string) $page['id'] === $requested_page_id) {
            $selected_page = $page;
            break;
        }
        if (!$selected_page && !empty($page['connected'])) {
            $selected_page = $page;
        }
    }
    if (!$selected_page && !empty($connection['pages'])) {
        $selected_page = $connection['pages'][0];
    }

    if (!$selected_page && !empty($token_pages_raw)) {
        if (!empty($token_pages_raw)) {
            $selected_page = $token_pages_raw[0];
        }
    }

    if (!$selected_page && !empty($token_page_raw)) {
        if (!empty($token_page_raw)) {
            $selected_page = $token_page_raw[0];
        }
    }

    $tokenPageId = '';
    foreach (['page_id', 'pageId', 'page', 'id'] as $tokenKey) {
        if (!empty($connection['token'][$tokenKey]) && !is_array($connection['token'][$tokenKey])) {
            $tokenPageId = sanitize_text_field((string) $connection['token'][$tokenKey]);
            break;
        }
    }
    if (!$selected_page || empty($selected_page['id'])) {
        if ($tokenPageId !== '') {
            $selected_page = [
                'id' => $tokenPageId,
                'name' => $connection['accountName'] ?: '',
                'connected' => true,
            ];
        }
    }

    $token_pages_payload = array_values(array_filter(array_map(function ($page) {
                        if (empty($page['page_access_token']) || empty($page['id'])) {
                            return null;
                        }
                        return [
                            'page_id' => (string) $page['id'],
                            'page_access_token' => (string) $page['page_access_token'],
                        ];
                    }, $token_pages_raw)));

    if (!empty($token_page_raw)) {
        $tp = $token_page_raw[0];
        if (!empty($tp['page_access_token']) && !empty($tp['id'])) {
            $candidate = [
                'page_id' => (string) $tp['id'],
                'page_access_token' => (string) $tp['page_access_token'],
            ];
            $exists = false;
            foreach ($token_pages_payload as $entry) {
                if ($entry['page_id'] === $candidate['page_id']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $token_pages_payload[] = $candidate;
            }
        }
    }

    $selected_page_token = '';
    if ($selected_page) {
        foreach (['page_access_token', 'access_token', 'token', 'page_token'] as $field) {
            if (!empty($selected_page[$field]) && is_string($selected_page[$field])) {
                $candidate_token = sanitize_text_field($selected_page[$field]);
                if ($candidate_token !== '') {
                    $selected_page_token = $candidate_token;
                    break;
                }
            }
        }
        if ($selected_page_token === '' && !empty($token_pages_payload)) {
            foreach ($token_pages_payload as $entry) {
                if ($entry['page_id'] === (string) $selected_page['id']) {
                    $selected_page_token = (string) $entry['page_access_token'];
                    break;
                }
            }
        }
        if ($selected_page_token === '' && isset($connection['token']['page_access_token'])) {
            $candidate_token = sanitize_text_field((string) $connection['token']['page_access_token']);
            if ($candidate_token !== '') {
                $selected_page_token = $candidate_token;
            }
        }
    }

    if ($selected_page_token !== '' && $selected_page && !empty($selected_page['id'])) {
        $has_entry = false;
        foreach ($token_pages_payload as $entry) {
            if ($entry['page_id'] === (string) $selected_page['id']) {
                $has_entry = true;
                break;
            }
        }
        if (!$has_entry) {
            $token_pages_payload[] = [
                'page_id' => (string) $selected_page['id'],
                'page_access_token' => $selected_page_token,
            ];
        }
    }

    if (!$selected_page || empty($selected_page['id'])) {
        return new WP_Error('fifu_as_facebook_page_missing', 'No Facebook page available', ['status' => 409]);
    }

    $message = '';
    foreach (['message', 'caption', 'text'] as $msg_key) {
        if (!empty($body[$msg_key]) && is_string($body[$msg_key])) {
            $message = wp_strip_all_tags((string) $body[$msg_key]);
            break;
        }
    }
    if ($message) {
        $message = trim(preg_replace('/\s+/', ' ', $message));
        if (function_exists('mb_substr')) {
            $message = mb_substr($message, 0, 5000);
        } else {
            $message = substr($message, 0, 5000);
        }
    }

    $site_origin = set_url_scheme(home_url('/'), 'https');
    $site_origin = rtrim($site_origin, '/');
    if (parse_url($site_origin, PHP_URL_SCHEME) !== 'https') {
        return new WP_Error('fifu_as_site_not_https', 'Facebook sharing requires the site to be served over HTTPS', ['status' => 409]);
    }

    $permalink = get_permalink($post_id);
    $link = $permalink ? set_url_scheme($permalink, 'https') : '';

    $payload = [
        'provider' => 'facebook',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'version' => fifu_as_version(),
        'postId' => $post_id,
        'post' => $post_payload,
        'page' => [
            'id' => (string) $selected_page['id'],
            'name' => isset($selected_page['name']) ? (string) $selected_page['name'] : '',
        ],
        'token' => $connection['token'],
        'requestedBy' => [
            'user_id' => get_current_user_id(),
            'timestamp' => gmdate('c', current_time('timestamp', true)),
        ],
    ];

    if ($selected_page_token !== '') {
        $payload['page']['page_access_token'] = $selected_page_token;
        $payload['page']['access_token'] = $selected_page_token;
    }

    if (!empty($token_pages_payload)) {
        $payload['token_pages'] = $token_pages_payload;
        foreach ($token_pages_payload as $entry) {
            if ($entry['page_id'] === (string) $selected_page['id']) {
                $payload['token_page'] = $entry;
                break;
            }
        }
        if (!isset($payload['token_page'])) {
            $payload['token_page'] = $token_pages_payload[0];
        }
    }

    if ($message) {
        $payload['message'] = $message;
    }
    if ($link) {
        $payload['link'] = $link;
    }

    if (function_exists('fifu_get_home_url')) {
        $payload['site'] = fifu_get_home_url();
    }
    $payload['site_origin'] = $site_origin;
    if (function_exists('fifu_partial_key')) {
        $payload['partial_key'] = fifu_partial_key();
    }

    $worker = fifu_as_worker_request('POST', '/share/facebook', $payload);
    if (is_wp_error($worker)) {
        if (function_exists('fifu_as_log_share_attempt')) {
            $extra = [
                'ERROR' => $worker->get_error_message(),
            ];
            $code = $worker->get_error_code();
            if ($code) {
                $extra['ERROR_CODE'] = $code;
            }
            fifu_as_log_share_attempt('facebook', $post_id, '', 'error', $extra);
        }
        return $worker;
    }

    $assessment = function_exists('fifu_as_assess_worker_response') ? fifu_as_assess_worker_response('facebook', $worker) : ['normalized' => $worker, 'error' => null];

    if (function_exists('fifu_as_log_share_attempt')) {
        $extra = [
            'RESPONSE' => $assessment['normalized'],
        ];
        if ($assessment['error'] instanceof WP_Error) {
            $extra['ERROR'] = $assessment['error']->get_error_message();
            fifu_as_log_share_attempt('facebook', $post_id, '', 'error', $extra);
        } else {
            fifu_as_log_share_attempt('facebook', $post_id, '', 'success', $extra);
        }
    }

    if ($assessment['error'] instanceof WP_Error) {
        return $assessment['error'];
    }

    if (function_exists('fifu_as_mark_shared_on_provider')) {
        fifu_as_mark_shared_on_provider($post_id, 'facebook');
    }

    return fifu_as_json([
        'ok' => true,
        'page' => $payload['page'],
        'worker' => $worker,
        'provider' => 'facebook',
    ]);
}

