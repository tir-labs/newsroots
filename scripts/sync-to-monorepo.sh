#!/usr/bin/env bash
#
# sync-to-monorepo.sh — Run this FROM the NewsWoo repo to push changes into NewsRock.
#
# Usage:
#   cd /path/to/NewsWoo
#   ./scripts/sync-to-monorepo.sh [--commit-message "your message"]
#
# What it does:
#   1. Copies all NewsWoo files (excluding .git and zips) into NewsRock/newswoo/
#   2. Commits the changes in NewsRock
#   3. Pushes NewsRock to origin
#
# Requirements:
#   - Both repos cloned side by side (or set NEWSROCK_PATH)
#   - rsync, git

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NEWSWOO_DIR="$(dirname "$SCRIPT_DIR")"
NEWSROCK_PATH="${NEWSROCK_PATH:-$(dirname "$NEWSWOO_DIR")/NewsRock}"
COMMIT_MSG=""

# Parse args
while [[ $# -gt 0 ]]; do
  case "$1" in
    --commit-message|-m) COMMIT_MSG="$2"; shift 2 ;;
    *) echo "Unknown arg: $1"; exit 1 ;;
  esac
done

# Validate paths
if [[ ! -d "$NEWSWOO_DIR/.git" ]]; then
  echo "Error: Not inside a NewsWoo git repo ($NEWSWOO_DIR)"
  exit 1
fi

if [[ ! -d "$NEWSROCK_PATH/.git" ]]; then
  echo "Error: NewsRock repo not found at $NEWSROCK_PATH"
  echo "Set NEWSROCK_PATH env var or clone NewsRock side by side with NewsWoo"
  exit 1
fi

echo "Syncing NewsWoo → NewsRock/newswoo/"
echo "  Source: $NEWSWOO_DIR"
echo "  Dest:   $NEWSROCK_PATH/newswoo/"

# Sync files (exclude .git and zip archives)
rsync -av \
  --exclude='.git' \
  --exclude='*.zip' \
  --delete \
  "$NEWSWOO_DIR/" \
  "$NEWSROCK_PATH/newswoo/"

# Stage changes
cd "$NEWSROCK_PATH"
git add newswoo/

# Check if there are changes
if git diff --cached --quiet; then
  echo "No changes to sync."
  exit 0
fi

# Generate commit message if not provided
if [[ -z "$COMMIT_MSG" ]]; then
  NEWSWOO_SHA=$(cd "$NEWSWOO_DIR" && git rev-parse --short HEAD)
  COMMIT_MSG="sync(newswoo): update from NewsWoo@$NEWSWOO_SHA"
fi

git commit -m "$COMMIT_MSG"
git push origin main

echo "Done. NewsRock updated: $COMMIT_MSG"

