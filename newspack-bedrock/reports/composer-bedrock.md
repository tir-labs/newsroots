# Composer + Bedrock conversion

Newspack Bedrock now follows the Roots Composer standards:

- WP Packages repository `https://repo.wp-packages.org`
- `roots/wordpress` 7.1.1 installed to `web/wp`
- installer-paths use `{$name}`
- public plugin `wp-plugin/co-authors-plus`
- parent theme `wp-theme/twentytwentyfive`
- custom/commercial plugins are path packages in `packages/` with `type: wordpress-plugin`
- `composer/installers` symlinks them into `web/app/plugins/`
- Acorn `post-autoload-dump` script registered
- Memcached object-cache drop-in unchanged

`composer test` (Pest 4) passes Bedrock structure tests.
