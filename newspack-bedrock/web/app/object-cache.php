<?php
/**
 * Memcached object-cache drop-in for Newspack Bedrock / Roots Trellis.
 * PECL memcached only. Redis is not used.
 */
defined('ABSPATH') || exit;

if (!class_exists('Memcached')) {
    return;
}
function wp_cache_add($key, $data, $group = '', $expire = 0) { global $wp_object_cache; return $wp_object_cache->add($key, $data, $group, (int) $expire); }
function wp_cache_close() { return true; }
function wp_cache_decr($key, $offset = 1, $group = '') { global $wp_object_cache; return $wp_object_cache->decr($key, $offset, $group); }
function wp_cache_delete($key, $group = '') { global $wp_object_cache; return $wp_object_cache->delete($key, $group); }
function wp_cache_flush() { global $wp_object_cache; return $wp_object_cache->flush(); }
function wp_cache_flush_group($group) { global $wp_object_cache; return $wp_object_cache->flush_group($group); }
function wp_cache_get($key, $group = '', $force = false, &$found = null) { global $wp_object_cache; return $wp_object_cache->get($key, $group, $force, $found); }
function wp_cache_incr($key, $offset = 1, $group = '') { global $wp_object_cache; return $wp_object_cache->incr($key, $offset, $group); }
function wp_cache_init() { global $wp_object_cache; $wp_object_cache = new WP_Object_Cache(); }
function wp_cache_replace($key, $data, $group = '', $expire = 0) { global $wp_object_cache; return $wp_object_cache->replace($key, $data, $group, (int) $expire); }
function wp_cache_set($key, $data, $group = '', $expire = 0) { global $wp_object_cache; return $wp_object_cache->set($key, $data, $group, (int) $expire); }
function wp_cache_switch_to_blog($blog_id) { global $wp_object_cache; $wp_object_cache->switch_to_blog($blog_id); }
function wp_cache_add_global_groups($groups) { global $wp_object_cache; $wp_object_cache->add_global_groups($groups); }
function wp_cache_add_non_persistent_groups($groups) { global $wp_object_cache; $wp_object_cache->add_non_persistent_groups($groups); }

class WP_Object_Cache {
    private $m;
    private $global_groups = [];
    private $no_persist = [];
    private $runtime = [];
    private $blog_prefix = '1:';
    private $global_prefix = '';
    public function __construct() {
        $this->m = new Memcached(defined('MEMCACHED_PERSISTENT_ID') ? MEMCACHED_PERSISTENT_ID : 'newspack-bedrock');
        if (!count($this->m->getServerList())) {
            $host = defined('MEMCACHED_HOST') ? MEMCACHED_HOST : '127.0.0.1';
            $port = defined('MEMCACHED_PORT') ? (int) MEMCACHED_PORT : 11211;
            $this->m->addServer($host, $port);
        }
        if (function_exists('is_multisite') && is_multisite()) {
            $this->blog_prefix = get_current_blog_id() . ':';
        }
        $this->global_prefix = (defined('WP_CACHE_KEY_SALT') ? WP_CACHE_KEY_SALT : '') . 'nb:';
    }
    private function key($key, $group) {
        $group = $group ?: 'default';
        $prefix = isset($this->global_groups[$group]) ? $this->global_prefix : $this->global_prefix . $this->blog_prefix;
        return $prefix . $group . ':' . $key;
    }
    public function add($key, $data, $group = '', $expire = 0) {
        $this->get($key, $group, false, $found);
        if ($found) { return false; }
        return $this->set($key, $data, $group, $expire);
    }
    public function replace($key, $data, $group = '', $expire = 0) {
        $this->get($key, $group, false, $found);
        if (!$found) { return false; }
        return $this->set($key, $data, $group, $expire);
    }
    public function set($key, $data, $group = '', $expire = 0) {
        $group = $group ?: 'default';
        $ck = $this->key($key, $group);
        $this->runtime[$ck] = $data;
        if (isset($this->no_persist[$group])) { return true; }
        return $this->m->set($ck, $data, (int) $expire);
    }
    public function get($key, $group = '', $force = false, &$found = null) {
        $group = $group ?: 'default';
        $ck = $this->key($key, $group);
        if (!$force && array_key_exists($ck, $this->runtime)) { $found = true; return $this->runtime[$ck]; }
        if (isset($this->no_persist[$group])) { $found = false; return false; }
        $val = $this->m->get($ck);
        $found = Memcached::RES_NOTFOUND !== $this->m->getResultCode();
        if ($found) { $this->runtime[$ck] = $val; return $val; }
        return false;
    }
    public function delete($key, $group = '') {
        $ck = $this->key($key, $group ?: 'default');
        unset($this->runtime[$ck]);
        return $this->m->delete($ck);
    }
    public function flush() { $this->runtime = []; return $this->m->flush(); }
    public function flush_group($group) {
        foreach (array_keys($this->runtime) as $ck) {
            if (strpos($ck, ':' . $group . ':') !== false) { unset($this->runtime[$ck]); }
        }
        return true;
    }
    public function incr($key, $offset = 1, $group = '') { return $this->m->increment($this->key($key, $group), $offset); }
    public function decr($key, $offset = 1, $group = '') { return $this->m->decrement($this->key($key, $group), $offset); }
    public function add_global_groups($groups) { foreach ((array) $groups as $g) { $this->global_groups[$g] = true; } }
    public function add_non_persistent_groups($groups) { foreach ((array) $groups as $g) { $this->no_persist[$g] = true; } }
    public function switch_to_blog($blog_id) { $this->blog_prefix = (int) $blog_id . ':'; }
}

