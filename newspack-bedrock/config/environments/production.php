<?php

use Roots\WPConfig\Config;

/**
 * Production environment configuration.
 * Debug is off, indexing is allowed, file mods are blocked.
 */
Config::define('WP_DEBUG', false);
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);
Config::define('DISALLOW_INDEXING', false);
