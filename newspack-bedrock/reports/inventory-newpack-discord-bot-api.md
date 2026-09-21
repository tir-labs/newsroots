# Phase 0 Inventory: newpack-discord-bot-api

=== newpack-discord-bot-api ===
<?php
/**
 * Plugin Name: Newpack Discord Bot API
 * Description: Exposes a REST API for the Discord polling bot to query rolling coverage, newsletters, and events.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author: Newsroom
 *
 * @package Newpack_Discord_Bot
 */

namespace Newpack_Discord_Bot;

defined( 'ABSPATH' ) || exit;

class API {

    const REST_NAMESPACE = 'newspack-discord-bot/v1';
    const API_KEY_OPTION = 'newpack_discord_bot_api_key';

    public static function init() {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_page' ] );
    }

    /**
     * Register REST routes.
     */
    public static function register_routes() {
---FILES---
newpack-discord-bot-api/newpack-discord-bot-api.php
---FUNCTIONS---
newpack-discord-bot-api/newpack-discord-bot-api.php:22:    public static function init() {
newpack-discord-bot-api/newpack-discord-bot-api.php:30:    public static function register_routes() {
newpack-discord-bot-api/newpack-discord-bot-api.php:118:    public static function check_api_key() {
newpack-discord-bot-api/newpack-discord-bot-api.php:133:    public static function get_entries( $request ) {
newpack-discord-bot-api/newpack-discord-bot-api.php:188:    public static function get_newsletters( $request ) {
newpack-discord-bot-api/newpack-discord-bot-api.php:238:    public static function get_events( $request ) {
newpack-discord-bot-api/newpack-discord-bot-api.php:286:    public static function health_check() {
newpack-discord-bot-api/newpack-discord-bot-api.php:297:    public static function add_admin_page() {
newpack-discord-bot-api/newpack-discord-bot-api.php:310:    public static function render_admin_page() {
---HOOKS---

