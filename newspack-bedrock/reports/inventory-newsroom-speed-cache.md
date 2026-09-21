# Phase 0 Inventory: newsroom-speed-cache

=== newsroom-speed-cache ===
<?php
/**
 * Plugin Name: Newspack Object Cache
 * Description: Wraps Newpack's hot database queries with WordPress object cache calls. Works with ANY backend — Memcached, Redis, APCu, file-based, etc. Uses wp_cache_* abstraction layer.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author: Newsroom
 * License: GPL-2.0-or-later
 *
 * @package Newspack_Object_Cache
 */

namespace Newspack_Object_Cache;

defined( 'ABSPATH' ) || exit;

class Cache {

    /**
     * Default TTL for cached items (1 hour).
     */
    const DEFAULT_TTL = 3600;

    /**
     * Cache group prefix.
     */
    const GROUP = 'newspack';

    /**
<?php
/**
 * Plugin Name: Newsroom Speed Cache
 * Plugin URI:  https://github.com/newsroom/speed-cache
 * Description: Makes your newsroom blazing fast. Wraps Newpack's heaviest database queries with instant object cache lookups. Works with Memcached, Redis, APCu, or any WordPress object cache backend.
 * Version:     1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author:      Newsroom
 * Author URI:  https://newsroom.dev
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: newsroom-speed-cache
 * Domain Path: /languages
 * Requires Plugins: newspack-plugin
 *
 * @package Newsroom_Speed_Cache
 */

namespace Newsroom_Speed_Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Speed_Cache {

    /**
     * Plugin version.
---FILES---
newsroom-speed-cache/newspack-object-cache.php
newsroom-speed-cache/newsroom-speed-cache.php
---FUNCTIONS---
newsroom-speed-cache/newspack-object-cache.php:33:    public static function init() {
newsroom-speed-cache/newspack-object-cache.php:79:    public static function cache_gate_restrict( $restrict, $post_id ) {
newsroom-speed-cache/newspack-object-cache.php:99:    public static function flush_gate_cache( $post_id ) {
newsroom-speed-cache/newspack-object-cache.php:113:    public static function cache_metering( $restrict, $post_id ) {
newsroom-speed-cache/newspack-object-cache.php:121:    public static function flush_metering_cache( $activity ) {
newsroom-speed-cache/newspack-object-cache.php:132:    public static function cache_lists_config( $config ) {
newsroom-speed-cache/newspack-object-cache.php:142:    public static function flush_lists_cache() {
newsroom-speed-cache/newspack-object-cache.php:150:    public static function cache_placements( $placements ) {
newsroom-speed-cache/newspack-object-cache.php:160:    public static function flush_placements_cache() {
newsroom-speed-cache/newspack-object-cache.php:168:    public static function cache_reader_data( $data, $user_id ) {
newsroom-speed-cache/newspack-object-cache.php:178:    public static function flush_reader_cache( $user_id ) {
newsroom-speed-cache/newspack-object-cache.php:186:    public static function cache_provider_config( $provider ) {
newsroom-speed-cache/newspack-object-cache.php:196:    public static function flush_provider_cache() {
newsroom-speed-cache/newspack-object-cache.php:204:    public static function cache_sponsors( $sponsors, $post_id ) {
newsroom-speed-cache/newspack-object-cache.php:214:    public static function flush_sponsors_cache() {
newsroom-speed-cache/newspack-object-cache.php:222:    public static function flush_coverage_cache( $post_id ) {
newsroom-speed-cache/newspack-object-cache.php:233:    public static function admin_bar_cache_indicator( $wp_admin_bar ) {
newsroom-speed-cache/newsroom-speed-cache.php:56:    public static function instance() {
newsroom-speed-cache/newsroom-speed-cache.php:66:    private function __construct() {
newsroom-speed-cache/newsroom-speed-cache.php:75:    public static function has_object_cache() {
newsroom-speed-cache/newsroom-speed-cache.php:91:    public static function detect_backend() {
newsroom-speed-cache/newsroom-speed-cache.php:121:    public static function get_backend_label() {
newsroom-speed-cache/newsroom-speed-cache.php:139:    private function init_hooks() {
newsroom-speed-cache/newsroom-speed-cache.php:191:    public function cache_gate_check( $restrict, $post_id ) {
newsroom-speed-cache/newsroom-speed-cache.php:211:    public function flush_on_post_save( $post_id, $post ) {
newsroom-speed-cache/newsroom-speed-cache.php:229:    public function flush_metering( $activity ) {
newsroom-speed-cache/newsroom-speed-cache.php:243:    public function cache_lists_config( $config ) {
newsroom-speed-cache/newsroom-speed-cache.php:252:    public function flush_lists() {
newsroom-speed-cache/newsroom-speed-cache.php:260:    public function cache_placements( $placements ) {
newsroom-speed-cache/newsroom-speed-cache.php:269:    public function flush_placements() {
---HOOKS---

