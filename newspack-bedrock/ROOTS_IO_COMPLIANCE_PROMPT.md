# Roots.io Ecosystem Full Compliance Audit Prompt

Use this prompt with any AI coding agent to ensure a codebase is fully compliant with the entire Roots.io ecosystem.

---

## Instructions for the Agent

You are performing a **full ecosystem compliance audit** of a WordPress project built on the Roots.io stack. Your job is to:

1. **Scrape every URL listed below** using `curl` or a web fetch tool.
2. **Read the actual documentation content** from each page (extract the markdown or main body text).
3. **Compare the codebase against every rule, convention, and requirement** found in the docs.
4. **Produce a compliance matrix** with PASS / FAIL / UNVERIFIED status for every requirement.
5. **Fix any FAILs** in the codebase so the project passes all checks.
6. **Produce a final report** at `reports/roots-compliance-report.md`.

Do not rely on pre-trained knowledge. You must read every URL.

---

## Project Context

- **Repo**: [Postdated/Newspack](https://github.com/Postdated/Newspack) (standalone) and [Postdated/NewsRock](https://github.com/Postdated/NewsRock) (monorepo, `newspack-bedrock/` subfolder)
- **Stack**: Roots Bedrock + Acorn + Sage (Foxiz theme ported to Sage 11 Blade)
- **Database**: MariaDB via Trellis / DDEV
- **Object Cache**: Memcached (Redis is NOT used)
- **PHP**: >= 8.3
- **WordPress**: `roots/wordpress` via WP Packages
- **Composer**: `composer/installers` with `{$name}` installer paths
- **Eloquent**: Used for models (Post, User, PostMeta, Term)
- **Livewire**: Required for reactive Sage Blade components
- **Plugin packages**: Path repos in `packages/` with `type: wordpress-plugin`

---

## URLs to Scrape and Audit

### Acorn Documentation

Read each URL and extract every requirement. Check the codebase for compliance.

| # | Topic | URL |
|---|-------|-----|
| 1 | Installation | https://roots.io/acorn/docs/installation/ |
| 2 | Routing | https://roots.io/acorn/docs/routing/ |
| 3 | Controllers, Middleware & Kernel | https://roots.io/acorn/docs/controllers-middleware-kernel/ |
| 4 | Eloquent Models | https://roots.io/acorn/docs/eloquent-models/ |
| 5 | Error Handling | https://roots.io/acorn/docs/error-handling/ |
| 6 | Logging | https://roots.io/acorn/docs/logging/ |
| 7 | WP-CLI | https://roots.io/acorn/docs/wp-cli/ |
| 8 | Package Development | https://roots.io/acorn/docs/package-development/ |
| 9 | Available Packages | https://roots.io/acorn/docs/available-packages/ |
| 10 | Rendering Blade Views | https://roots.io/acorn/docs/rendering-blade-views/ |
| 11 | Laravel Cache (WordPress Transients alternative) | https://roots.io/acorn/docs/laravel-cache-alternative-to-wordpress-transients/ |
| 12 | Using Livewire with WordPress | https://roots.io/acorn/docs/using-livewire-with-wordpress/ |
| 13 | Creating and Running Laravel Migrations | https://roots.io/acorn/docs/creating-and-running-laravel-migrations/ |
| 14 | Creating and Processing Laravel Queues | https://roots.io/acorn/docs/creating-and-processing-laravel-queues/ |
| 15 | Creating WP-CLI Commands with Artisan Console | https://roots.io/acorn/docs/creating-wp-cli-commands-with-artisan-console/ |
| 16 | Laravel Redis Configuration | https://roots.io/acorn/docs/laravel-redis-configuration/ |

### Bedrock Documentation

| # | Topic | URL |
|---|-------|-----|
| 17 | Installation | https://roots.io/bedrock/docs/installation/ |
| 18 | Compatibility | https://roots.io/bedrock/docs/compatibility/ |
| 19 | Configuration | https://roots.io/bedrock/docs/configuration/ |
| 20 | Deployment | https://roots.io/bedrock/docs/deployment/ |
| 21 | Composer | https://roots.io/bedrock/docs/composer/ |
| 22 | Environment Variables | https://roots.io/bedrock/docs/environment-variables/ |
| 23 | Folder Structure | https://roots.io/bedrock/docs/folder-structure/ |
| 24 | Server Configuration | https://roots.io/bedrock/docs/server-configuration/ |
| 25 | Local Development | https://roots.io/bedrock/docs/local-development/ |
| 26 | Testing | https://roots.io/bedrock/docs/testing/ |
| 27 | MU-Plugin Autoloader | https://roots.io/bedrock/docs/mu-plugin-autoloader/ |
| 28 | WP-Cron | https://roots.io/bedrock/docs/wp-cron/ |
| 29 | Bedrock with DDEV | https://roots.io/bedrock/docs/bedrock-with-ddev/ |
| 30 | Private Plugins as Composer Dependencies | https://roots.io/bedrock/docs/private-or-commercial-wordpress-plugins-as-composer-dependencies/ |
| 31 | Patching Plugins with Composer | https://roots.io/bedrock/docs/patching-wordpress-plugins-with-composer/ |

### Sage Documentation

| # | Topic | URL |
|---|-------|-----|
| 32 | Configuration | https://roots.io/sage/docs/configuration/ |
| 33 | Structure | https://roots.io/sage/docs/structure/ |
| 34 | Functionality | https://roots.io/sage/docs/functionality/ |
| 35 | Deployment | https://roots.io/sage/docs/deployment/ |
| 36 | Compatibility | https://roots.io/sage/docs/compatibility/ |
| 37 | Compiling Assets | https://roots.io/sage/docs/compiling-assets/ |
| 38 | Theme Templates | https://roots.io/sage/docs/theme-templates/ |
| 39 | Blade Templates | https://roots.io/sage/docs/blade-templates/ |
| 40 | Gutenberg | https://roots.io/sage/docs/gutenberg/ |
| 41 | Tailwind CSS | https://roots.io/sage/docs/tailwind-css/ |
| 42 | Composers | https://roots.io/sage/docs/composers/ |
| 43 | Components | https://roots.io/sage/docs/components/ |
| 44 | Localization | https://roots.io/sage/docs/localization/ |
| 45 | WooCommerce | https://roots.io/sage/docs/woocommerce/ |
| 46 | Sass | https://roots.io/sage/docs/sass/ |
| 47 | Fonts Setup | https://roots.io/sage/docs/fonts-setup/ |
| 48 | Bootstrap | https://roots.io/sage/docs/bootstrap/ |
| 49 | Blade Icons | https://roots.io/sage/docs/use-blade-icons/ |

### Trellis Documentation

| # | Topic | URL |
|---|-------|-----|
| 50 | Installation | https://roots.io/trellis/docs/installation/ |
| 51 | CLI | https://roots.io/trellis/docs/cli/ |
| 52 | WordPress Sites | https://roots.io/trellis/docs/wordpress-sites/ |
| 53 | Local Development | https://roots.io/trellis/docs/local-development/ |
| 54 | Remote Server Setup | https://roots.io/trellis/docs/remote-server-setup/ |
| 55 | Deployments | https://roots.io/trellis/docs/deployments/ |
| 56 | Cron Jobs | https://roots.io/trellis/docs/cron-jobs/ |
| 57 | Passwords | https://roots.io/trellis/docs/passwords/ |
| 58 | Database Access | https://roots.io/trellis/docs/database-access/ |
| 59 | Server Logs | https://roots.io/trellis/docs/server-logs/ |
| 60 | Configuring PHP | https://roots.io/trellis/docs/configuring-php/ |
| 61 | Multisite | https://roots.io/trellis/docs/multisite/ |
| 62 | Existing Projects | https://roots.io/trellis/docs/existing-projects/ |
| 63 | Security | https://roots.io/trellis/docs/security/ |
| 64 | Vault | https://roots.io/trellis/docs/vault/ |
| 65 | SSH Keys | https://roots.io/trellis/docs/ssh-keys/ |
| 66 | SSL | https://roots.io/trellis/docs/ssl/ |
| 67 | Ansible | https://roots.io/trellis/docs/ansible/ |
| 68 | Composer Authentication | https://roots.io/trellis/docs/composer-authentication/ |
| 69 | Mail | https://roots.io/trellis/docs/mail/ |
| 70 | Nginx Includes | https://roots.io/trellis/docs/nginx-includes/ |
| 71 | FastCGI Caching | https://roots.io/trellis/docs/fastcgi-caching/ |
| 72 | Redis | https://roots.io/trellis/docs/redis/ |
| 73 | Sage Integration | https://roots.io/trellis/docs/sage-integration/ |
| 74 | Python | https://roots.io/trellis/docs/python/ |
| 75 | Troubleshooting | https://roots.io/trellis/docs/troubleshooting/ |
| 76 | Debugging PHP | https://roots.io/trellis/docs/debugging-php/ |
| 77 | User-Contributed Extensions | https://roots.io/trellis/docs/user-contributed-extensions/ |
| 78 | Deploy with GitHub Actions | https://roots.io/trellis/docs/deploy-with-github-actions/ |
| 79 | Install WordPress Language Files | https://roots.io/trellis/docs/install-wordpress-language-files/ |
| 80 | Multiple Sites | https://roots.io/trellis/docs/multiple-sites/ |

### Additional Roots.io Resources

| # | Topic | URL |
|---|-------|-----|
| 81 | WP Packages Core | https://wp-packages.org/wordpress-core |
| 82 | Using Composer with WordPress | https://roots.io/using-composer-with-wordpress/ |
| 83 | WordPress Plugins with Composer | https://roots.io/wordpress-plugins-with-composer/ |
| 84 | Disable WooCommerce Telemetry | https://roots.io/disable-woocommerce-telemetry/ |
| 85 | SEO Plugins Markdown for AI | https://roots.io/some-seo-plugins-claim-markdown-for-ai-but-ignore-the-accept-header/ |
| 86 | Serve Posts as Markdown | https://roots.io/serve-your-wordpress-posts-as-markdown/ |
| 87 | Vite Plugin Theme JSON Partials | https://roots.io/roots-vite-plugin-now-supports-theme-json-partials/ |
| 88 | Millicache Redis Full Page Caching | https://roots.io/millicache-redis-backed-full-page-caching-for-wordpress/ |
| 89 | WP-SEC-ADV Security Advisories | https://roots.io/wp-sec-adv-wordpress-security-advisories-for-composer/ |

### Discourse Releases

| # | Topic | URL |
|---|-------|-----|
| 90 | Roots Discourse Releases | https://discourse.roots.io/tag/releases/61 |

---

## How to Fetch Each URL

Use the `.md` suffix for roots.io doc pages to get clean markdown:

```bash
curl -fsSL 'https://roots.io/acorn/docs/installation.md'
curl -fsSL 'https://roots.io/bedrock/docs/composer.md'
curl -fsSL 'https://roots.io/sage/docs/blade-templates.md'
curl -fsSL 'https://roots.io/trellis/docs/deployments.md'
```

For pages that do not have a `.md` endpoint, fetch the HTML and extract the `<main>` body:

```bash
curl -fsSL 'https://roots.io/acorn/docs/routing/' | python3 -c "import sys,re; t=sys.stdin.read(); m=re.search(r'<main.*?>(.*)</main>', t, re.S); print(re.sub(r'<[^>]+>', '', m.group(1)).strip() if m else '')"
```

For WP Packages:

```bash
curl -fsSL 'https://wp-packages.org/wordpress-core.md'
```

For Discourse:

```bash
curl -fsSL 'https://discourse.roots.io/tag/releases/61.json'
```

---

## Compliance Matrix Template

For each URL, create rows like:

| # | Requirement | Source URL | Status | Evidence |
|---|-------------|-----------|--------|----------|
| 1 | `composer.json` uses `roots/wordpress` | bedrock/docs/composer.md | PASS | `composer.json` line 25 |
| 2 | Eloquent models set `$primaryKey = 'ID'` | acorn/docs/eloquent-models.md | PASS | `packages/*/src/Models/Post.php` |
| 3 | Acorn booted with `withRouting(wordpress: true)` | acorn/docs/using-livewire-with-wordpress.md | PASS | `web/app/mu-plugins/acorn-bootloader.php` |

Status must be one of:
- **PASS** — Verified with evidence (file path + line number)
- **FAIL** — Violation found; fix it
- **UNVERIFIED** — Cannot be confirmed (explain why)

---

## Key Compliance Checks

These are the critical checks derived from the Roots.io docs:

### Bedrock
- [ ] `composer.json` uses `roots/wordpress` (WP Packages)
- [ ] `wordpress-install-dir` is `web/wp`
- [ ] Installer paths use `{$name}` token (not `${name}`)
- [ ] WP Packages repository registered: `https://repo.wp-packages.org`
- [ ] `web/wp-config.php` only loads autoload + `config/application.php` + `wp-settings.php`
- [ ] `config/application.php` uses `Roots\WPConfig\Config` and `Env\env`
- [ ] Environment files exist in `config/environments/`
- [ ] `.env.example` has all required keys
- [ ] `vendor/` and `web/wp/` are gitignored
- [ ] Plugins are gitignored (`web/app/plugins/*`) with `.gitkeep` exception
- [ ] No Redis (Memcached only for this project)

### Acorn
- [ ] `roots/acorn` is required in `composer.json`
- [ ] Acorn MU-plugin bootloader exists and calls `Application::configure()->withRouting(wordpress: true)->boot()`
- [ ] `post-autoload-dump` includes `Roots\Acorn\ComposerScripts::postAutoloadDump`
- [ ] Eloquent models use `Illuminate\Database\Eloquent\Model`
- [ ] Models for WP tables set `$table`, `$primaryKey = 'ID'`, `$timestamps = false`
- [ ] Cache uses `Illuminate\Support\Facades\Cache` (not raw transients)
- [ ] Blade views use `plugin_dir_url()` for assets (not hardcoded `/wp-content/`)
- [ ] Livewire components follow `App\Livewire\` namespace
- [ ] Livewire styles/scripts enqueued via `@livewireStyles` / `@livewireScripts`
- [ ] Error handling uses Laravel exception handler patterns
- [ ] Logging uses `Illuminate\Support\Facades\Log`

### Sage
- [ ] Theme uses Blade templates
- [ ] Composers use `App\View\Composers\` namespace
- [ ] Components use `App\View\Components\` namespace
- [ ] Assets compiled with Bud.js or Vite
- [ ] Gutenberg blocks output standard `block.json` markup
- [ ] WooCommerce integration follows Sage docs if WooCommerce is used

### Composer
- [ ] All plugin packages have `type: wordpress-plugin`
- [ ] All plugin packages have `composer/installers` as dependency
- [ ] All plugin packages declare `php >= 8.3`
- [ ] Root `composer.json` has `optimize-autoloader: true`
- [ ] Root `composer.json` has `preferred-install: dist`
- [ ] Root `composer.json` has `sort-packages: true`
- [ ] `allow-plugins` lists `composer/installers`, `roots/wordpress-core-installer`
- [ ] Public WP.org plugins use `wp-plugin/` namespace
- [ ] No duplicate package declarations

### Security
- [ ] `DISALLOW_FILE_EDIT` is `true` in production
- [ ] `DISALLOW_FILE_MODS` is `true` in production
- [ ] Nonce verification on all form submissions and AJAX
- [ ] Capability checks on all privileged operations
- [ ] Prepared statements for all DB queries
- [ ] No `eval()`, `unserialize()`, `extract()`
- [ ] All outputs escaped per context

### Plugin-Specific
- [ ] Discord Bot API uses Eloquent models (not `WP_Query`)
- [ ] Discord Bot API uses Laravel Cache (not raw transients)
- [ ] Newspack Local ESPs has `Plugin` class with `init()`
- [ ] Newspack Bedrock Pack lists all companion plugins in catalog
- [ ] Slim SEO uses WordPress AI plugin (not vendor API keys)
- [ ] Slim SEO uses `edit_posts` / `edit_others_posts` capabilities
- [ ] OneSignal is a Composer `wordpress-plugin` package
- [ ] Prevent Direct Access is a Composer `wordpress-plugin` package
- [ ] Flux Media Optimizer external API is nulled (local-only)

---

## Output

Write the following files:

1. `reports/roots-compliance-report.md` — Full compliance matrix with every URL checked
2. `reports/roots-compliance-fixes.md` — List of every fix applied
3. Updated code files — Fix any FAILs found

---

## Notes

- Do NOT use Redis. This project uses Memcached only.
- Do NOT rewrite Automattic Newspack into Laravel apps. Acorn is the Laravel layer for this Bedrock site.
- Foxiz is the theme (not Sage), but it must be compatible with Sage patterns.
- Gutenberg blocks must output standard block markup for Sage Blade.
- All plugins must work when WordPress core is at `web/wp/`.
- All plugins must work with Bedrock's `web/app/plugins/` directory.
- Plugin CSS/JS must use `wp_enqueue_*` and `plugin_dir_url()` so assets resolve under `/app/` not `/wp-content/`.

