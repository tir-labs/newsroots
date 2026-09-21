#!/usr/bin/env bash
#
# pull-from-newswoo.sh — Run this FROM the NewsRock repo to pull latest NewsWoo.
#
# Usage:
#   cd /path/to/NewsRock
#   ./scripts/pull-from-newswoo.sh [--newswoo-path /path/to/NewsWoo]
#
# What it does:
#   1. Copies latest NewsWoo files into NewsRock/newswoo/
#   2. Commits and pushes

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NEWSROCK_DIR="$(dirname "$SCRIPT_DIR")"
NEWSWOO_PATH="${NEWSWOO_PATH:-$(dirname "$NEWSROCK_DIR")/NewsWoo}"

# Parse args
while [[ $# -gt 0 ]]; do
  case "$1" in
    --newswoo-path) NEWSWOO_PATH="$2"; shift 2 ;;
    *) echo "Unknown arg: $1"; exit 1 ;;
  esac
done

if [[ ! -d "$NEWSWOO_PATH/.git" ]]; then
  echo "Error: NewsWoo repo not found at $NEWSWOO_PATH"
  echo "Set NEWSWOO_PATH env var or clone NewsWoo side by side with NewsRock"
  exit 1
fi

echo "Pulling NewsWoo into NewsRock/newswoo/"
echo "  Source: $NEWSWOO_PATH"

cd "$NEWSROCK_DIR"

rsync -av \
  --exclude='.git' \
  --exclude='*.zip' \
  --delete \
  "$NEWSWOO_PATH/" \
  "$NEWSROCK_DIR/newswoo/"

git add newswoo/

if git diff --cached --quiet; then
  echo "Already up to date."
  exit 0
fi

NEWSWOO_SHA=$(cd "$NEWSWOO_PATH" && git rev-parse --short HEAD)
git commit -m "sync(newswoo): pull from NewsWoo@$NEWSWOO_SHA"
git push origin main

echo "Done. NewsRock updated from NewsWoo@$NEWSWOO_SHA"

