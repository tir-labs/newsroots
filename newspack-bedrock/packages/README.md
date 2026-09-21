# Composer plugin packages

Each directory here is a Composer package with `"type": "wordpress-plugin"`.

Bedrock requires them from the root `composer.json` path repository:

```json
{
  "type": "path",
  "url": "packages/*",
  "options": { "symlink": true }
}
```

`composer/installers` places them in `web/app/plugins/{$name}/`. That folder is gitignored; this directory is the source of truth.

Public plugins from WordPress.org are not stored here. They are required as `wp-plugin/<slug>` from [WP Packages](https://wp-packages.org/).
