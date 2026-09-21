# Phase 0 Inventory: newsroom-image-downloader

=== newsroom-image-downloader ===
<?php
/**
 * Plugin Name: Newsroom Image Downloader
 * Plugin URI:  https://newsroom.dev
 * Description: Download external images into your WordPress media library from the admin dashboard. Scans posts for external image URLs and imports them locally. Web-based wrapper around the image download logic.
 * Version:     1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author:      Newsroom
 * License:     GPL-2.0-or-later
 * Text Domain: newsroom-image-downloader
 *
 * @package Newsroom_Image_Downloader
 */

namespace Newsroom_Image_Downloader;

defined( 'ABSPATH' ) || exit;

class Downloader {

    const VERSION = '1.0.0';
    const BATCH_SIZE = 20;
    const OPTION_LAST_SCAN = 'newsroom_img_downloader_last_scan';
    const OPTION_SCAN_RESULTS = 'newsroom_img_downloader_scan_results';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'wp_ajax_newsroom_img_scan', [ __CLASS__, 'ajax_scan' ] );
---FILES---
newsroom-image-downloader/assets/admin.css
newsroom-image-downloader/assets/admin.js
newsroom-image-downloader/newsroom-image-downloader.php
---FUNCTIONS---
newsroom-image-downloader/newsroom-image-downloader.php:27:    public static function init() {
newsroom-image-downloader/newsroom-image-downloader.php:36:    public static function add_menu() {
newsroom-image-downloader/newsroom-image-downloader.php:46:    public static function enqueue_assets( $hook ) {
newsroom-image-downloader/newsroom-image-downloader.php:79:    public static function ajax_scan() {
newsroom-image-downloader/newsroom-image-downloader.php:128:    public static function ajax_download() {
newsroom-image-downloader/newsroom-image-downloader.php:205:    public static function ajax_download_single() {
newsroom-image-downloader/newsroom-image-downloader.php:246:    public static function ajax_get_logs() {
newsroom-image-downloader/newsroom-image-downloader.php:258:    public static function extract_external_images( $html, $post_id = 0 ) {
newsroom-image-downloader/newsroom-image-downloader.php:318:    public static function sideload_image( $url, $post_id = 0, $dry_run = false ) {
newsroom-image-downloader/newsroom-image-downloader.php:390:    public static function is_external_url( $url ) {
newsroom-image-downloader/newsroom-image-downloader.php:399:    public static function is_absolute_url( $url ) {
newsroom-image-downloader/newsroom-image-downloader.php:403:    public static function is_valid_url( $url ) {
newsroom-image-downloader/newsroom-image-downloader.php:407:    public static function get_existing_attachment( $url ) {
newsroom-image-downloader/newsroom-image-downloader.php:422:    public static function render_page() {
---HOOKS---

