<?php

/**
 * FIFU Auto-Share — X (Twitter) specific thin handlers.
 * Mirrors the Facebook/Instagram flows: PHP proxies to the Worker.
 */
function fifu_as_x_auth_start(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);
    $intent = isset($body['intent']) ? sanitize_text_field($body['intent']) : 'login';

    $client_id = fifu_as_x_get_client_id();
    if ($client_id === '') {
        return new WP_Error('fifu_as_x_client_id_missing', 'X Client ID is required', ['status' => 400]);
    }

    $payload = [
        'provider' => 'x',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'intent' => $intent,
        'version' => fifu_as_version(),
        'clientId' => $client_id,
    ];

    $wk = fifu_as_worker_request('POST', '/v2/oauth/x/start', $payload);
    if (is_wp_error($wk)) {
        return $wk;
    }

    return fifu_as_json([
        'authUrl' => isset($wk['authUrl']) ? esc_url_raw($wk['authUrl']) : '',
        'state' => isset($wk['state']) ? sanitize_text_field($wk['state']) : '',
        'popupOrigin' => isset($wk['popupOrigin']) ? esc_url_raw($wk['popupOrigin']) : '',
    ]);
}

function fifu_as_x_auth_finalize(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);
    $token = isset($body['tempToken']) ? sanitize_text_field($body['tempToken']) : '';
    $state = isset($body['state']) ? sanitize_text_field($body['state']) : '';

    if (!$token) {
        return new WP_Error('fifu_as_bad_request', 'Missing tempToken', ['status' => 400]);
    }

    $client_id = fifu_as_x_get_client_id();
    if ($client_id === '') {
        return new WP_Error('fifu_as_x_client_id_missing', 'X Client ID is required', ['status' => 400]);
    }

    $payload = [
        'provider' => 'x',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'tempToken' => $token,
        'state' => $state,
        'version' => fifu_as_version(),
        'clientId' => $client_id,
    ];

    $wk = fifu_as_worker_request('POST', '/v2/oauth/x/finalize', $payload);
    if (is_wp_error($wk)) {
        return $wk;
    }

    $accounts_raw = [];
    foreach (['accounts', 'profiles', 'pages', 'tenants'] as $key) {
        if (!empty($wk[$key]) && is_array($wk[$key])) {
            $accounts_raw = $wk[$key];
            break;
        }
    }

    $token_payload = [];
    foreach (['token', 'tokens', 'credentials'] as $key) {
        if (!empty($wk[$key]) && is_array($wk[$key])) {
            $token_payload = $wk[$key];
            break;
        }
    }

    $normalized_accounts = fifu_as_normalize_pages($accounts_raw);
    $normalized_token = fifu_as_normalize_token($token_payload);

    $account_name = '';
    if (isset($wk['accountName']) && is_string($wk['accountName'])) {
        $account_name = sanitize_text_field($wk['accountName']);
    }
    if (!$account_name && !empty($wk['account']) && is_array($wk['account'])) {
        $maybe = $wk['account'];
        $account_name = isset($maybe['name']) ? sanitize_text_field($maybe['name']) : '';
        if (!$account_name && isset($maybe['username'])) {
            $account_name = sanitize_text_field($maybe['username']);
        }
    }
    if (!$account_name && !empty($normalized_accounts)) {
        $first = $normalized_accounts[0];
        if (!empty($first['name'])) {
            $account_name = sanitize_text_field($first['name']);
        } elseif (!empty($first['username'])) {
            $account_name = sanitize_text_field($first['username']);
        } elseif (!empty($first['handle'])) {
            $account_name = sanitize_text_field($first['handle']);
        }
    }

    $store = [
        'accountName' => $account_name,
        'pages' => $accounts_raw,
        'token' => $token_payload,
    ];

    $store['connected'] = !empty($wk['connected']);
    if (!$store['connected']) {
        if (fifu_as_token_has_credential($normalized_token) || fifu_as_pages_have_credentials($normalized_accounts)) {
            $store['connected'] = true;
        }
    }

    fifu_as_store_connection('x', $store);

    $connection = fifu_as_get_status_snapshot('x');

    return fifu_as_json([
        'connected' => !empty($connection['connected']),
        'accountName' => $connection['accountName'],
        'pages' => $connection['pages'],
    ]);
}

function fifu_as_x_status(WP_REST_Request $req) {
    $status = fifu_as_get_status_snapshot('x');
    $client_id = fifu_as_x_get_client_id();
    $status['clientId'] = $client_id;
    return fifu_as_json($status);
}

function fifu_as_x_disconnect(WP_REST_Request $req) {
    fifu_as_clear_connection('x');
    return fifu_as_json([
        'connected' => false,
        'accountName' => null,
        'pages' => [],
    ]);
}

function fifu_as_x_share(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);

    $post_id = isset($body['postId']) ? absint($body['postId']) : 0;
    if (!$post_id && isset($body['post_id'])) {
        $post_id = absint($body['post_id']);
    }
    if ($post_id <= 0) {
        return new WP_Error('fifu_as_missing_post', 'Post ID is required', ['status' => 400]);
    }

    $post_payload = fifu_build_post_share_payload($post_id);
    if (!$post_payload) {
        return new WP_Error('fifu_as_post_not_found', 'Post not found', ['status' => 404]);
    }

    $connection = fifu_as_get_connection_details('x');
    if (empty($connection['connected'])) {
        return new WP_Error('fifu_as_x_not_connected', 'X connection required', ['status' => 409]);
    }

    if (function_exists('fifu_as_has_shared_on_provider') && fifu_as_has_shared_on_provider($post_id, 'x')) {
        return fifu_as_json([
            'ok' => true,
            'already_shared' => true,
            'provider' => 'x',
        ]);
    }

    $accounts = [];
    if (!empty($connection['pages']) && is_array($connection['pages'])) {
        foreach ($connection['pages'] as $candidate) {
            if (is_array($candidate)) {
                $accounts[] = $candidate;
            }
        }
    }

    $requested_account_id = '';
    foreach (['accountId', 'account_id', 'account'] as $key) {
        if (!empty($body[$key]) && is_string($body[$key])) {
            $requested_account_id = trim((string) $body[$key]);
            break;
        }
    }

    $selected_account = null;
    if ($requested_account_id !== '') {
        foreach ($accounts as $account) {
            if (!is_array($account)) {
                continue;
            }
            foreach (['id', 'handle', 'username'] as $field) {
                if (isset($account[$field]) && (string) $account[$field] === $requested_account_id) {
                    $selected_account = $account;
                    break 2;
                }
            }
        }
    }

    if (!$selected_account && !empty($accounts)) {
        foreach ($accounts as $account) {
            if (!is_array($account)) {
                continue;
            }
            if (!empty($account['connected'])) {
                $selected_account = $account;
                break;
            }
        }
        if (!$selected_account) {
            $selected_account = $accounts[0];
        }
    }

    if (!$selected_account && !empty($connection['accountName'])) {
        $selected_account = [
            'name' => (string) $connection['accountName'],
        ];
    }

    $site_origin = set_url_scheme(home_url('/'), 'https');
    $site_origin = rtrim($site_origin, '/');

    $message = '';
    foreach (['message', 'text', 'caption'] as $msg_key) {
        if (!empty($body[$msg_key]) && is_string($body[$msg_key])) {
            $message = trim(wp_strip_all_tags((string) $body[$msg_key]));
            break;
        }
    }
    if ($message === '' && !empty($post_payload['excerpt'])) {
        $message = trim(wp_strip_all_tags((string) $post_payload['excerpt']));
    }
    if ($message !== '') {
        $message = preg_replace('/\s+/', ' ', $message);
        if (function_exists('mb_substr')) {
            $message = mb_substr($message, 0, 280);
        } else {
            $message = substr($message, 0, 280);
        }
    }

    $permalink = get_permalink($post_id);
    $link = $permalink ? set_url_scheme($permalink, 'https') : '';

    $media = null;
    if (parse_url($site_origin, PHP_URL_SCHEME) === 'https') {
        $media = fifu_share_prepare_instagram_media($post_id, $site_origin);
    }

    $payload = [
        'provider' => 'x',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'version' => fifu_as_version(),
        'postId' => $post_id,
        'post' => $post_payload,
        'account' => $selected_account ?: [],
        'accounts' => $accounts,
        'token' => is_array($connection['token']) ? $connection['token'] : [],
        'clientId' => fifu_as_x_get_client_id(),
        'requestedBy' => [
            'user_id' => get_current_user_id(),
            'timestamp' => gmdate('c', current_time('timestamp', true)),
        ],
    ];

    if (!empty($connection['accountName']) && empty($payload['account']['name'])) {
        $payload['account']['name'] = (string) $connection['accountName'];
    }

    if ($message !== '') {
        $payload['message'] = $message;
    }
    if ($link) {
        $payload['link'] = $link;
    }
    if ($media) {
        $payload['media'] = $media;
    }
    if (function_exists('fifu_get_home_url')) {
        $payload['site'] = fifu_get_home_url();
    }
    $payload['site_origin'] = $site_origin;
    if (function_exists('fifu_partial_key')) {
        $payload['partial_key'] = fifu_partial_key();
    }

    $worker = fifu_as_worker_request('POST', '/share/x', $payload);
    if (is_wp_error($worker)) {
        if (function_exists('fifu_as_log_share_attempt')) {
            $extra = [
                'ERROR' => $worker->get_error_message(),
            ];
            $code = $worker->get_error_code();
            if ($code) {
                $extra['ERROR_CODE'] = $code;
            }
            fifu_as_log_share_attempt('x', $post_id, '', 'error', $extra);
        }
        return $worker;
    }

    $assessment = function_exists('fifu_as_assess_worker_response') ? fifu_as_assess_worker_response('x', $worker) : ['normalized' => $worker, 'error' => null];

    if (function_exists('fifu_as_log_share_attempt')) {
        $extra = [
            'RESPONSE' => $assessment['normalized'],
        ];
        if ($assessment['error'] instanceof WP_Error) {
            $extra['ERROR'] = $assessment['error']->get_error_message();
            fifu_as_log_share_attempt('x', $post_id, '', 'error', $extra);
        } else {
            fifu_as_log_share_attempt('x', $post_id, '', 'success', $extra);
        }
    }

    if ($assessment['error'] instanceof WP_Error) {
        return $assessment['error'];
    }

    fifu_as_x_maybe_persist_refreshed_token(
            $worker,
            is_string($connection['accountName'] ?? null) ? $connection['accountName'] : '',
            $accounts ?: (is_array($connection['pages']) ? $connection['pages'] : [])
    );

    if (function_exists('fifu_as_mark_shared_on_provider')) {
        fifu_as_mark_shared_on_provider($post_id, 'x');
    }

    return fifu_as_json([
        'ok' => true,
        'account' => $payload['account'],
        'worker' => $worker,
        'provider' => 'x',
    ]);
}

/**
 * Persist refreshed X credentials returned by the worker so future requests use the new token set.
 *
 * @param array       $worker_response Raw response array from the worker.
 * @param string|null $account_name_hint Account name fallback from the stored connection snapshot.
 * @param array       $pages_hint Fallback array of pages/accounts from the stored connection.
 */
function fifu_as_x_maybe_persist_refreshed_token(array $worker_response, $account_name_hint, array $pages_hint) {
    $token = fifu_as_x_find_refresh_token_array($worker_response);
    if (!$token) {
        return false;
    }

    $refresh_section = isset($worker_response['refresh']) && is_array($worker_response['refresh']) ? $worker_response['refresh'] : null;

    if ($refresh_section) {
        $refresh_ok = null;
        if (isset($refresh_section['ok'])) {
            $value = $refresh_section['ok'];
            if (is_bool($value)) {
                $refresh_ok = $value;
            } else {
                $refresh_ok = in_array(strtolower((string) $value), ['1', 'true', 'yes', 'ok', 'success'], true);
            }
        }
        if ($refresh_ok === false) {
            return false;
        }
    }

    $account_name = fifu_as_x_extract_account_name_from_worker($worker_response);
    if ($account_name === '' && is_string($account_name_hint)) {
        $account_name = $account_name_hint;
    }

    $pages = fifu_as_x_extract_accounts_from_worker($worker_response);
    if ($pages === null) {
        $pages = $pages_hint;
    }

    fifu_as_store_connection('x', [
        'accountName' => $account_name,
        'pages' => $pages,
        'token' => $token,
        'connected' => true,
    ]);

    return true;
}

/**
 * Locate a token structure that includes a refresh_token inside the worker response tree.
 *
 * @param mixed $data
 * @param int   $depth
 * @return array|null
 */
function fifu_as_x_find_refresh_token_array($data, $depth = 0) {
    if ($depth > 6 || !is_array($data)) {
        return null;
    }

    if (isset($data['refresh_token']) && is_string($data['refresh_token'])) {
        $trimmed = trim($data['refresh_token']);
        if ($trimmed !== '') {
            return $data;
        }
    }

    foreach ($data as $value) {
        if (is_array($value)) {
            $found = fifu_as_x_find_refresh_token_array($value, $depth + 1);
            if ($found !== null) {
                return $found;
            }
        }
    }

    return null;
}

/**
 * Attempt to derive an account name from the worker response.
 *
 * @param array $worker_response
 * @return string
 */
function fifu_as_x_extract_account_name_from_worker(array $worker_response) {
    if (isset($worker_response['accountName']) && is_string($worker_response['accountName'])) {
        $name = trim($worker_response['accountName']);
        if ($name !== '') {
            return $name;
        }
    }

    if (!empty($worker_response['account']) && is_array($worker_response['account'])) {
        $account = $worker_response['account'];
        foreach (['name', 'username', 'handle'] as $field) {
            if (!empty($account[$field]) && is_string($account[$field])) {
                $candidate = trim($account[$field]);
                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }
    }

    $accounts = fifu_as_x_extract_accounts_from_worker($worker_response);
    if (is_array($accounts) && !empty($accounts)) {
        $primary = $accounts[0];
        if (is_array($primary)) {
            foreach (['name', 'username', 'handle'] as $field) {
                if (!empty($primary[$field]) && is_string($primary[$field])) {
                    $candidate = trim($primary[$field]);
                    if ($candidate !== '') {
                        return $candidate;
                    }
                }
            }
        }
    }

    return '';
}

/**
 * Pull account/page arrays from a worker response when present.
 *
 * @param array $worker_response
 * @return array|null
 */
function fifu_as_x_extract_accounts_from_worker(array $worker_response) {
    foreach (['accounts', 'pages', 'profiles', 'tenants'] as $key) {
        if (!empty($worker_response[$key]) && is_array($worker_response[$key])) {
            return array_values(array_filter($worker_response[$key], 'is_array'));
        }
    }

    if (!empty($worker_response['account']) && is_array($worker_response['account'])) {
        return [$worker_response['account']];
    }

    return null;
}

function fifu_as_x_normalize_client_id($value) {
    if (!is_string($value)) {
        if (is_numeric($value)) {
            $value = (string) $value;
        } else {
            return '';
        }
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }
    return sanitize_text_field($trimmed);
}

function fifu_as_x_get_client_id() {
    $raw = get_option('fifu_auto_share_x_clientid');
    if (!is_string($raw) && !is_numeric($raw)) {
        return '';
    }
    return fifu_as_x_normalize_client_id((string) $raw);
}

