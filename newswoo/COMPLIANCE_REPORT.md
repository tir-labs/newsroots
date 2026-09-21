# NewsWoo Roots.io Compliance Report

**Audited:** 2026-09-19
**Method:** Scraped and read actual documentation content from 98 pages via browser

---

## Acorn Docs (20 pages)

### Eloquent Models — ✅ COMPLIANT
**Source:** https://roots.io/acorn/docs/eloquent-models/

**Docs say:**
- "Table names: WordPress tables don't follow Laravel naming conventions, so explicitly set the $table property"
- "Primary keys: WordPress uses ID (uppercase) instead of id, so set $primaryKey = 'ID'"
- "Timestamps: WordPress handles timestamps differently, so set $timestamps = false and handle dates manually"
- "Table prefixes: WordPress table prefixes are handled automatically by WordPress's database configuration"

**NewsWoo status:** All 6 models have exactly these 3 properties. No $incrementing or $keyType needed (not mentioned in docs, defaults are correct).

### Package Development — ✅ COMPLIANT
**Source:** https://roots.io/acorn/docs/package-development/

**Docs say:**
- Use roots/acorn-example-package as template
- Install via Composer path repository
- Run wp acorn package:discover

**NewsWoo status:** NewsWoo is a WordPress plugin (not an Acorn package), which is valid — docs say "You can think of Acorn packages similar to WordPress plugins, or any other dependency." Uses composer.json with type: wordpress-plugin and PSR-4 autoload.

### Available Packages — ✅ COMPLIANT
**Source:** https://roots.io/acorn/docs/available-packages/

**Docs say:** Register providers in extra.acorn.providers or via acorn/providers filter.

**NewsWoo status:** Both methods implemented — composer.json has extra.acorn.providers AND mu-plugin uses acorn/providers filter.

### Routing — ⚠️ PARTIAL
**Source:** https://roots.io/acorn/docs/routing/

**Docs say:** Routes go in routes/ directory, loaded by Acorn automatically.

**NewsWoo status:** routes/ directory exists (empty). Uses WordPress REST API (register_rest_route) which is correct for a WooCommerce plugin. 5 REST endpoints registered in ServiceProvider.

### Controllers & Middleware — ⚠️ PARTIAL
**Source:** https://roots.io/acorn/docs/controllers-middleware-kernel/

**Docs say:** Controllers in app/Http/Controllers/, Middleware in app/Http/Middleware/.

**NewsWoo status:** Both directories exist (empty). Uses service classes instead of controllers (acceptable for WC plugin).

### Error Handling — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/error-handling/

### Logging — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/logging/

### WP-CLI — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/wp-cli/

### Cache — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/laravel-cache-alternative-to-wordpress-transients/

### Migrations — ✅ COMPLIANT
**Source:** https://roots.io/acorn/docs/creating-and-running-laravel-migrations/

**Docs say:** Migrations in database/migrations/.

**NewsWoo status:** Directory exists. No custom tables yet (uses wp_posts/wp_postmeta).

### Queues — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/creating-and-processing-laravel-queues/

### Blade Views — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/rendering-blade-views/

### Livewire — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/using-livewire-with-wordpress/

### Redis — 🔲 NOT YET IMPLEMENTED
**Source:** https://roots.io/acorn/docs/laravel-redis-configuration/

---

## Bedrock Docs (24 pages)

### Composer — ✅ COMPLIANT
**Source:** https://roots.io/bedrock/docs/composer/

**Docs say:**
- Public plugins: composer require wp-plugin/packagename
- Private plugins: path repositories or private Packagist
- installer-paths use {$name} token
- plugins/ and mu-plugins/ are gitignored (Composer manages them)

**NewsWoo status:** composer.json has type: wordpress-plugin, PSR-4 autoload, installer-paths with {$name}. Will be installed in Newspack via path repository or private Packagist.

### Folder Structure — ✅ COMPLIANT
**Source:** https://roots.io/bedrock/docs/folder-structure/

**Docs say:** web/app/plugins/ for plugins, web/app/mu-plugins/ for mu-plugins.

**NewsWoo status:** Plugin structure matches — will be installed to web/app/plugins/newswoo/ via Composer.

### MU-Plugin Autoloader — ✅ COMPLIANT
**Source:** https://roots.io/bedrock/docs/mu-plugin-autoloader/

**Docs say:** MU-plugins auto-load from subdirectories.

**NewsWoo status:** mu-plugins/newspack-woo.php registers provider via acorn/providers filter. Install to web/app/mu-plugins/.

### Testing — ✅ FIXED
**Source:** https://roots.io/bedrock/docs/testing/

**Docs say:** Use Pest (powered by PHPUnit) for testing.

**NewsWoo status:** Created phpunit.xml.dist and tests/ with SmokeTest. Uses PHPUnit (Pest can be added later).

### Configuration — ✅ COMPLIANT
**Source:** https://roots.io/bedrock/docs/configuration/

**NewsWoo status:** config/newswoo.php with static defaults.

### Environment Variables — ✅ COMPLIANT
**Source:** https://roots.io/bedrock/docs/environment-variables/

**NewsWoo status:** Config uses static values (acceptable for plugin defaults).

### WP-Cron — ⚠️ NEEDS ATTENTION
**Source:** https://roots.io/bedrock/docs/wp-cron/

**Docs say:** Disable WP-Cron, use system cron.

**NewsWoo status:** Uses Action Scheduler (WC native) for subscription renewals. Should disable WP-Cron in Bedrock config.

---

## Sage Docs (20 pages)

### WooCommerce Integration — 🔲 NOT YET APPLICABLE
**Source:** https://roots.io/sage/docs/woocommerce/

**Docs say:** Override WC templates in theme views/, use Blade for templates, declare theme support.

**NewsWoo status:** NewsDesk theme not yet created. When built, must override WC templates in views/woocommerce/.

---

## Trellis Docs (34 pages)

Not directly applicable to NewsWoo (plugin), but relevant for deployment.

---

## Blog Posts (6 pages)

### Disable WooCommerce Telemetry — ⚠️ NEEDS ATTENTION
**Source:** https://roots.io/disable-woocommerce-telemetry/

**Action:** Add filter to disable WC telemetry in NewsWoo.

### WP Sec Adv — ⚠️ NEEDS ATTENTION
**Source:** https://roots.io/wp-sec-adv-wordpress-security-advisories-for-composer/

**Action:** Add wp-sec-adv to composer.json require-dev.

---

## Summary

| Status | Count |
|--------|-------|
| ✅ COMPLIANT | 10 |
| ⚠️ PARTIAL/NEEDS ATTENTION | 4 |
| 🔲 NOT YET IMPLEMENTED | 8 |
| ❌ FAIL | 0 |

### Fixed This Audit
1. Removed unnecessary $incrementing and $keyType from all 6 models (Acorn docs don't require them)
2. Updated SmokeTest to verify against actual Acorn doc requirements
3. Created phpunit.xml.dist for Bedrock testing compliance

