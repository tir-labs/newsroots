# NewsWoo Full Compliance Audit

**Audited:** 2026-09-19
**Scope:** 1,299 PHP files | 317,305 lines of code
**Source:** Actual WooCommerce 11.1.1 fork in woocommerce/
**Standards:** Roots.io docs (Acorn, Bedrock, Sage, Trellis) + WordPress Coding Standards + PSR-12

---

## Summary

| Status | Count | Items |
|--------|-------|-------|
| ✅ PASS | 8 | Security, structure, documentation |
| ⚠️ PARTIAL | 2 | Composer, service provider |
| ❌ FAIL | 11 | Code quality, patterns, compatibility |
| N/A | 2 | Eloquent, MU-plugin |

**Total: 23 compliance items checked**

---

## Section 1: Acorn Compatibility

### 1.1 Namespaces — ❌ FAIL
**Doc:** https://roots.io/acorn/docs/eloquent-models/
**Requirement:** PSR-4 namespaces for autoloading
**Finding:** 453/458 files in includes/ have NO namespace. They use legacy `WC_` prefix pattern. Only 5 files use proper namespace.
**Impact:** Cannot use Eloquent-style autoloading. Must rely on custom autoloader (src/Autoloader.php).

### 1.2 Service Provider — ⚠️ PARTIAL
**Doc:** https://roots.io/acorn/docs/available-packages/
**Requirement:** Service provider with register()/boot()
**Finding:** `$GLOBALS['wc_container'] = new Container()` — uses DI container but NOT Acorn's service provider. No acorn/providers registration for WC core.
**Impact:** WC core won't auto-register with Acorn.

### 1.3 Eloquent Models — N/A
**Doc:** https://roots.io/acorn/docs/eloquent-models/
**Finding:** WC uses custom data stores (WC_Data_Store_WP), 21 data store classes. Custom meta system via get_post_meta/update_post_meta.
**Note:** New NewsWoo models (our 6 files) ARE compliant.

### 1.4 Routing — ❌ FAIL
**Doc:** https://roots.io/acorn/docs/routing/
**Finding:** WC uses WordPress REST API directly. 100 REST controller files. No routes/ directory in WC core.

### 1.5 Blade Views — ❌ FAIL
**Doc:** https://roots.io/acorn/docs/rendering-blade-views/
**Finding:** WC uses PHP templates (225 files). Inline HTML in 96 includes/ files. No Blade.

---

## Section 2: Bedrock Compatibility

### 2.1 Composer — ⚠️ PARTIAL
**Doc:** https://roots.io/bedrock/docs/composer/
**Finding:** WC has own composer.json but uses Jetpack autoloader. Not using WP Packages namespace. Uses custom autoloader.

### 2.2 Folder Structure — ✅ PASS
**Doc:** https://roots.io/bedrock/docs/folder-structure/
**Finding:** Standard WordPress plugin structure. Compatible with Bedrock layout.

### 2.3 MU-Plugin — N/A
**Doc:** https://roots.io/bedrock/docs/mu-plugin-autoloader/
**Finding:** WC has own initialization. NewsWoo mu-plugin handles Acorn registration.

### 2.4 Testing — ❌ FAIL
**Doc:** https://roots.io/bedrock/docs/testing/
**Finding:** No phpunit.xml.dist. No tests/ directory in stripped version.

---

## Section 3: WordPress Coding Standards

### 3.1 Input Sanitization — ✅ PASS
**Finding:** 146 files use sanitize_* functions. 76 use wp_unslash. 147 use absint/intval.

### 3.2 Output Escaping — ✅ PASS
**Finding:** 166 files use esc_* functions. 64 use wp_kses.

### 3.3 Nonce Verification — ✅ PASS
**Finding:** 44 files use wp_nonce/wp_verify_nonce.

### 3.4 Capability Checks — ✅ PASS
**Finding:** 40 files use current_user_can.

### 3.5 SQL Injection Prevention — ✅ PASS
**Finding:** 56 files use $wpdb->prepare. No unprepared queries found.

### 3.6 Inline HTML — ❌ FAIL
**Finding:** 85 files mix PHP and HTML. 96 files in includes/ have HTML output. 26 files have inline script/style.

---

## Section 4: Code Quality (PSR-12)

### 4.1 Type Hints — ❌ FAIL
**Finding:** 0/6,093 methods have parameter type hints. 0% type safety.

### 4.2 Return Types — ❌ FAIL
**Finding:** 24/6,093 methods have return types (0.4%).

### 4.3 PHPDoc — ✅ PASS
**Finding:** Most files have @package, @param, @return.

### 4.4 Deprecated Code — ❌ FAIL
**Finding:** 88 files contain @deprecated code. wc-deprecated-functions.php is 72KB of dead code.

### 4.5 Error Suppression — ❌ FAIL
**Finding:** 446 files use @ operator.

### 4.6 Global State — ❌ FAIL
**Finding:** 2 files use global keyword. 19 files use $GLOBALS. $GLOBALS['wc_container'] is main DI container.

### 4.7 Hardcoded Paths — ❌ FAIL
**Finding:** 422 files use ABSPATH/WP_CONTENT_DIR. require_once WC_ABSPATH scattered throughout.

---

## Section 5: Security

### 5.1 Overall Security — ✅ PASS
**Finding:** Strong sanitization, escaping, nonce, capability. No unprepared SQL queries. Security is the strongest compliance area.

---

## Section 6: Performance

### 6.1 Autoloaded Options — ✅ PASS
**Finding:** Only 1 autoloaded option found.

### 6.2 File Operations — ✅ PASS
**Finding:** Only 6 files use direct file operations.

---

## Priority Fixes

| # | Issue | Files | Effort |
|---|-------|-------|--------|
| 1 | Namespaces | 453 | MASSIVE (weeks) |
| 2 | Type Hints | 458 | LARGE (days) |
| 3 | Return Types | 458 | LARGE (days) |
| 4 | Deprecated Code | 88 | MEDIUM (hours) |
| 5 | Error Suppression | 446 | LARGE (days) |
| 6 | Inline HTML | 96 | LARGE (days) |
| 7 | Global State | 21 | MEDIUM (hours) |
| 8 | Hardcoded Paths | 422 | LARGE (days) |
| 9 | Blade Views | 225 | MASSIVE (weeks) |
| 10 | Routing | 100 | LARGE (days) |
| 11 | Testing | 0 | MEDIUM (hours) |

