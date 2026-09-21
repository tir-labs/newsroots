<?php
/**
 * Plugin Name: Newspack Bedrock Pack
 * Description: After Newspack is installed, prompt to activate companion plugins bundled in this Bedrock repo. Composer/Bedrock/Sage compatible.
 * Version: 1.1.0
 * Requires PHP: 8.3
 * Author: Postdated
 * License: GPL-2.0-or-later
 *
 * @package Newspack_Bedrock_Pack
 */

defined('ABSPATH') || exit;

define( 'NEWSPACK_BEDROCK_PACK_FILE', __FILE__ );

require_once __DIR__ . '/src/class-catalog.php';
require_once __DIR__ . '/src/class-installer.php';
require_once __DIR__ . '/src/class-admin.php';
require_once __DIR__ . '/src/class-plugin.php';

\Newspack_Bedrock_Pack\Plugin::init();
