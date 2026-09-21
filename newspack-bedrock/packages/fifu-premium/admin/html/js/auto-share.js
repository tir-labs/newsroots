/*! FIFU Auto-Share — menu bindings */
(function ($, window, document) {
    'use strict';

    const vars = window.fifuScriptVars || {};
    const restBase = ((vars.restUrl) ? String(vars.restUrl) : '/wp-json/').replace(/\/?$/, '/');
    const apiRoot = restBase + 'fifu-premium/v2';
    const nonce = vars.nonce || '';
    const X_CALLBACK_URL = 'https://auto-share.fifu.workers.dev/v2/oauth/x/callback';
    const SHARE_PROVIDERS = ['facebook', 'instagram', 'x'];

    const api = {
        startAuth: function (provider) {
            return apiRoot + '/social/' + provider + '/auth/start';
        },
        finalizeAuth: function (provider) {
            return apiRoot + '/social/' + provider + '/auth/finalize';
        },
        status: function (provider) {
            return apiRoot + '/social/' + provider + '/status';
        },
        disconnect: function (provider) {
            return apiRoot + '/social/' + provider + '/disconnect';
        },
        share: function (provider) {
            return apiRoot + '/share/' + provider;
        }
    };

    async function wpFetch(url, opts) {
        const options = opts || {};
        const headers = new Headers(options.headers || {});
        headers.set('Accept', 'application/json');
        if (options.method && options.method !== 'GET' && !headers.has('Content-Type')) {
            headers.set('Content-Type', 'application/json');
        }
        if (nonce) {
            headers.set('X-WP-Nonce', nonce);
        }
        const response = await fetch(url, {
            ...options,
            headers: headers,
            credentials: 'same-origin'
        });
        const text = await response.text();
        if (!response.ok) {
            let payload = null;
            if (text) {
                try {
                    payload = JSON.parse(text);
                } catch (err) {
                }
            }
            const error = new Error(('HTTP ' + response.status + ' ' + (response.statusText || '')).trim());
            error.status = response.status;
            error.body = text;
            if (payload) {
                error.payload = payload;
            }
            throw error;
        }
        try {
            return text ? JSON.parse(text) : {};
        } catch (err) {
            return {};
        }
    }

    function extractErrorMessage(error) {
        if (!error) {
            return 'Unknown error';
        }
        if (error.payload && typeof error.payload.message === 'string' && error.payload.message.trim() !== '') {
            return error.payload.message;
        }
        const combined = (error.body && typeof error.body === 'string')
                ? ((error.message || '') + ' ' + error.body).trim()
                : (error.message || String(error));
        const jsonMatch = combined.match(/\{.+\}$/);
        if (jsonMatch) {
            try {
                const parsed = JSON.parse(jsonMatch[0]);
                if (parsed && typeof parsed.message === 'string' && parsed.message.trim() !== '') {
                    return parsed.message;
                }
            } catch (err) {
            }
        }
        const cleaned = (error.message || String(error)).replace(/^HTTP\s\d+\s*/, '').trim();
        if (cleaned) {
            return cleaned;
        }
        if (error.body && typeof error.body === 'string') {
            const trimmed = error.body.trim();
            if (trimmed) {
                return trimmed;
            }
        }
        return 'Unknown error';
    }

    function providerLabel(provider) {
        switch (provider) {
            case 'facebook':
                return 'Facebook';
            case 'instagram':
                return 'Instagram';
            case 'x':
                return 'X';
            default:
                return (provider || '').charAt(0).toUpperCase() + (provider || '').slice(1);
        }
    }

    function setShareIconState(provider, state) {
        const icon = document.querySelector('.fifu-share-icon[data-provider="' + provider + '"]');
        if (!icon) {
            return;
        }
        if (!state) {
            icon.removeAttribute('data-share-state');
        } else {
            icon.setAttribute('data-share-state', state);
        }
    }

    function extractShareTarget(provider, response) {
        if (!response || typeof response !== 'object') {
            return '';
        }

        if (provider === 'facebook') {
            const page = (response.page && typeof response.page === 'object') ? response.page : null;
            const name = normalizeString(page && page.name, '');
            if (name) {
                return name;
            }
            const id = page && page.id;
            if (typeof id === 'string') {
                const trimmed = id.trim();
                if (trimmed !== '') {
                    return trimmed;
                }
            }
            if (typeof id === 'number' && !isNaN(id)) {
                return String(id);
            }
        }

        if (provider === 'instagram') {
            const account = (response.account && typeof response.account === 'object') ? response.account : null;
            const username = normalizeString(account && account.username, '');
            if (username) {
                return username;
            }
            const name = normalizeString(account && account.name, '');
            if (name) {
                return name;
            }
        }

        if (provider === 'x') {
            const account = (response.account && typeof response.account === 'object') ? response.account : null;
            const handle = normalizeString(account && account.handle, '');
            if (handle) {
                return handle;
            }
            const username = normalizeString(account && account.username, '');
            if (username) {
                return username;
            }
            const name = normalizeString(account && account.name, '');
            if (name) {
                return name;
            }
            const accountName = normalizeString(response.accountName, '');
            if (accountName) {
                return accountName;
            }
        }

        const account = (response.account && typeof response.account === 'object') ? response.account : null;
        const fallbackName = normalizeString(account && account.name, '');
        if (fallbackName) {
            return fallbackName;
        }
        const fallbackUsername = normalizeString(account && account.username, '');
        if (fallbackUsername) {
            return fallbackUsername;
        }
        return '';
    }

    function describeShareResult(provider, response) {
        const target = extractShareTarget(provider, response);
        const label = providerLabel(provider);
        return target ? (label + ' (' + target + ')') : label;
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') {
            return '';
        }
        return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
    }

    function normalizeString(value, fallback) {
        if (typeof value === 'string') {
            const trimmed = value.trim();
            if (trimmed !== '') {
                return trimmed;
            }
        }
        return fallback;
    }

    function openAuthTab() {
        let tab = window.open('about:blank', '_blank');
        if (tab) {
            try {
                tab.document.write(
                        '<!doctype html><html><head><meta charset="utf-8"><title>Loading…</title>' +
                        '<style>body{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;margin:0;padding:24px;display:flex;align-items:center;justify-content:center;background:#f8fafc;color:#0f172a;} .spinner{width:32px;height:32px;border:4px solid rgba(148,163,184,.4);border-top-color:#2563eb;border-radius:50%;animation:spin 0.9s linear infinite;margin-right:12px}@keyframes spin{to{transform:rotate(360deg)}} .wrap{display:flex;align-items:center;gap:12px;}</style>' +
                        '</head><body><div class="wrap"><div class="spinner" role="progressbar" aria-label="Loading"></div><div>' + vars.connect + '</div></div></body></html>'
                        );
                tab.document.close();
            } catch (err) {
            }
        }
        return tab;
    }

    function showProgress(message) {
        if (message) {
            console.info(message);
        }
    }

    function showResult(message, variant, options) {
        const opts = (options && typeof options === 'object') ? options : {};
        if (!message) {
            return;
        }
        if (variant === 'success' || variant === 'pending') {
            console.info(message);
        } else {
            console.error(message);
        }
        if (opts.silent) {
            return;
        }
        if (typeof window.updateMessage === 'function') {
            const state = variant === 'success' ? 'success' : 'error';
            window.updateMessage('Auto Share', message, state);
        } else if (variant === 'success' || variant === 'pending') {
            console.info(message);
        } else {
            console.error(message);
        }
    }

    function blockTabsTop() {
        const $target = $('#tabs-top');
        if (!$target.length || typeof $target.block !== 'function') {
            return null;
        }
        const message = (vars && typeof vars.wait1 === 'string') ? vars.wait1 : (vars && typeof vars.wait === 'string') ? vars.wait : '';
        $target.block({message: message, css: {backgroundColor: 'none', border: 'none', color: 'white'}});
        return function () {
            try {
                $target.unblock();
            } catch (err) {
            }
        };
    }

    async function connectProvider(provider) {
        const label = providerLabel(provider);
        showProgress('Connecting to ' + label + '…');
        const unblock = blockTabsTop();

        const authTab = openAuthTab();
        if (!authTab) {
            showResult('Allow pop-ups to connect to ' + label + '.', 'error');
            if (typeof unblock === 'function') {
                unblock();
            }
            return false;
        }

        let state = '';
        try {
            const payload = {provider: provider, intent: 'login', version: 1};
            const response = await wpFetch(api.startAuth(provider), {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            state = (response && typeof response.state === 'string') ? response.state : '';
            const authUrl = (response && typeof response.authUrl === 'string') ? response.authUrl : '';
            if (!authUrl) {
                showResult(label + ' authorization URL not available.', 'error');
                try {
                    authTab.close();
                } catch (err) {
                }
                if (typeof unblock === 'function') {
                    unblock();
                }
                return false;
            }
            authTab.location.replace(authUrl);
        } catch (error) {
            showResult(label + ' authorization failed: ' + extractErrorMessage(error), 'error');
            try {
                authTab.close();
            } catch (err) {
            }
            if (typeof unblock === 'function') {
                unblock();
            }
            return false;
        }

        return await new Promise(function (resolve) {
            let settled = false;
            let watcherId = null;

            function cleanup() {
                if (watcherId !== null) {
                    clearInterval(watcherId);
                    watcherId = null;
                }
                window.removeEventListener('message', onMessage);
            }

            function finish(ok, message) {
                if (settled) {
                    return;
                }
                settled = true;
                cleanup();
                try {
                    authTab.close();
                } catch (err) {
                }
                if (ok) {
                    console.info(message || (label + ' connected.'));
                } else {
                    showResult(message || (label + ' connection failed.'), 'error');
                }
                if (typeof unblock === 'function') {
                    unblock();
                }
                resolve(ok);
            }

            watcherId = setInterval(function () {
                if (authTab.closed) {
                    finish(false, label + ' authorization window closed before completion.');
                }
            }, 600);

            async function onMessage(ev) {
                const msg = ev.data || {};
                if (msg.source !== 'fifu-worker' || msg.flow !== 'oauth' || msg.provider !== provider) {
                    return;
                }

                cleanup();

                if (msg.status !== 'success') {
                    finish(false, label + ' authorization cancelled.');
                    return;
                }

                showProgress('Finalizing ' + label + ' connection…');

                try {
                    await wpFetch(api.finalizeAuth(provider), {
                        method: 'POST',
                        body: JSON.stringify({provider: provider, tempToken: msg.tempToken, state: state})
                    });
                } catch (error) {
                    finish(false, 'Finalize failed: ' + extractErrorMessage(error));
                    return;
                }

                try {
                    const status = await wpFetch(api.status(provider), {method: 'GET'});
                    if (status && status.connected) {
                        finish(true, label + ' connected.');
                    } else {
                        finish(false, label + ' connection not confirmed.');
                    }
                } catch (error) {
                    finish(false, 'Could not confirm ' + label + ' connection: ' + extractErrorMessage(error));
                }
            }

            window.addEventListener('message', onMessage);
        });
    }

    async function disconnectProvider(provider) {
        const label = providerLabel(provider);
        showProgress('Disconnecting from ' + label + '…');
        const unblock = blockTabsTop();

        try {
            await wpFetch(api.disconnect(provider), {method: 'POST'});
            console.info(label + ' disconnected.');
            return true;
        } catch (error) {
            showResult('Could not disconnect from ' + label + ': ' + extractErrorMessage(error), 'error');
            return false;
        } finally {
            if (typeof unblock === 'function') {
                unblock();
            }
        }
    }

    function bindAutoShareToggle(provider, toggleSelector, formSelector, invertKey) {
        const $toggle = $(toggleSelector);
        const $form = $(formSelector);
        if (!$toggle.length || !$form.length) {
            return;
        }

        let busy = false;
        const el = $toggle.get(0);

        el.addEventListener('click', async function (event) {
            const isOn = el.classList.contains('toggleon');
            const isOff = el.classList.contains('toggleoff');
            if (!isOn && !isOff) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (busy) {
                return;
            }

            busy = true;

            try {
                if (isOn) {
                    const ok = await disconnectProvider(provider);
                    if (ok && typeof window.invert === 'function') {
                        window.invert(invertKey);
                        $form.trigger('submit');
                    }
                } else {
                    const ok = await connectProvider(provider);
                    if (ok && typeof window.invert === 'function') {
                        window.invert(invertKey);
                        $form.trigger('submit');
                    }
                }
            } finally {
                busy = false;
            }
        }, true);
    }

    $(function () {
        bindAutoShareToggle('facebook', '#fifu_toggle_auto_share_facebook', '#fifu_form_auto_share_facebook', 'auto_share_facebook');
        bindAutoShareToggle('instagram', '#fifu_toggle_auto_share_instagram', '#fifu_form_auto_share_instagram', 'auto_share_instagram');
        bindAutoShareToggle('x', '#fifu_toggle_auto_share_x', '#fifu_form_auto_share_x', 'auto_share_x');
        $('#fifu_form_auto_share_test').on('submit', async function (event) {
            event.preventDefault();
            const $form = $(this);
            const rawPostId = $('#fifu_input_auto_share_postid').val();
            const postId = parseInt(rawPostId, 10);
            if (!postId) {
                showResult('Enter a valid post ID.', 'error', {silent: true});
                return;
            }

            SHARE_PROVIDERS.forEach(function (provider) {
                setShareIconState(provider, null);
            });

            const $submit = $form.find('input[type="submit"], button[type="submit"]');
            if ($submit.length) {
                $submit.prop('disabled', true);
            }

            showProgress('Preparing to share post ' + postId + '…');

            let unblockTabsTop = null;
            try {
                unblockTabsTop = blockTabsTop();
                const providers = SHARE_PROVIDERS;
                const statusResults = await Promise.all(providers.map(function (provider) {
                    return wpFetch(api.status(provider), {method: 'GET'}).then(function (status) {
                        return {provider: provider, status: status};
                    }).catch(function (error) {
                        return {provider: provider, error: error};
                    });
                }));

                const statusFailures = statusResults.filter(function (item) {
                    return item.error;
                }).map(function (item) {
                    return providerLabel(item.provider) + ': ' + extractErrorMessage(item.error);
                });

                statusResults.forEach(function (item) {
                    if (item.error) {
                        setShareIconState(item.provider, 'error');
                    } else {
                        setShareIconState(item.provider, null);
                    }
                });

                const connectedProviders = statusResults.filter(function (item) {
                    return item.status && item.status.connected;
                }).map(function (item) {
                    return item.provider;
                });

                if (!connectedProviders.length) {
                    if (statusFailures.length) {
                        showResult('Could not share post ' + postId + ': ' + statusFailures.join('; '), 'error', {silent: true});
                    } else {
                        showResult('Connect a social account before sharing post ' + postId + '.', 'error', {silent: true});
                    }
                    return;
                }

                connectedProviders.forEach(function (provider) {
                    setShareIconState(provider, 'pending');
                });

                const shareResults = await Promise.allSettled(connectedProviders.map(function (provider) {
                    return wpFetch(api.share(provider), {
                        method: 'POST',
                        body: JSON.stringify({postId: postId})
                    }).then(function (response) {
                        return {provider: provider, response: response};
                    }).catch(function (error) {
                        return Promise.reject({provider: provider, error: error});
                    });
                }));

                const successes = [];
                const failures = statusFailures.slice();

                shareResults.forEach(function (result) {
                    if (result.status === 'fulfilled') {
                        const value = result.value || {};
                        const provider = value.provider;
                        setShareIconState(provider, 'success');
                        successes.push(describeShareResult(provider, value.response));
                    } else {
                        const reason = result.reason || {};
                        const provider = reason.provider || 'unknown';
                        const message = reason.error ? extractErrorMessage(reason.error) : 'Unknown error';
                        failures.push(providerLabel(provider) + ': ' + message);
                        setShareIconState(provider, 'error');
                    }
                });

                if (successes.length && !failures.length) {
                    showResult('Post ' + postId + ' share queued for ' + successes.join(', ') + '.', 'success', {silent: true});
                } else if (successes.length && failures.length) {
                    showResult('Post ' + postId + ' share queued for ' + successes.join(', ') + ' with issues for ' + failures.join('; '), 'error', {silent: true});
                } else if (!successes.length && failures.length) {
                    showResult('Post ' + postId + ' share failed: ' + failures.join('; '), 'error', {silent: true});
                } else {
                    showResult('No share actions were performed for post ' + postId + '.', 'error', {silent: true});
                }
            } finally {
                if ($submit.length) {
                    $submit.prop('disabled', false);
                }
                if (typeof unblockTabsTop === 'function') {
                    try {
                        unblockTabsTop();
                    } catch (err) {
                        console.debug('Could not unblock tabs-top:', err);
                    }
                }
            }
        });

        $(document).on('click', '#fifu-auto-share-info', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (!$.fancybox || !$.isFunction($.fancybox.open)) {
                console.warn('fancyBox is not available to open Auto-Share info.');
                return;
            }
            const infoVars = (vars.shareInfo && typeof vars.shareInfo === 'object') ? vars.shareInfo : {};
            const shareInfo = {
                title: normalizeString(infoVars.title, ''),
                facebook: {
                    page: normalizeString(infoVars.facebook && infoVars.facebook.page, ''),
                    published: normalizeString(infoVars.facebook && infoVars.facebook.published, '')
                },
                instagram: {
                    professional: normalizeString(infoVars.instagram && infoVars.instagram.professional, ''),
                    public: normalizeString(infoVars.instagram && infoVars.instagram.public, '')
                },
                x: {
                    developer: normalizeString(infoVars.x && infoVars.x.developer, ''),
                    permissions: normalizeString(infoVars.x && infoVars.x.permissions, ''),
                    type: normalizeString(infoVars.x && infoVars.x.type, ''),
                    callback: normalizeString(infoVars.x && infoVars.x.callback, ''),
                    client: normalizeString(infoVars.x && infoVars.x.client, '')
                }
            };

            const facebookItems = [
                shareInfo.facebook.page,
                shareInfo.facebook.published
            ].filter(Boolean).map(function (text) {
                return '<li>' + escapeHtml(text) + '</li>';
            }).join('');

            const instagramItems = [
                shareInfo.instagram.professional,
                shareInfo.instagram.public
            ].filter(Boolean).map(function (text) {
                return '<li>' + escapeHtml(text) + '</li>';
            }).join('');

            const xItemsArray = [];
            if (shareInfo.x.developer) {
                xItemsArray.push('<li>' + escapeHtml(shareInfo.x.developer) + ' <code>' + 'https://developer.x.com' + '</code></li>');
            }
            if (shareInfo.x.permissions) {
                xItemsArray.push('<li>' + escapeHtml(shareInfo.x.permissions) + '</li>');
            }
            if (shareInfo.x.type) {
                xItemsArray.push('<li>' + escapeHtml(shareInfo.x.type) + '</li>');
            }
            if (shareInfo.x.callback) {
                xItemsArray.push('<li>' + escapeHtml(shareInfo.x.callback) + ' <code>' + escapeHtml(X_CALLBACK_URL) + '</code></li>');
            }
            if (shareInfo.x.client) {
                xItemsArray.push('<li>' + escapeHtml(shareInfo.x.client) + '</li>');
            }
            const xItems = xItemsArray.join('');

            const modalHtmlParts = [
                '<div class="fifu-auto-share-modal" tabindex="0">'
            ];

            if (shareInfo.title) {
                modalHtmlParts.push('<h2>' + escapeHtml(shareInfo.title) + '</h2>');
            }

            if (facebookItems) {
                modalHtmlParts.push(
                        '<section class="fifu-auto-share-section fifu-auto-share-facebook">',
                        '<div class="fifu-auto-share-section__header">',
                        '<span class="fifu-auto-share-section__label">' + escapeHtml('Facebook') + '</span>',
                        '</div>',
                        '<ul>' + facebookItems + '</ul>',
                        '</section>'
                        );
            }

            if (instagramItems) {
                modalHtmlParts.push(
                        '<section class="fifu-auto-share-section fifu-auto-share-instagram">',
                        '<div class="fifu-auto-share-section__header">',
                        '<span class="fifu-auto-share-section__label">' + escapeHtml('Instagram') + '</span>',
                        '</div>',
                        '<ul>' + instagramItems + '</ul>',
                        '</section>'
                        );
            }

            if (xItems) {
                modalHtmlParts.push(
                        '<section class="fifu-auto-share-section fifu-auto-share-x">',
                        '<div class="fifu-auto-share-section__header">',
                        '<span class="fifu-auto-share-section__label">' + escapeHtml('X') + '</span>',
                        '</div>',
                        '<ul>' + xItems + '</ul>',
                        '</section>'
                        );
            }

            modalHtmlParts.push('</div>');

            const modalHtml = modalHtmlParts.join('');

            $.fancybox.open({
                src: modalHtml,
                type: 'html',
                touch: false,
                smallBtn: true,
                dragToClose: false,
                clickSlide: false,
                clickOutside: false
            });
        });
    });

})(jQuery, window, document);
