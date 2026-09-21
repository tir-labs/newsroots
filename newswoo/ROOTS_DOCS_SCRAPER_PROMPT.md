# Roots.io Documentation Scraper Prompt

You are a documentation scraper and compliance auditor. Your job is to visit every URL listed below, extract the full documentation content, and produce a structured reference document that can be used to audit a WordPress/Bedrock/Acorn/Sage/Trellis project for compliance.

---

## INSTRUCTIONS

1. **Visit every URL** listed below using web_search or browser tools
2. **Extract the full documentation content** from each page — code examples, configuration options, CLI commands, API references, requirements, and compatibility notes
3. **Organize the output** by category (Acorn, Bedrock, Sage, Trellis, Blog Posts)
4. **For each page**, capture:
   - Page title and URL
   - All code examples (verbatim, with syntax highlighting)
   - Configuration options and their defaults
   - CLI commands
   - Requirements and compatibility notes
   - Links to related pages
5. **Produce a single markdown file** organized by category with a table of contents
6. **Flag any deprecation notices** or version-specific requirements
7. **Cross-reference** related docs (e.g., Acorn routing references controllers, Bedrock composer references WP Packages)

---

## CATEGORY 1: ACORN (Roots Acorn — Laravel in WordPress)

Acorn is the bridge between Laravel and WordPress. It provides Eloquent, Blade, routing, queues, caching, and Artisan inside WordPress.

### Core
- [ ] https://roots.io/acorn/docs/routing/
- [ ] https://roots.io/acorn/docs/controllers-middleware-kernel/
- [ ] https://roots.io/acorn/docs/eloquent-models/
- [ ] https://roots.io/acorn/docs/error-handling/
- [ ] https://roots.io/acorn/docs/logging/
- [ ] https://roots.io/acorn/docs/rendering-blade-views/

### Data & Storage
- [ ] https://roots.io/acorn/docs/eloquent-models/
- [ ] https://roots.io/acorn/docs/laravel-cache-alternative-to-wordpress-transients/
- [ ] https://roots.io/acorn/docs/creating-and-running-laravel-migrations/
- [ ] https://roots.io/acorn/docs/laravel-redis-configuration/

### Async & CLI
- [ ] https://roots.io/acorn/docs/creating-and-processing-laravel-queues/
- [ ] https://roots.io/acorn/docs/creating-wp-cli-commands-with-artisan-console/
- [ ] https://roots.io/acorn/docs/wp-cli/

### Packages & Extensions
- [ ] https://roots.io/acorn/docs/package-development/
- [ ] https://roots.io/acorn/docs/available-packages/

### Frontend
- [ ] https://roots.io/acorn/docs/rendering-blade-views/
- [ ] https://roots.io/acorn/docs/using-livewire-with-wordpress/

### Also scrape the Acorn docs index for any sub-links not listed above:
- [ ] https://roots.io/acorn/docs/

---

## CATEGORY 2: BEDROCK (Composer-managed WordPress)

Bedrock is the WordPress project structure. It uses Composer for all dependencies, environment variables for configuration, and a clean folder layout.

### Setup & Configuration
- [ ] https://roots.io/bedrock/docs/installation/
- [ ] https://roots.io/bedrock/docs/configuration/
- [ ] https://roots.io/bedrock/docs/environment-variables/
- [ ] https://roots.io/bedrock/docs/folder-structure/
- [ ] https://roots.io/bedrock/docs/compatibility/

### Composer & Dependencies
- [ ] https://roots.io/bedrock/docs/composer/
- [ ] https://roots.io/bedrock/docs/mu-plugin-autoloader/

### Deployment & Server
- [ ] https://roots.io/bedrock/docs/deployment/
- [ ] https://roots.io/bedrock/docs/server-configuration/
- [ ] https://roots.io/bedrock/docs/local-development/
- [ ] https://roots.io/bedrock/docs/bedrock-with-ddev/

### Testing & Maintenance
- [ ] https://roots.io/bedrock/docs/testing/
- [ ] https://roots.io/bedrock/docs/wp-cron/

### Also scrape the Bedrock docs index for any sub-links not listed above:
- [ ] https://roots.io/bedrock/docs/

---

## CATEGORY 3: SAGE (Blade-based WordPress theme)

Sage is the WordPress theme framework. It uses Blade templates, Tailwind CSS, Vite, and component-based architecture.

### Setup & Configuration
- [ ] https://roots.io/sage/docs/configuration/
- [ ] https://roots.io/sage/docs/structure/
- [ ] https://roots.io/sage/docs/functionality/
- [ ] https://roots.io/sage/docs/compatibility/
- [ ] https://roots.io/sage/docs/deployment/

### Templates & Components
- [ ] https://roots.io/sage/docs/theme-templates/
- [ ] https://roots.io/sage/docs/blade-templates/
- [ ] https://roots.io/sage/docs/composers/
- [ ] https://roots.io/sage/docs/components/

### Frontend & Assets
- [ ] https://roots.io/sage/docs/compiling-assets/
- [ ] https://roots.io/sage/docs/tailwind-css/
- [ ] https://roots.io/sage/docs/sass/
- [ ] https://roots.io/sage/docs/fonts-setup/
- [ ] https://roots.io/sage/docs/use-blade-icons/
- [ ] https://roots.io/sage/docs/bootstrap/

### WordPress Integration
- [ ] https://roots.io/sage/docs/gutenberg/
- [ ] https://roots.io/sage/docs/woocommerce/
- [ ] https://roots.io/sage/docs/localization/

### Also scrape the Sage docs index for any sub-links not listed above:
- [ ] https://roots.io/sage/docs/

---

## CATEGORY 4: TRELLIS (Ansible-based server provisioning)

Trellis provisions and deploys WordPress servers using Ansible.

### Setup & Installation
- [ ] https://roots.io/trellis/docs/installation/
- [ ] https://roots.io/trellis/docs/cli/
- [ ] https://roots.io/trellis/docs/wordpress-sites/

### Development
- [ ] https://roots.io/trellis/docs/local-development/
- [ ] https://roots.io/trellis/docs/bedrock-with-ddev/ (if referenced)

### Server & Deployment
- [ ] https://roots.io/trellis/docs/remote-server-setup/
- [ ] https://roots.io/trellis/docs/deployments/
- [ ] https://roots.io/trellis/docs/deploy-with-github-actions/
- [ ] https://roots.io/trellis/docs/cron-jobs/

### Configuration
- [ ] https://roots.io/trellis/docs/configuring-php/
- [ ] https://roots.io/trellis/docs/multisite/
- [ ] https://roots.io/trellis/docs/multiple-sites/
- [ ] https://roots.io/trellis/docs/existing-projects/

### Security
- [ ] https://roots.io/trellis/docs/security/
- [ ] https://roots.io/trellis/docs/vault/
- [ ] https://roots.io/trellis/docs/ssh-keys/
- [ ] https://roots.io/trellis/docs/ssl/
- [ ] https://roots.io/trellis/docs/passwords/

### Database & Logging
- [ ] https://roots.io/trellis/docs/database-access/
- [ ] https://roots.io/trellis/docs/server-logs/
- [ ] https://roots.io/trellis/docs/debugging-php/
- [ ] https://roots.io/trellis/docs/troubleshooting/

### Advanced
- [ ] https://roots.io/trellis/docs/ansible/
- [ ] https://roots.io/trellis/docs/composer-authentication/
- [ ] https://roots.io/trellis/docs/mail/
- [ ] https://roots.io/trellis/docs/nginx-includes/
- [ ] https://roots.io/trellis/docs/fastcgi-caching/
- [ ] https://roots.io/trellis/docs/redis/
- [ ] https://roots.io/trellis/docs/sage-integration/
- [ ] https://roots.io/trellis/docs/python/
- [ ] https://roots.io/trellis/docs/user-contributed-extensions/
- [ ] https://roots.io/trellis/docs/install-wordpress-language-files/

### Also scrape the Trellis docs index for any sub-links not listed above:
- [ ] https://roots.io/trellis/docs/

---

## CATEGORY 5: BLOG POSTS & ANNOUNCEMENTS

These are important for understanding current best practices, deprecations, and new features.

- [ ] https://roots.io/disable-woocommerce-telemetry/
- [ ] https://roots.io/some-seo-plugins-claim-markdown-for-ai-but-ignore-the-accept-header/
- [ ] https://roots.io/serve-your-wordpress-posts-as-markdown/
- [ ] https://roots.io/roots-vite-plugin-now-supports-theme-json-partials/
- [ ] https://roots.io/millicache-redis-backed-full-page-caching-for-wordpress/
- [ ] https://roots.io/wp-sec-adv-wordpress-security-advisories-for-composer/

### Also scrape recent blog posts for anything related to:
- [ ] https://roots.io/blog/ (scrape index for recent posts about Bedrock, Acorn, Sage, Trellis releases)

---

## CATEGORY 6: DISCOURSE RELEASES

Scrape the Roots Discourse releases tag for version history, breaking changes, and migration guides.

- [ ] https://discourse.roots.io/tag/releases/61

---

## CATEGORY 7: WP PACKAGES & COMPOSER STANDARDS

These are critical for understanding how WordPress core and plugins are managed as Composer dependencies.

- [ ] https://wp-packages.org/wordpress-core/
- [ ] https://wp-packages.org/ (main page, list all packages)
- [ ] https://roots.io/using-composer-with-wordpress/
- [ ] https://roots.io/wordpress-plugins-with-composer/
- [ ] https://roots.io/private-or-commercial-wordpress-plugins-as-composer-dependencies/

---

## OUTPUT FORMAT

Produce a single markdown file with this structure:

```markdown
# Roots.io Complete Documentation Reference

Generated: [DATE]

## Table of Contents
- [Acorn](#acorn)
- [Bedrock](#bedrock)
- [Sage](#sage)
- [Trellis](#trellis)
- [Blog Posts](#blog-posts)
- [Releases](#releases)
- [WP Packages & Composer](#wp-packages--composer)

---

## Acorn

### [Page Title](URL)

**Summary:** [1-2 sentence summary]

**Key Concepts:**
- [bullet points]

**Code Examples:**
```php
// verbatim code from the page
```

**Configuration:**
| Option | Default | Description |
|--------|---------|-------------|
| ... | ... | ... |

**CLI Commands:**
```bash
wp acorn ...
```

**Requirements:**
- PHP >= 8.x
- WordPress >= x.x

**Related Pages:**
- [Link to related doc](URL)

**Deprecation Notes:** [if any]

---

[repeat for every page]
```

---

## COMPLIANCE CHECKLIST

After scraping all docs, produce a compliance checklist that can be used to audit the NewsWoo project at https://github.com/Postdated/NewsWoo and the NewsRock monorepo at https://github.com/Postdated/NewsRock:

```markdown
## NewsWoo Compliance Audit

### Acorn Compliance
- [ ] Service provider registered correctly
- [ ] Eloquent models use correct table mapping
- [ ] Routes follow Acorn conventions
- [ ] Blade views render correctly
- [ ] Queue jobs use Acorn's queue system

### Bedrock Compliance
- [ ] Composer dependencies use WP Packages namespace
- [ ] Folder structure matches Bedrock layout
- [ ] Environment variables in .env
- [ ] MU-plugin autoloader configured
- [ ] Plugin installer paths correct

### Sage Compliance (if NewsDesk theme exists)
- [ ] Blade templates in correct location
- [ ] Composers registered
- [ ] Assets compiled with Vite
- [ ] Tailwind configured

### Trellis Compliance (if deployed via Trellis)
- [ ] Server provisioning matches Trellis requirements
- [ ] Deployment hooks configured
- [ ] SSL certificates managed
```

