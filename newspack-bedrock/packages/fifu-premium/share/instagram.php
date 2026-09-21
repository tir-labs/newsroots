<?php

/**
 * FIFU Auto-Share — Instagram-specific thin handlers.
 * The Worker owns the actual OAuth + token exchange flow; PHP just proxies.
 */
function fifu_as_instagram_auth_start(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);

    $payload = [
        'provider' => 'instagram',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'intent' => $body['intent'] ?? 'login',
        'version' => fifu_as_version(),
    ];

    $wk = fifu_as_worker_request('POST', '/v2/oauth/instagram/start', $payload);
    if (is_wp_error($wk)) {
        return $wk;
    }

    return fifu_as_json([
        'authUrl' => isset($wk['authUrl']) ? esc_url_raw($wk['authUrl']) : '',
        'state' => isset($wk['state']) ? sanitize_text_field($wk['state']) : '',
        'popupOrigin' => isset($wk['popupOrigin']) ? esc_url_raw($wk['popupOrigin']) : '',
    ]);
}

function fifu_as_instagram_auth_finalize(WP_REST_Request $req) {
    $body = fifu_as_read_json($req);
    $token = isset($body['tempToken']) ? sanitize_text_field($body['tempToken']) : '';
    $state = isset($body['state']) ? sanitize_text_field($body['state']) : '';

    if (!$token) {
        return new WP_Error('fifu_as_bad_request', 'Missing tempToken', ['status' => 400]);
    }

    $payload = [
        'provider' => 'instagram',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'tempToken' => $token,
        'state' => $state,
        'version' => fifu_as_version(),
    ];

    $wk = fifu_as_worker_request('POST', '/v2/oauth/instagram/finalize', $payload);
    if (is_wp_error($wk)) {
        return $wk;
    }

    $accounts_raw = [];
    if (isset($wk['accounts']) && is_array($wk['accounts'])) {
        $accounts_raw = $wk['accounts'];
    } elseif (isset($wk['profiles']) && is_array($wk['profiles'])) {
        $accounts_raw = $wk['profiles'];
    } elseif (isset($wk['pages']) && is_array($wk['pages'])) {
        $accounts_raw = $wk['pages'];
    }

    $token_payload = [];
    if (isset($wk['token']) && is_array($wk['token'])) {
        $token_payload = $wk['token'];
    } elseif (isset($wk['tokens']) && is_array($wk['tokens'])) {
        $token_payload = $wk['tokens'];
    } elseif (isset($wk['credentials']) && is_array($wk['credentials'])) {
        $token_payload = $wk['credentials'];
    }

    $normalized_pages = fifu_as_normalize_pages($accounts_raw);
    $normalized_token = fifu_as_normalize_token($token_payload);

    $account_name = '';
    if (isset($wk['accountName']) && is_string($wk['accountName'])) {
        $account_name = sanitize_text_field($wk['accountName']);
    }

    $store = [
        'accountName' => $account_name,
        'pages' => $accounts_raw,
        'token' => $token_payload,
    ];

    if (!$store['accountName']) {
        if (!empty($normalized_pages[0]['name'])) {
            $store['accountName'] = $normalized_pages[0]['name'];
        } elseif (!empty($normalized_pages[0]['username'])) {
            $store['accountName'] = $normalized_pages[0]['username'];
        }
    }

    $store['connected'] = !empty($wk['connected']);
    if (!$store['connected']) {
        if (fifu_as_token_has_credential($normalized_token) || fifu_as_pages_have_credentials($normalized_pages) || !empty($normalized_pages)) {
            $store['connected'] = true;
        }
    }

    fifu_as_store_connection('instagram', $store);

    $connection = fifu_as_get_status_snapshot('instagram');

    return fifu_as_json([
        'connected' => !empty($connection['connected']),
        'accountName' => $connection['accountName'],
        'pages' => $connection['pages'],
    ]);
}

function fifu_as_instagram_status(WP_REST_Request $req) {
    return fifu_as_json(fifu_as_get_status_snapshot('instagram'));
}

function fifu_as_instagram_disconnect(WP_REST_Request $req) {
    fifu_as_clear_connection('instagram');
    return fifu_as_json([
        'connected' => false,
        'accountName' => null,
        'pages' => [],
    ]);
}

function fifu_share_prepare_instagram_media($post_id, string $site_origin, $override_url = null) {
    $candidates = [];
    if ($override_url) {
        $candidates[] = ['url' => $override_url, 'source' => 'override'];
    }

    $featured = get_the_post_thumbnail_url($post_id, 'full');
    if ($featured) {
        $candidates[] = ['url' => $featured, 'source' => 'featured'];
    }

    if (function_exists('fifu_main_image_url')) {
        $main = fifu_main_image_url($post_id, true);
        if ($main) {
            $candidates[] = ['url' => $main, 'source' => 'fifu_main'];
        }
    }

    $normalized_candidates = [];
    foreach ($candidates as $candidate) {
        if (!isset($candidate['url']) || !is_string($candidate['url'])) {
            continue;
        }
        $raw = trim($candidate['url']);
        if ($raw === '') {
            continue;
        }
        $normalized = fifu_share_normalize_https_url($raw);
        if (!$normalized) {
            continue;
        }

        $source = isset($candidate['source']) ? $candidate['source'] : 'unknown';
        $normalized_candidates[] = [
            'url' => $normalized,
            'source' => $source,
        ];

        if (fifu_share_is_same_host($normalized, $site_origin)) {
            return [
                'type' => 'image',
                'image_url' => $normalized,
                'imageUrl' => $normalized,
                'media_url' => $normalized,
                'mediaUrl' => $normalized,
                'url' => $normalized,
            ];
        }
    }

    foreach ($normalized_candidates as $candidate) {
        $url = $candidate['url'];
        $source = $candidate['source'];
        $allow_remote = true;
        if (function_exists('apply_filters')) {
            /**
             * Allow overriding whether remote (off-site) images can be used for Instagram.
             *
             * @param bool   $allow_remote Default true — FIFU remote images are expected.
             * @param string $url          Candidate media URL.
             * @param int    $post_id      Post identifier being shared.
             * @param string $source       The origin of the candidate (override, featured, fifu_main).
             */
            $allow_remote = apply_filters('fifu_as_allow_remote_instagram_media', true, $url, $post_id, $source);
        }
        if (!$allow_remote) {
            continue;
        }

        return [
            'type' => 'image',
            'image_url' => $url,
            'imageUrl' => $url,
            'media_url' => $url,
            'mediaUrl' => $url,
            'url' => $url,
        ];
    }

    return null;
}

function fifu_as_select_instagram_account(array $accounts, array $body) {
    $requested = '';
    $request_keys = ['igUserId', 'ig_user_id', 'pageId', 'page_id', 'accountId', 'account_id'];
    foreach ($request_keys as $key) {
        if (!empty($body[$key]) && is_string($body[$key])) {
            $requested = sanitize_text_field($body[$key]);
            break;
        }
    }

    $normalize = static function ($value) {
        if (!is_string($value) && !is_numeric($value)) {
            return '';
        }
        $value = sanitize_text_field((string) $value);
        return $value === '' ? '' : $value;
    };

    $preferred = null;
    if ($requested !== '') {
        foreach ($accounts as $account) {
            if (!is_array($account)) {
                continue;
            }
            $candidates = [
                $account['ig_user_id'] ?? null,
                $account['instagram_business_account_id'] ?? null,
                $account['id'] ?? null,
                $account['page_id'] ?? null,
            ];
            foreach ($candidates as $candidate) {
                if ($requested === $normalize($candidate)) {
                    $preferred = $account;
                    break 2;
                }
            }
        }
    }

    if ($preferred) {
        return $preferred;
    }

    foreach ($accounts as $account) {
        if (!is_array($account)) {
            continue;
        }
        if (!empty($account['connected'])) {
            return $account;
        }
    }

    foreach ($accounts as $account) {
        if (is_array($account)) {
            return $account;
        }
    }

    return null;
}

function fifu_as_find_token_value_recursive($token, array $keys) {
    if (!is_array($token) || empty($keys)) {
        return '';
    }

    $queue = [$token];
    while (!empty($queue)) {
        $current = array_shift($queue);
        if (!is_array($current)) {
            continue;
        }
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                continue;
            }
            $candidate = $current[$key];
            if (!is_string($candidate) && !is_numeric($candidate)) {
                continue;
            }
            $candidate = sanitize_text_field((string) $candidate);
            if ($candidate !== '') {
                return $candidate;
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

function fifu_as_build_instagram_account_from_token(array $token, $account_name = '') {
    if (empty($token)) {
        return null;
    }

    $instagram_business_account_id = fifu_as_find_token_value_recursive($token, ['instagram_business_account_id']);
    $ig_user_id = fifu_as_find_token_value_recursive($token, ['ig_user_id', 'instagram_id', 'instagramId']);

    $instagram_business_account_data = fifu_as_find_token_array_recursive($token, [
        'instagram_business_account',
        'instagram_business_account_info',
        'instagram_business_profile',
        'instagram_account',
        'instagram_profile',
        'instagram',
    ]);
    if (is_array($instagram_business_account_data)) {
        if ($instagram_business_account_id === '') {
            $instagram_business_account_id = fifu_as_find_token_value_recursive($instagram_business_account_data, ['id']);
        }
        if ($ig_user_id === '') {
            $ig_user_id = fifu_as_find_token_value_recursive($instagram_business_account_data, ['ig_user_id', 'instagram_id', 'id']);
        }
        if ($ig_user_id === '' && isset($instagram_business_account_data['user_id'])) {
            $candidate = $instagram_business_account_data['user_id'];
            if (is_string($candidate) || is_numeric($candidate)) {
                $ig_user_id = sanitize_text_field((string) $candidate);
            }
        }
    }

    $primary_id = $instagram_business_account_id !== '' ? $instagram_business_account_id : $ig_user_id;
    if ($primary_id === '') {
        $primary_id = fifu_as_find_token_value_recursive($token, ['id', 'user_id']);
    }

    $username = fifu_as_find_token_value_recursive($token, ['username', 'instagram_username', 'handle']);
    $name = fifu_as_find_token_value_recursive($token, ['name', 'account_name', 'title']);
    if ($name === '' && is_string($account_name) && $account_name !== '') {
        $name = sanitize_text_field($account_name);
    }

    $access_token = fifu_as_find_token_value_recursive($token, ['access_token', 'user_access_token', 'token']);

    if ($primary_id === '' && $username === '' && $name === '') {
        return null;
    }

    $account = [
        'connected' => true,
    ];

    if ($primary_id !== '') {
        $account['id'] = $primary_id;
    }
    if ($instagram_business_account_id !== '') {
        $account['instagram_business_account_id'] = $instagram_business_account_id;
    }
    if ($ig_user_id !== '' && $ig_user_id !== $primary_id) {
        $account['ig_user_id'] = $ig_user_id;
    } elseif ($primary_id !== '') {
        $account['ig_user_id'] = $primary_id;
    }
    if ($username !== '') {
        $account['username'] = $username;
    }
    if ($name !== '') {
        $account['name'] = $name;
    }
    if ($access_token !== '') {
        $account['access_token'] = $access_token;
        $account['token'] = $access_token;
    }

    return $account;
}

function fifu_as_find_token_array_recursive($token, array $keys) {
    if (!is_array($token) || empty($keys)) {
        return null;
    }

    $queue = [$token];
    while (!empty($queue)) {
        $current = array_shift($queue);
        if (!is_array($current)) {
            continue;
        }
        foreach ($current as $key => $value) {
            if (in_array($key, $keys, true) && is_array($value) && !empty($value)) {
                return $value;
            }
            if (is_array($value)) {
                $queue[] = $value;
            }
        }
    }

    return null;
}

function fifu_as_collect_instagram_access_token(array $account, array $token) {
    $token_fields = [
        'access_token',
        'page_access_token',
        'token',
        'user_access_token',
        'long_lived_access_token',
    ];

    foreach ($token_fields as $field) {
        if (!empty($account[$field]) && is_string($account[$field])) {
            $candidate = trim((string) $account[$field]);
            if ($candidate !== '') {
                return $candidate;
            }
        }
    }

    $candidate = fifu_as_find_token_value_recursive($token, $token_fields);
    if ($candidate !== '') {
        return $candidate;
    }

    return '';
}

function fifu_as_graph_candidate_to_account(array $candidate, string $source = '', string $page_id = '') {
    $account = [
        'source' => $source,
    ];
    if ($page_id !== '') {
        $account['page_id'] = $page_id;
    }
    $is_instagram = !empty($candidate['username']) || !empty($candidate['ig_id']) || !empty($candidate['__from_me']) || !empty($candidate['__from_connected']) || !empty($candidate['__from_instagram']);

    if (!$is_instagram && strpos($source, 'page') === 0) {
        if (!empty($candidate['instagram_business_account']) && is_array($candidate['instagram_business_account'])) {
            $is_instagram = true;
            $candidate = array_merge($candidate, $candidate['instagram_business_account']);
        } elseif (!empty($candidate['connected_instagram_account']) && is_array($candidate['connected_instagram_account'])) {
            $is_instagram = true;
            $candidate = array_merge($candidate, $candidate['connected_instagram_account']);
        }
    }

    if ($is_instagram) {
        if (!empty($candidate['id'])) {
            $value = sanitize_text_field((string) $candidate['id']);
            if ($value !== '') {
                $account['instagram_business_account_id'] = $value;
                $account['ig_user_id'] = $value;
                $account['id'] = $value;
            }
        }
        if (!empty($candidate['ig_id'])) {
            $value = sanitize_text_field((string) $candidate['ig_id']);
            if ($value !== '') {
                $account['ig_user_id'] = $value;
                if (empty($account['id'])) {
                    $account['id'] = $value;
                }
            }
        }
        if (!empty($candidate['username']) && is_string($candidate['username'])) {
            $value = sanitize_text_field($candidate['username']);
            if ($value !== '') {
                $account['username'] = $value;
            }
        }
        if (!empty($candidate['name']) && is_string($candidate['name'])) {
            $value = sanitize_text_field($candidate['name']);
            if ($value !== '') {
                $account['name'] = $value;
            }
        }
    }

    return $account;
}

function fifu_as_fetch_instagram_identity_via_graph(string $access_token) {
    if ($access_token === '') {
        return [
            'identity' => [],
            'accounts' => [],
        ];
    }

    $identity = [];
    $accounts = [];
    $page_ids = [];

    $maybe_add_account = static function (array $candidate) use (&$accounts) {
        $key = '';
        if (!empty($candidate['ig_user_id'])) {
            $key = 'ig:' . $candidate['ig_user_id'];
        } elseif (!empty($candidate['instagram_business_account_id'])) {
            $key = 'iba:' . $candidate['instagram_business_account_id'];
        } elseif (!empty($candidate['id'])) {
            $key = 'id:' . $candidate['id'];
        }
        if ($key === '') {
            return;
        }
        if (!isset($accounts[$key])) {
            $accounts[$key] = $candidate;
        } else {
            $accounts[$key] = array_merge($accounts[$key], $candidate);
        }
    };

    $endpoints = [
        [
            'url' => 'https://graph.facebook.com/v21.0/me',
            'query' => [
                'fields' => 'id,name',
            ],
        ],
        [
            'url' => 'https://graph.facebook.com/v21.0/me/accounts',
            'query' => [
                'fields' => 'id,name,instagram_business_account{id,username,ig_id},connected_instagram_account{id,username,ig_id}',
            ],
        ],
    ];

    foreach ($endpoints as $endpoint) {
        $query = $endpoint['query'];
        $query['access_token'] = $access_token;

        $url = add_query_arg($query, $endpoint['url']);
        $response = wp_remote_get($url, ['timeout' => 15]);
        if (is_wp_error($response)) {
            continue;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code >= 400) {
            continue;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            continue;
        }

        $candidates = [];
        if ($endpoint['url'] === 'https://graph.facebook.com/v21.0/me') {
            $candidates[] = $data;
            if (isset($data['instagram_business_account']) && is_array($data['instagram_business_account'])) {
                $instagram_business_account = $data['instagram_business_account'];
                $instagram_business_account['__from_me'] = true;
                $candidates[] = $instagram_business_account;
            }
            if (isset($data['connected_instagram_account']) && is_array($data['connected_instagram_account'])) {
                $connected_instagram_account = $data['connected_instagram_account'];
                $connected_instagram_account['__from_connected'] = true;
                $candidates[] = $connected_instagram_account;
            }
        } elseif ($endpoint['url'] === 'https://graph.facebook.com/v21.0/me/accounts') {
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $page_entry) {
                    if (isset($page_entry['instagram_business_account']) && is_array($page_entry['instagram_business_account'])) {
                        $iba = $page_entry['instagram_business_account'];
                        if (!isset($iba['__from_page']) && isset($page_entry['id'])) {
                            $iba['__from_page'] = sanitize_text_field((string) $page_entry['id']);
                        }
                        $candidates[] = $iba;
                    }
                    if (isset($page_entry['connected_instagram_account']) && is_array($page_entry['connected_instagram_account'])) {
                        $cia = $page_entry['connected_instagram_account'];
                        if (!isset($cia['__from_page']) && isset($page_entry['id'])) {
                            $cia['__from_page'] = sanitize_text_field((string) $page_entry['id']);
                        }
                        $candidates[] = $cia;
                    }
                    $candidates[] = $page_entry;
                    if (!empty($page_entry['id']) && is_string($page_entry['id'])) {
                        $page_ids[] = sanitize_text_field($page_entry['id']);
                    }
                }
            }
        }

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            if (empty($identity['instagram_business_account_id']) && !empty($candidate['id']) && (!empty($candidate['__from_me']) || !empty($candidate['__from_page']))) {
                $value = sanitize_text_field((string) $candidate['id']);
                if ($value !== '') {
                    $identity['instagram_business_account_id'] = $value;
                }
            }
            if (empty($identity['ig_user_id']) && !empty($candidate['id']) && ($endpoint['url'] === 'https://graph.facebook.com/v21.0/me' || !empty($candidate['__from_me']))) {
                $value = sanitize_text_field((string) $candidate['id']);
                if ($value !== '') {
                    $identity['ig_user_id'] = $value;
                }
            }
            if (empty($identity['username']) && !empty($candidate['username']) && is_string($candidate['username'])) {
                $value = sanitize_text_field($candidate['username']);
                if ($value !== '') {
                    $identity['username'] = $value;
                }
            }
            if (empty($identity['name']) && !empty($candidate['name']) && is_string($candidate['name'])) {
                $value = sanitize_text_field($candidate['name']);
                if ($value !== '') {
                    $identity['name'] = $value;
                }
            }

            $source = '';
            if (!empty($candidate['__from_page'])) {
                $source = 'page:' . $candidate['__from_page'];
            } elseif (!empty($candidate['__from_me'])) {
                $source = 'me';
            } elseif (!empty($candidate['__from_connected'])) {
                $source = 'connected';
            } elseif ($endpoint['url'] === 'https://graph.facebook.com/v21.0/me/accounts') {
                $source = 'page';
            } else {
                $source = 'me';
            }

            $page_id = '';
            if (!empty($candidate['__from_page'])) {
                $page_id = sanitize_text_field((string) $candidate['__from_page']);
            } elseif (!empty($candidate['id']) && $source === 'page') {
                $page_id = sanitize_text_field((string) $candidate['id']);
            }

            $account_candidate = fifu_as_graph_candidate_to_account($candidate, $source, $page_id);
            if (!empty($account_candidate['instagram_business_account_id']) || !empty($account_candidate['ig_user_id'])) {
                $maybe_add_account($account_candidate);
            }
        }

        if (!empty($identity['instagram_business_account_id']) && !empty($identity['ig_user_id'])) {
            break;
        }
    }

    $page_ids = array_values(array_unique(array_filter($page_ids)));
    if (!empty($page_ids) && (empty($identity['instagram_business_account_id']) || empty($identity['ig_user_id']))) {
        foreach ($page_ids as $page_id) {
            $url = add_query_arg([
                'fields' => 'instagram_business_account{id,username,ig_id},connected_instagram_account{id,username,ig_id}',
                'access_token' => $access_token,
                    ], 'https://graph.facebook.com/v21.0/' . rawurlencode($page_id));
            $response = wp_remote_get($url, ['timeout' => 15]);
            if (is_wp_error($response)) {
                continue;
            }
            $code = (int) wp_remote_retrieve_response_code($response);
            if ($code >= 400) {
                continue;
            }
            $page_body = wp_remote_retrieve_body($response);
            $page_data = json_decode($page_body, true);
            if (!is_array($page_data)) {
                continue;
            }
            foreach (['instagram_business_account', 'connected_instagram_account'] as $key) {
                if (!isset($page_data[$key]) || !is_array($page_data[$key])) {
                    continue;
                }
                $candidate = $page_data[$key];
                $candidate['__from_page'] = $page_id;
                $account_candidate = fifu_as_graph_candidate_to_account($candidate, 'page_fetch:' . $page_id, $page_id);
                if (!empty($account_candidate['instagram_business_account_id']) || !empty($account_candidate['ig_user_id'])) {
                    $maybe_add_account($account_candidate);
                }
                if (empty($identity['instagram_business_account_id']) && !empty($account_candidate['instagram_business_account_id'])) {
                    $identity['instagram_business_account_id'] = $account_candidate['instagram_business_account_id'];
                }
                if (empty($identity['ig_user_id']) && !empty($account_candidate['ig_user_id'])) {
                    $identity['ig_user_id'] = $account_candidate['ig_user_id'];
                }
                if (empty($identity['username']) && !empty($account_candidate['username'])) {
                    $identity['username'] = $account_candidate['username'];
                }
                if (empty($identity['name']) && !empty($account_candidate['name'])) {
                    $identity['name'] = $account_candidate['name'];
                }
            }
            if (!empty($identity['instagram_business_account_id']) && !empty($identity['ig_user_id'])) {
                break;
            }
        }
    }

    return [
        'identity' => [
            'instagram_business_account_id' => $identity['instagram_business_account_id'] ?? '',
            'ig_user_id' => $identity['ig_user_id'] ?? '',
            'username' => $identity['username'] ?? '',
            'name' => $identity['name'] ?? '',
        ],
        'accounts' => array_values($accounts),
    ];
}

function fifu_as_resolve_instagram_identity(array $account, array $token, $account_name = '', array &$discovered_accounts = []) {
    $resolved = $account;
    $discovered_accounts = [];

    $has_id = !empty($resolved['id']);
    $has_ig_user_id = !empty($resolved['ig_user_id']);
    $has_instagram_business_account_id = !empty($resolved['instagram_business_account_id']);

    if ($has_id && $has_ig_user_id) {
        return $resolved;
    }

    $access_token = fifu_as_collect_instagram_access_token($resolved, $token);

    $identity_result = $access_token !== '' ? fifu_as_fetch_instagram_identity_via_graph($access_token) : ['identity' => [], 'accounts' => []];
    $identity = isset($identity_result['identity']) && is_array($identity_result['identity']) ? $identity_result['identity'] : [];
    $discovered_accounts = isset($identity_result['accounts']) && is_array($identity_result['accounts']) ? $identity_result['accounts'] : [];

    if (!empty($identity['instagram_business_account_id'])) {
        $resolved['instagram_business_account_id'] = $identity['instagram_business_account_id'];
    }
    if (!empty($identity['ig_user_id'])) {
        $resolved['ig_user_id'] = $identity['ig_user_id'];
    }
    if (!empty($identity['username']) && empty($resolved['username'])) {
        $resolved['username'] = $identity['username'];
    }
    if (!empty($identity['name']) && empty($resolved['name'])) {
        $resolved['name'] = $identity['name'];
    }
    if (empty($resolved['id']) && !empty($resolved['ig_user_id'])) {
        $resolved['id'] = $resolved['ig_user_id'];
    }
    if (empty($resolved['ig_user_id']) && !empty($resolved['instagram_business_account_id'])) {
        $resolved['ig_user_id'] = $resolved['instagram_business_account_id'];
    }

    return $resolved;
}

function fifu_as_enrich_instagram_account(array $account, array $token, $account_name = '') {
    $enriched = $account;

    $fill = static function (&$target, string $field, $value) {
        if (!isset($target[$field]) || $target[$field] === '' || $target[$field] === null) {
            if (is_string($value) || is_numeric($value)) {
                $target[$field] = sanitize_text_field((string) $value);
            }
        }
    };

    $token_account = fifu_as_build_instagram_account_from_token($token, $account_name);
    if (is_array($token_account)) {
        foreach ([
    'instagram_business_account_id',
    'ig_user_id',
    'id',
    'username',
    'name',
    'access_token',
    'token',
        ] as $field) {
            if (isset($token_account[$field]) && $token_account[$field] !== '' && $token_account[$field] !== null) {
                $fill($enriched, $field, $token_account[$field]);
            }
        }
        if (!empty($token_account['connected'])) {
            $enriched['connected'] = true;
        }
    }

    if ((empty($enriched['ig_user_id']) || !is_string($enriched['ig_user_id'])) && !empty($enriched['instagram_business_account_id'])) {
        $fill($enriched, 'ig_user_id', $enriched['instagram_business_account_id']);
    }
    if ((empty($enriched['ig_user_id']) || !is_string($enriched['ig_user_id'])) && !empty($enriched['id'])) {
        $fill($enriched, 'ig_user_id', $enriched['id']);
    }
    if ((empty($enriched['id']) || !is_string($enriched['id'])) && !empty($enriched['ig_user_id'])) {
        $fill($enriched, 'id', $enriched['ig_user_id']);
    }

    if (!empty($enriched['username']) && is_string($enriched['username'])) {
        $enriched['username'] = sanitize_text_field($enriched['username']);
    }
    if (!empty($enriched['name']) && is_string($enriched['name'])) {
        $enriched['name'] = sanitize_text_field($enriched['name']);
    }

    return $enriched;
}

function fifu_as_instagram_share(WP_REST_Request $req) {
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

    $connection = fifu_as_get_connection_details('instagram');
    if (empty($connection['connected'])) {
        return new WP_Error('fifu_as_instagram_not_connected', 'Instagram connection required', ['status' => 409]);
    }

    if (function_exists('fifu_as_has_shared_on_provider') && fifu_as_has_shared_on_provider($post_id, 'instagram')) {
        return fifu_as_json([
            'ok' => true,
            'already_shared' => true,
            'provider' => 'instagram',
        ]);
    }

    $accounts = is_array($connection['pages']) ? $connection['pages'] : [];
    $selected_account = fifu_as_select_instagram_account($accounts, $body);
    if (!$selected_account) {
        $fallback_account = fifu_as_build_instagram_account_from_token(
                is_array($connection['token']) ? $connection['token'] : [],
                $connection['accountName'] ?? ''
        );
        if ($fallback_account) {
            $accounts[] = $fallback_account;
            $selected_account = $fallback_account;
        }
    }
    if (!$selected_account) {
        return new WP_Error('fifu_as_instagram_account_missing', 'Instagram account selection required', ['status' => 409]);
    }

    $token_data = is_array($connection['token']) ? $connection['token'] : [];
    $selected_account = fifu_as_enrich_instagram_account($selected_account, $token_data, $connection['accountName'] ?? '');
    $discovered_accounts = [];
    $selected_account = fifu_as_resolve_instagram_identity($selected_account, $token_data, $connection['accountName'] ?? '', $discovered_accounts);

    $selected_id = isset($selected_account['id']) ? (string) $selected_account['id'] : '';
    $selected_ig = isset($selected_account['ig_user_id']) ? (string) $selected_account['ig_user_id'] : '';
    $selected_iba = isset($selected_account['instagram_business_account_id']) ? (string) $selected_account['instagram_business_account_id'] : '';

    $matched_index = null;
    foreach ($accounts as $idx => $account) {
        if (!is_array($account)) {
            continue;
        }
        $candidate_values = [];
        foreach (['id', 'ig_user_id', 'instagram_business_account_id', 'page_id'] as $field) {
            if (isset($account[$field]) && (is_string($account[$field]) || is_numeric($account[$field]))) {
                $candidate_values[] = sanitize_text_field((string) $account[$field]);
            }
        }
        if (($selected_id !== '' && in_array($selected_id, $candidate_values, true)) ||
                ($selected_ig !== '' && in_array($selected_ig, $candidate_values, true)) ||
                ($selected_iba !== '' && in_array($selected_iba, $candidate_values, true))) {
            $matched_index = $idx;
            break;
        }
    }

    if ($matched_index !== null) {
        $accounts[$matched_index] = array_merge($accounts[$matched_index], $selected_account);
    } else {
        $accounts[] = $selected_account;
    }

    if (!empty($discovered_accounts)) {
        foreach ($discovered_accounts as $discovered) {
            if (!is_array($discovered)) {
                continue;
            }
            $discovered_id = isset($discovered['id']) ? (string) $discovered['id'] : '';
            $discovered_ig = isset($discovered['ig_user_id']) ? (string) $discovered['ig_user_id'] : '';
            $discovered_iba = isset($discovered['instagram_business_account_id']) ? (string) $discovered['instagram_business_account_id'] : '';

            $exists = false;
            foreach ($accounts as $idx => $account) {
                if (!is_array($account)) {
                    continue;
                }
                $values = [];
                foreach (['id', 'ig_user_id', 'instagram_business_account_id'] as $field) {
                    if (!empty($account[$field]) && (is_string($account[$field]) || is_numeric($account[$field]))) {
                        $values[] = sanitize_text_field((string) $account[$field]);
                    }
                }
                if (($discovered_id !== '' && in_array($discovered_id, $values, true)) ||
                        ($discovered_ig !== '' && in_array($discovered_ig, $values, true)) ||
                        ($discovered_iba !== '' && in_array($discovered_iba, $values, true))) {
                    $accounts[$idx] = array_merge($account, $discovered);
                    $exists = true;
                    break;
                }
            }
            if (!$exists && ($discovered_id !== '' || $discovered_ig !== '' || $discovered_iba !== '')) {
                $accounts[] = $discovered;
            }
        }
    }

    $site_origin = set_url_scheme(home_url('/'), 'https');
    $site_origin = rtrim($site_origin, '/');
    if (parse_url($site_origin, PHP_URL_SCHEME) !== 'https') {
        return new WP_Error('fifu_as_site_not_https', 'Instagram sharing requires the site to be served over HTTPS', ['status' => 409]);
    }

    $override_url = null;
    foreach (['imageUrl', 'image_url', 'mediaUrl', 'media_url'] as $media_key) {
        if (!empty($body[$media_key]) && is_string($body[$media_key])) {
            $override_url = $body[$media_key];
            break;
        }
    }

    $media = fifu_share_prepare_instagram_media($post_id, $site_origin, $override_url);
    if (!$media) {
        return new WP_Error('fifu_as_instagram_media_missing', 'Unable to locate an HTTPS image hosted on this site for the post.', ['status' => 422]);
    }

    $message = '';
    foreach (['caption', 'message', 'text'] as $field) {
        if (!empty($body[$field]) && is_string($body[$field])) {
            $message = trim(wp_strip_all_tags($body[$field]));
            break;
        }
    }
    if ($message === '' && !empty($post_payload['excerpt'])) {
        $message = trim(wp_strip_all_tags((string) $post_payload['excerpt']));
    }
    if ($message !== '') {
        $message = preg_replace('/\s+/', ' ', $message);
        if (function_exists('mb_substr')) {
            $message = mb_substr($message, 0, 2200);
        } else {
            $message = substr($message, 0, 2200);
        }
    }

    $permalink = isset($post_payload['permalink']) ? set_url_scheme($post_payload['permalink'], 'https') : '';
    $share_link = '';
    if ($permalink && fifu_share_is_same_host($permalink, $site_origin)) {
        $share_link = $permalink;
    }

    $account_payload = [
        'id' => isset($selected_account['id']) ? (string) $selected_account['id'] : '',
        'ig_user_id' => isset($selected_account['ig_user_id']) ? (string) $selected_account['ig_user_id'] : '',
        'page_id' => isset($selected_account['page_id']) ? (string) $selected_account['page_id'] : '',
        'name' => isset($selected_account['name']) ? (string) $selected_account['name'] : '',
        'username' => isset($selected_account['username']) ? (string) $selected_account['username'] : '',
    ];
    if (isset($selected_account['instagram_business_account_id'])) {
        $account_payload['instagram_business_account_id'] = (string) $selected_account['instagram_business_account_id'];
    }
    if ($account_payload['id'] === '' && $account_payload['ig_user_id'] !== '') {
        $account_payload['id'] = $account_payload['ig_user_id'];
    }

    foreach (['access_token', 'page_access_token', 'token'] as $token_field) {
        if (!empty($selected_account[$token_field]) && is_string($selected_account[$token_field])) {
            $account_payload[$token_field] = sanitize_text_field($selected_account[$token_field]);
            break;
        }
    }

    $payload = [
        'provider' => 'instagram',
        'site_id' => fifu_as_site_id(),
        'user_id' => fifu_as_user_id(),
        'version' => fifu_as_version(),
        'postId' => $post_id,
        'post' => $post_payload,
        'account' => $account_payload,
        'accounts' => $accounts,
        'token' => $connection['token'],
        'media' => $media,
        'requestedBy' => [
            'user_id' => get_current_user_id(),
            'timestamp' => gmdate('c', current_time('timestamp', true)),
        ],
    ];
    if (is_array($media)) {
        $media_primary_url = '';
        foreach (['media_url', 'mediaUrl', 'image_url', 'imageUrl', 'url', 'video_url', 'videoUrl'] as $media_url_key) {
            if (!empty($media[$media_url_key]) && is_string($media[$media_url_key])) {
                $candidate_url = trim($media[$media_url_key]);
                if ($candidate_url !== '') {
                    $media_primary_url = $candidate_url;
                    break;
                }
            }
        }
        if ($media_primary_url !== '') {
            $payload['media_url'] = $media_primary_url;
            $payload['mediaUrl'] = $media_primary_url;
            $media['media_url'] = $media_primary_url;
            $media['mediaUrl'] = $media_primary_url;
            $media['url'] = $media_primary_url;
            $media['image_url'] = $media_primary_url;
            $media['imageUrl'] = $media_primary_url;
            if (!empty($media['type']) && $media['type'] === 'video') {
                $payload['video_url'] = $media_primary_url;
                $payload['videoUrl'] = $media_primary_url;
                $media['video_url'] = $media_primary_url;
                $media['videoUrl'] = $media_primary_url;
            } else {
                $payload['image_url'] = $media_primary_url;
                $payload['imageUrl'] = $media_primary_url;
            }
            $payload['media'] = $media;
        }
    }

    if ($message !== '') {
        $payload['caption'] = $message;
        $payload['message'] = $message;
    }
    if ($share_link !== '') {
        $payload['link'] = $share_link;
    }
    if (function_exists('fifu_get_home_url')) {
        $payload['site'] = fifu_get_home_url();
    }
    $payload['site_origin'] = $site_origin;
    if (function_exists('fifu_partial_key')) {
        $payload['partial_key'] = fifu_partial_key();
    }

    $worker = fifu_as_worker_request('POST', '/share/instagram', $payload);
    if (is_wp_error($worker)) {
        if (function_exists('fifu_as_log_share_attempt')) {
            $extra = [
                'ERROR' => $worker->get_error_message(),
            ];
            $code = $worker->get_error_code();
            if ($code) {
                $extra['ERROR_CODE'] = $code;
            }
            fifu_as_log_share_attempt('instagram', $post_id, '', 'error', $extra);
        }
        return $worker;
    }

    $assessment = function_exists('fifu_as_assess_worker_response') ? fifu_as_assess_worker_response('instagram', $worker) : ['normalized' => $worker, 'error' => null];

    if (function_exists('fifu_as_log_share_attempt')) {
        $extra = [
            'RESPONSE' => $assessment['normalized'],
        ];
        if ($assessment['error'] instanceof WP_Error) {
            $extra['ERROR'] = $assessment['error']->get_error_message();
            fifu_as_log_share_attempt('instagram', $post_id, '', 'error', $extra);
        } else {
            fifu_as_log_share_attempt('instagram', $post_id, '', 'success', $extra);
        }
    }

    if ($assessment['error'] instanceof WP_Error) {
        return $assessment['error'];
    }

    if (function_exists('fifu_as_mark_shared_on_provider')) {
        fifu_as_mark_shared_on_provider($post_id, 'instagram');
    }

    return fifu_as_json([
        'ok' => true,
        'account' => [
            'id' => $account_payload['id'],
            'ig_user_id' => $account_payload['ig_user_id'],
            'username' => $account_payload['username'],
            'name' => $account_payload['name'],
        ],
        'worker' => $worker,
        'provider' => 'instagram',
    ]);
}

