# Newspack Bedrock v1.1.0

A local-first newsroom on [Roots Bedrock](https://roots.io/bedrock/) with [Acorn](https://roots.io/acorn/) (Laravel in WordPress). WordPress core, plugins, and themes are Composer dependencies from [WP Packages](https://wp-packages.org/wordpress-core). Custom and commercial plugins live in `packages/` and install into `web/app/plugins/` through `composer/installers`.

Adapted from [Automattic/newspack-workspace](https://github.com/Automattic/newspack-workspace). Layout matches [roots/bedrock](https://github.com/roots/bedrock).

[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-7.1.1-21759B?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![Bedrock](https://img.shields.io/badge/Roots-Bedrock-525ddc)](https://roots.io/bedrock/)
[![Acorn](https://img.shields.io/badge/Roots-Acorn-525ddc)](https://roots.io/acorn/)
[![WP Packages](https://img.shields.io/badge/WP%20Packages-repo.wp-packages.org-0F172A)](https://wp-packages.org/wordpress-core)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](LICENSE)

## Composer standards this repo follows

- [Using Composer with WordPress](https://roots.io/using-composer-with-wordpress/)
- [WordPress plugins with Composer](https://roots.io/wordpress-plugins-with-composer/)
- [Bedrock Composer docs](https://roots.io/bedrock/docs/composer/)
- [WP Packages WordPress core](https://wp-packages.org/wordpress-core)
- [Private / commercial plugins as Composer dependencies](https://roots.io/bedrock/docs/private-or-commercial-wordpress-plugins-as-composer-dependencies/)

Public WordPress.org plugins use the `wp-plugin/` namespace (`composer require wp-plugin/co-authors-plus`). Custom plugins are path repositories under `packages/*` with `"type": "wordpress-plugin"` and `extra.installer-name`. Installer paths use the Composer extra token documented by Bedrock, not shell-style paths.

## Folder structure

Official Bedrock: `composer.json`, `config/application.php`, `config/environments/`, `web/wp-config.php` (do not edit), `web/index.php`, `web/wp/` (core), `web/app/plugins/` (Composer-installed), `packages/` (plugin sources), `web/app/mu-plugins/`, `web/app/themes/`, `web/app/uploads/`, `web/app/object-cache.php` (Memcached).

Gutenberg blocks from `newspack-blocks` ship compiled `dist/` assets and `block.json` files. They render standard block markup and work with classic PHP themes and Sage Blade.

## Install

This product lives in the [NewsRock](https://github.com/Postdated/NewsRock) monorepo.

```bash
git clone https://github.com/Postdated/NewsRock.git
cd NewsRock/newspack-bedrock
cp .env.example .env
composer install
```

Web root is `web/`. Admin is at `/wp/wp-admin`. Set MariaDB credentials and `MEMCACHED_HOST` in `.env`. Generate salts at https://roots.io/salts.html

Foxiz (or Sage Foxiz) belongs in `web/app/themes/`. Add gitignore exceptions if the theme is not a Composer package.

## Laravel / Acorn

`roots/acorn` is required. `web/app/mu-plugins/acorn-bootloader.php` boots Acorn on `after_setup_theme` and no-ops if Sage already booted it. Plugin CSS/JS must use `wp_enqueue_*` and `plugin_dir_url()` so assets resolve under `/app/` not `/wp-content/`.

Object cache is Memcached (Trellis default). Redis is not used.

## Packaging

Package name: **Newspack Bedrock**. Tag: `newspack-bedrock-vMAJOR.MINOR.PATCH`. Current: **v1.1.0**.

## License

GPL-2.0-or-later for this project and WordPress plugins. Newspack: Automattic. Bedrock/Acorn: Roots MIT. Custom plugins: Postdated GPL-2.0-or-later.

## Companion plugin pack

Composer installs plugin files into `web/app/plugins/` (and the pack MU-plugin into `web/app/mu-plugins/`). Activating **Newspack** opens **Newspack pack**, which lists every bundled companion plugin and asks which ones to activate. Recommended plugins are pre-checked. Skip is always available.

This uses WordPress activation, not zip downloads, so it works with Bedrock `DISALLOW_FILE_MODS` in production. Gutenberg blocks emit standard block markup for Sage.

Sync this tree into the NewsRock monorepo with `../scripts/sync-to-newsrock.sh` from a standalone Newspack checkout, or `../scripts/pull-from-newspack.sh` from NewsRock.
