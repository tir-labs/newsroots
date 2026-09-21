<?php

/**
 * FIFU Auto-Share — common (provider-agnostic) helpers
 * - Capability/nonce checks
 * - Worker HTTP client (adds site/partial_key query params)
 * - Minimal per-user storage for connection snapshot
 */
// ---- Capability / Nonce -----------------------------------------------------
function fifu_as_can_manage(WP_REST_Request $req) {
    $nonce = $req->get_header('x-wp-nonce');
    return current_user_can('manage_options') && wp_verify_nonce($nonce, 'wp_rest');
}

// ---- JSON helpers ------------------------------------------------------------
function fifu_as_json($data, int $status = 200) {
    return new WP_REST_Response($data, $status, [
        'Content-Type' => 'application/json; charset=utf-8'
    ]);
}

function fifu_as_read_json(WP_REST_Request $req) {
    $raw = $req->get_body();
    if (!$raw)
        return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ---- Context ----------------------------------------------------------------
function fifu_as_site_id() {
    if (is_multisite())
        return get_network()->id . ':' . get_current_blog_id();
    return (string) parse_url(home_url(), PHP_URL_HOST);
}

function fifu_as_user_id() {
    $u = get_current_user_id();
    return $u ? (int) $u : 0;
}

/** Version reported to the Worker (plugin build/version). */
function fifu_as_version() {
    return function_exists('fifu_version_number') ? fifu_version_number() : 'dev';
}

// ---- Worker base (fixed) -----------------------------------------------------
/** Cloudflare Worker handling OAuth and posting. */
function fifu_as_worker_base() {
    return 'https://auto-share.fifu.workers.dev';
}

// ---- HTTP client to Worker ---------------------------------------------------
/**
 * Call the Worker with minimal headers and the **required auth query params**:
 *   ?site=fifu_get_home_url()&partial_key=fifu_partial_key()
 *
 * Returns decoded array or WP_Error on transport/status error.
 */
function fifu_as_worker_request(string $method, string $path, array $payload = []) {
    // Base path
    $base = rtrim(fifu_as_worker_base(), '/') . '/' . ltrim($path, '/');

    // REQUIRED INTERNAL AUTH PARAMS
    $q = [
        'site' => function_exists('fifu_get_home_url') ? fifu_get_home_url() : home_url(),
        'partial_key' => function_exists('fifu_partial_key') ? fifu_partial_key() : '',
    ];

    // Append query params safely (keeps any existing query in $path)
    $url = add_query_arg($q, $base);

    $args = [
        'method' => strtoupper($method),
        'timeout' => 20,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-FIFU-Site' => fifu_as_site_id(),
            'X-FIFU-User' => (string) fifu_as_user_id(),
            'X-FIFU-Version' => fifu_as_version(),
        ],
        'body' => $payload ? wp_json_encode($payload) : null,
    ];

    $res = wp_remote_request($url, $args);
    if (is_wp_error($res)) {
        return new WP_Error('fifu_worker_http', $res->get_error_message(), ['status' => 502]);
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    $body = (string) wp_remote_retrieve_body($res);
    $json = json_decode($body, true);
    if ($code >= 400) {
        return new WP_Error('fifu_worker_bad_status', "Worker HTTP $code", ['status' => 502, 'body' => $body]);
    }
    return is_array($json) ? $json : [];
}

// ---- Minimal storage (per-user) ---------------------------------------------
function fifu_as_meta_key(string $provider, string $suffix) {
    return 'fifu_as_' . sanitize_key($provider) . '_' . sanitize_key($suffix);
}

/** Store connection snapshot (account/pages/token) for a provider. */
function fifu_as_store_connection(string $provider, array $data) {
    $uid = fifu_as_user_id();
    $account = sanitize_text_field($data['accountName'] ?? '');
    $pages = fifu_as_normalize_pages($data['pages'] ?? []);
    $token = fifu_as_normalize_token($data['token'] ?? []);

    $connected = isset($data['connected']) ? (bool) $data['connected'] : false;
    if (!$connected) {
        if (fifu_as_token_has_credential($token) || fifu_as_pages_have_credentials($pages)) {
            $connected = true;
        }
    }

    update_user_meta($uid, fifu_as_meta_key($provider, 'account'), $account);
    update_user_meta($uid, fifu_as_meta_key($provider, 'pages'), wp_json_encode($pages));
    update_user_meta($uid, fifu_as_meta_key($provider, 'token'), wp_json_encode($token));
    update_user_meta($uid, fifu_as_meta_key($provider, 'connected'), $connected ? 1 : 0);
    update_user_meta($uid, fifu_as_meta_key($provider, 'updated'), time());
}

/** Clear stored connection snapshot for a provider. */
function fifu_as_clear_connection(string $provider) {
    $uid = fifu_as_user_id();
    delete_user_meta($uid, fifu_as_meta_key($provider, 'account'));
    delete_user_meta($uid, fifu_as_meta_key($provider, 'pages'));
    delete_user_meta($uid, fifu_as_meta_key($provider, 'token'));
    delete_user_meta($uid, fifu_as_meta_key($provider, 'connected'));
    delete_user_meta($uid, fifu_as_meta_key($provider, 'updated'));
}

/** Safe snapshot for UI. */
function fifu_as_get_status_snapshot(string $provider) {
    $uid = fifu_as_user_id();
    $acc = get_user_meta($uid, fifu_as_meta_key($provider, 'account'), true);
    $pages = json_decode((string) get_user_meta($uid, fifu_as_meta_key($provider, 'pages'), true), true) ?: [];
    $tok_raw = json_decode((string) get_user_meta($uid, fifu_as_meta_key($provider, 'token'), true), true) ?: [];
    $tok = fifu_as_normalize_token($tok_raw);
    $upd = (int) get_user_meta($uid, fifu_as_meta_key($provider, 'updated'), true);
    $stored_connected_raw = get_user_meta($uid, fifu_as_meta_key($provider, 'connected'), true);
    $stored_connected = $stored_connected_raw !== '' && $stored_connected_raw !== null && $stored_connected_raw !== '0' && $stored_connected_raw !== 0;

    $normalizedPages = fifu_as_normalize_pages($pages);
    $connected = fifu_as_token_has_credential($tok);
    if (!$connected && fifu_as_pages_have_credentials($normalizedPages)) {
        $connected = true;
    }
    if (!$connected && $stored_connected) {
        $connected = true;
    }
    if (!$connected && $acc) {
        $connected = true;
    }

    return [
        'connected' => $connected,
        'accountName' => $acc ?: null,
        'pages' => array_map(function ($p) {
            return [
                'id' => isset($p['id']) ? (string) $p['id'] : null,
                'name' => isset($p['name']) ? (string) $p['name'] : null,
                'connected' => !empty($p['connected']),
            ];
        }, $normalizedPages),
        'lastChecked' => $upd ?: time(),
    ];
}

function fifu_as_normalize_pages($pages) {
    if (!is_array($pages)) {
        return [];
    }
    if (isset($pages['data']) && is_array($pages['data'])) {
        $pages = $pages['data'];
    } elseif (isset($pages['pages']) && is_array($pages['pages'])) {
        $pages = $pages['pages'];
    }

    if (isset($pages['page']) && is_array($pages['page'])) {
        $pages = [$pages['page']];
    }

    $is_numeric_indexed = !empty($pages) && array_keys($pages) === range(0, count($pages) - 1);
    if (!$is_numeric_indexed && (isset($pages['id']) || isset($pages['page_id']) || isset($pages['pageId']))) {
        $pages = [$pages];
    }

    $normalized = [];

    foreach ($pages as $page) {
        if (!is_array($page)) {
            continue;
        }

        if (isset($page['page']) && is_array($page['page'])) {
            $page = array_merge($page, $page['page']);
        }

        $structures = fifu_as_collect_nested_arrays($page);

        $id = fifu_as_find_first_value_in_nested($structures, [
            'id',
            'page_id',
            'pageId',
            'ig_user_id',
            'igUserId',
            'instagram_user_id',
            'instagramUserId',
            'instagram_business_account_id',
            'instagramBusinessAccountId',
            'instagram_id',
            'instagramId',
            'user_id',
            'account_id',
            'profile_id',
        ]);

        $ig_user_id_value = fifu_as_find_first_value_in_nested($structures, [
            'ig_user_id',
            'igUserId',
            'instagram_user_id',
            'instagramUserId',
            'instagram_id',
            'instagramId',
            'user_id',
            'profile_id',
        ]);

        $instagram_business_account_id = fifu_as_find_first_value_in_nested($structures, [
            'instagram_business_account_id',
            'instagramBusinessAccountId',
            'business_account_id',
            'businessAccountId',
            'instagram_business_id',
            'instagramBusinessId',
            'instagram_busines_account_id', // common typo safeguard
        ]);

        if ($id === '' && $instagram_business_account_id !== '') {
            $id = $instagram_business_account_id;
        }
        if ($id === '' && $ig_user_id_value !== '') {
            $id = $ig_user_id_value;
        }

        $name = fifu_as_find_first_value_in_nested($structures, [
            'name',
            'page_name',
            'pageName',
            'account_name',
            'accountName',
            'title',
            'username',
            'instagram_username',
            'instagramUsername',
            'handle',
        ]);

        $username = fifu_as_find_first_value_in_nested($structures, [
            'username',
            'instagram_username',
            'instagramUsername',
            'handle',
        ]);

        $connected = fifu_as_nested_flag_is_true($structures, [
            'connected',
            'selected',
            'is_primary',
            'is_connected',
            'isConnected',
        ]);

        if ($id === '' && $name === '' && $username === '') {
            continue;
        }

        $page_data = [
            'id' => $id !== '' ? $id : null,
            'name' => $name !== '' ? $name : null,
            'connected' => $connected,
        ];

        if ($ig_user_id_value !== '') {
            $page_data['ig_user_id'] = $ig_user_id_value;
        } elseif ($id !== '') {
            $page_data['ig_user_id'] = $id;
        }
        if ($instagram_business_account_id !== '') {
            $page_data['instagram_business_account_id'] = $instagram_business_account_id;
        }
        if ($username !== '') {
            $page_data['username'] = $username;
        }

        $token_fields = [
            'page_access_token',
            'access_token',
            'token',
            'pageToken',
            'page_token',
        ];
        $page_token = '';
        foreach ($structures as $structure) {
            foreach ($token_fields as $field) {
                if (!isset($structure[$field])) {
                    continue;
                }
                $candidate_token = $structure[$field];
                if (!is_string($candidate_token) && !is_numeric($candidate_token)) {
                    continue;
                }
                $candidate_token = sanitize_text_field((string) $candidate_token);
                if ($candidate_token !== '') {
                    $page_token = $candidate_token;
                    break 2;
                }
            }
        }

        if ($page_token !== '') {
            $page_data['page_access_token'] = $page_token;
            $page_data['access_token'] = $page_token;
            $page_data['connected'] = true;
        }

        $category = fifu_as_find_first_value_in_nested($structures, ['category', 'page_category', 'pageCategory']);
        if ($category !== '') {
            $page_data['category'] = $category;
        }

        $perms = [];
        foreach ($structures as $structure) {
            if (!isset($structure['perms']) || !is_array($structure['perms'])) {
                continue;
            }
            foreach ($structure['perms'] as $perm) {
                if (!is_string($perm) && !is_numeric($perm)) {
                    continue;
                }
                $perm_value = sanitize_text_field((string) $perm);
                if ($perm_value !== '') {
                    $perms[] = $perm_value;
                }
            }
        }
        if (!empty($perms)) {
            $page_data['perms'] = array_values(array_unique($perms));
        }

        $normalized[] = $page_data;
    }

    return array_values($normalized);
}

function fifu_as_normalize_token($token, int $depth = 0) {
    if (is_string($token)) {
        $token = ['access_token' => $token];
    }
    if (!is_array($token)) {
        return [];
    }

    $mapped = $token;
    $aliases = [
        'accessToken' => 'access_token',
        'userAccessToken' => 'user_access_token',
        'user_access_token' => 'user_access_token',
        'pageAccessToken' => 'page_access_token',
        'page_access_token' => 'page_access_token',
        'longLivedAccessToken' => 'long_lived_access_token',
        'instagramToken' => 'access_token',
        'value' => 'access_token',
    ];
    foreach ($aliases as $from => $to) {
        if (isset($mapped[$from]) && empty($mapped[$to])) {
            $mapped[$to] = $mapped[$from];
        }
    }

    if ($depth < 3) {
        foreach ($mapped as $key => $value) {
            if (is_array($value)) {
                $nested = fifu_as_normalize_token($value, $depth + 1);
                foreach ($nested as $nested_key => $nested_value) {
                    if (!isset($mapped[$nested_key]) || $mapped[$nested_key] === '' || $mapped[$nested_key] === null) {
                        $mapped[$nested_key] = $nested_value;
                    }
                }
            }
        }
    }

    foreach ($mapped as $key => $value) {
        if (is_string($value)) {
            $mapped[$key] = sanitize_text_field($value);
        }
    }

    return $mapped;
}

function fifu_as_token_has_credential(array $token, int $depth = 0) {
    $keys = [
        'access_token',
        'user_access_token',
        'page_access_token',
        'long_lived_access_token',
        'token',
    ];
    foreach ($keys as $key) {
        if (!empty($token[$key]) && is_string($token[$key])) {
            return true;
        }
    }
    if ($depth >= 3) {
        return false;
    }
    foreach ($token as $value) {
        if (is_array($value) && fifu_as_token_has_credential($value, $depth + 1)) {
            return true;
        }
    }
    return false;
}

function fifu_as_pages_have_credentials(array $pages) {
    foreach ($pages as $page) {
        if (!is_array($page)) {
            continue;
        }
        if (!empty($page['connected'])) {
            return true;
        }
        foreach (['page_access_token', 'access_token', 'token', 'ig_user_token'] as $field) {
            if (!empty($page[$field]) && is_string($page[$field])) {
                return true;
            }
        }
    }
    return false;
}

/** Return the stored connection snapshot with normalized data for internal use. */
function fifu_as_get_connection_details(string $provider) {
    $uid = fifu_as_user_id();
    $account = (string) get_user_meta($uid, fifu_as_meta_key($provider, 'account'), true);
    $pages_raw = json_decode((string) get_user_meta($uid, fifu_as_meta_key($provider, 'pages'), true), true) ?: [];
    $token_raw = json_decode((string) get_user_meta($uid, fifu_as_meta_key($provider, 'token'), true), true) ?: [];
    $stored_connected_raw = get_user_meta($uid, fifu_as_meta_key($provider, 'connected'), true);
    $stored_connected = $stored_connected_raw !== '' && $stored_connected_raw !== null && $stored_connected_raw !== '0' && $stored_connected_raw !== 0;

    $pages = fifu_as_normalize_pages($pages_raw);
    $token = fifu_as_normalize_token($token_raw);

    $connected = fifu_as_token_has_credential($token);
    if (!$connected && fifu_as_pages_have_credentials($pages)) {
        $connected = true;
    }
    if (!$connected && $stored_connected) {
        $connected = true;
    }
    if (!$connected && $account) {
        $connected = true;
    }

    return [
        'accountName' => $account ?: null,
        'pages' => $pages,
        'token' => $token,
        'connected' => $connected,
        'lastUpdated' => (int) get_user_meta($uid, fifu_as_meta_key($provider, 'updated'), true),
    ];
}

// ---- Share helpers ----------------------------------------------------------
function fifu_share_normalize_https_url($url) {
    if (!is_string($url)) {
        return '';
    }
    $candidate = trim($url);
    if ($candidate === '') {
        return '';
    }
    $sanitized = esc_url_raw($candidate);
    if (!$sanitized) {
        return '';
    }
    $https = set_url_scheme($sanitized, 'https');
    if (!wp_http_validate_url($https)) {
        return '';
    }
    return $https;
}

function fifu_share_is_same_host(string $url, string $origin) {
    $a = wp_parse_url($url);
    $b = wp_parse_url($origin);
    if (!$a || !$b || empty($a['host']) || empty($b['host'])) {
        return false;
    }
    return strtolower($a['host']) === strtolower($b['host']);
}

function fifu_build_post_share_payload(int $post_id) {
    $post = get_post($post_id);
    if (!$post) {
        return null;
    }

    $permalink = get_permalink($post);
    $content = apply_filters('the_content', $post->post_content);

    $excerpt = $post->post_excerpt;
    if (!$excerpt) {
        $excerpt = wp_trim_words(wp_strip_all_tags($post->post_content), 55, '…');
    }

    $author = get_userdata($post->post_author);

    return [
        'id' => $post->ID,
        'type' => $post->post_type,
        'status' => $post->post_status,
        'title' => html_entity_decode(get_the_title($post), ENT_QUOTES | ENT_HTML5, get_bloginfo('charset') ?: 'UTF-8'),
        'excerpt' => $excerpt,
        'content' => $content,
        'permalink' => $permalink,
        'date_gmt' => mysql_to_rfc3339($post->post_date_gmt ?: $post->post_date),
        'modified_gmt' => mysql_to_rfc3339($post->post_modified_gmt ?: $post->post_modified),
        'author' => $author ? [
    'id' => $author->ID,
    'display_name' => $author->display_name,
    'user_login' => $author->user_login,
        ] : null,
    ];
}

function fifu_as_extract_pages_from_granular_scopes($granular_scopes) {
    if (!is_array($granular_scopes)) {
        return [];
    }

    $pages = [];
    foreach ($granular_scopes as $scope_entry) {
        if (!is_array($scope_entry)) {
            continue;
        }
        $targets = $scope_entry['target_ids'] ?? [];
        if (!is_array($targets)) {
            continue;
        }
        foreach ($targets as $target_id) {
            if (!is_string($target_id) && !is_numeric($target_id)) {
                continue;
            }
            $id = sanitize_text_field((string) $target_id);
            if ($id === '') {
                continue;
            }
            if (!isset($pages[$id])) {
                $pages[$id] = [
                    'id' => $id,
                    'name' => null,
                    'connected' => true,
                ];
            }
        }
    }

    return array_values($pages);
}

function fifu_as_collect_nested_arrays($input) {
    $collected = [];
    $queue = [$input];
    while (!empty($queue)) {
        $current = array_shift($queue);
        if (!is_array($current)) {
            continue;
        }
        $collected[] = $current;
        foreach ($current as $value) {
            if (is_array($value)) {
                $queue[] = $value;
            }
        }
    }
    return $collected;
}

function fifu_as_find_first_value_in_nested(array $structures, array $keys) {
    foreach ($structures as $structure) {
        foreach ($keys as $key) {
            if (!isset($structure[$key])) {
                continue;
            }
            $value = $structure[$key];
            if (is_string($value) || is_numeric($value)) {
                $value = sanitize_text_field((string) $value);
                if ($value !== '') {
                    return $value;
                }
            }
        }
    }
    return '';
}

function fifu_as_nested_flag_is_true(array $structures, array $keys) {
    foreach ($structures as $structure) {
        foreach ($keys as $key) {
            if (!empty($structure[$key])) {
                return true;
            }
        }
    }
    return false;
}

