#!/usr/bin/env bash
#
# pull-from-newspack.sh — Run FROM NewsRock to pull Postdated/Newspack into newspack-bedrock/.
#
# Usage:
#   cd /path/to/NewsRock
#   ./scripts/pull-from-newspack.sh [--newspack-path /path/to/Newspack]
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NEWSROCK_DIR="$(dirname "$SCRIPT_DIR")"
NEWSPACK_PATH="${NEWSPACK_PATH:-$(dirname "$NEWSROCK_DIR")/Newspack}"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --newspack-path) NEWSPACK_PATH="$2"; shift 2 ;;
    *) echo "Unknown arg: $1"; exit 1 ;;
  esac
done

if [[ ! -d "$NEWSPACK_PATH/.git" ]]; then
  echo "Error: Newspack repo not found at $NEWSPACK_PATH"
  exit 1
fi

echo "Pulling Newspack into NewsRock/newspack-bedrock/"
echo "  Source: $NEWSPACK_PATH"

cd "$NEWSROCK_DIR"

rsync -a \
  --exclude='.git' \
  --exclude='vendor/' \
  --exclude='web/wp/' \
  --exclude='web/app/plugins/' \
  --exclude='web/app/mu-plugins/bedrock-disallow-indexing/' \
  --exclude='web/app/themes/twentytwentyfive/' \
  --exclude='web/app/uploads/' \
  --exclude='web/app/upgrade/' \
  --exclude='.phpunit.cache/' \
  --exclude='*.zip' \
  "$NEWSPACK_PATH/" \
  "$NEWSROCK_DIR/newspack-bedrock/"

git add newspack-bedrock/

if git diff --cached --quiet; then
  echo "Already up to date."
  exit 0
fi

NEWSPACK_SHA=$(cd "$NEWSPACK_PATH" && git rev-parse --short HEAD)
git commit -m "sync(newspack-bedrock): pull from Newspack@$NEWSPACK_SHA"
git push origin main

echo "Done. NewsRock updated from Newspack@$NEWSPACK_SHA"
