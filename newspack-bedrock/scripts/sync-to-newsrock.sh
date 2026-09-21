#!/usr/bin/env bash
#
# sync-to-newsrock.sh — Run FROM the Newspack repo to push into NewsRock/newspack-bedrock/.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NEWSPACK_DIR="$(dirname "$SCRIPT_DIR")"
NEWSROCK_PATH="${NEWSROCK_PATH:-$(dirname "$NEWSPACK_DIR")/NewsRock}"
COMMIT_MSG=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --commit-message|-m) COMMIT_MSG="$2"; shift 2 ;;
    *) echo "Unknown arg: $1"; exit 1 ;;
  esac
done

if [[ ! -d "$NEWSPACK_DIR/.git" ]]; then
  echo "Error: Not inside the Newspack git repo ($NEWSPACK_DIR)"
  exit 1
fi

if [[ ! -d "$NEWSROCK_PATH/.git" ]]; then
  echo "Error: NewsRock repo not found at $NEWSROCK_PATH"
  exit 1
fi

echo "Syncing Newspack -> NewsRock/newspack-bedrock/"

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
  "$NEWSPACK_DIR/" \
  "$NEWSROCK_PATH/newspack-bedrock/"

cd "$NEWSROCK_PATH"
git add newspack-bedrock/

if git diff --cached --quiet; then
  echo "Already up to date."
  exit 0
fi

SHA=$(cd "$NEWSPACK_DIR" && git rev-parse --short HEAD)
MSG="${COMMIT_MSG:-sync(newspack-bedrock): pull from Newspack@$SHA}"
git commit -m "$MSG"
git push origin main
echo "Done."
