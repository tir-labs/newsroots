# NewsRock Sync Scripts

## How It Works

NewsWoo and NewsRock are separate Git repositories. The sync scripts keep `NewsRock/newswoo/` in sync with the latest NewsWoo content.

```
NewsWoo (standalone repo)          NewsRock (monorepo)
┌──────────────┐                  ┌──────────────────┐
│  src/        │  ─── sync ───→   │  newswoo/        │
│  assets/     │                  │  newspack/       │
│  README.md   │                  │  newsdesk/       │
│  ...         │                  │  scripts/        │
└──────────────┘                  └──────────────────┘
```

## Scripts

### `sync-to-monorepo.sh` (from NewsWoo)

Run from inside the NewsWoo repo to push your changes into NewsRock.

```bash
cd /path/to/NewsWoo
./scripts/sync-to-monorepo.sh
# or with a custom message:
./scripts/sync-to-monorepo.sh -m "feat: stripped shipping module"
```

### `pull-from-newswoo.sh` (from NewsRock)

Run from inside NewsRock to pull the latest NewsWoo.

```bash
cd /path/to/NewsRock
./scripts/pull-from-newswoo.sh
# or with a custom path:
./scripts/pull-from-newswoo.sh --newswoo-path /custom/path/NewsWoo
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `NEWSROCK_PATH` | `../NewsRock` | Path to NewsRock repo (for sync-to-monorepo) |
| `NEWSWOO_PATH` | `../NewsWoo` | Path to NewsWoo repo (for pull-from-newswoo) |

## Excluded Files

The sync excludes:
- `.git/` — Each repo has its own history
- `*.zip` — Plugin archives are too large for the monorepo

## Automation

To auto-sync on every NewsWoo commit, add a Git post-commit hook:

```bash
# .git/hooks/post-commit in NewsWoo
#!/bin/bash
./scripts/sync-to-monorepo.sh
```



### `pull-from-newspack.sh` (from NewsRock)

Pull the standalone [Postdated/Newspack](https://github.com/Postdated/Newspack) repo into `newspack-bedrock/`:

```bash
cd /path/to/NewsRock
./scripts/pull-from-newspack.sh
```

### `sync-to-newsrock.sh` (from Newspack)

Run from the Newspack repo after you commit there:

```bash
cd /path/to/Newspack
./scripts/sync-to-newsrock.sh
```
