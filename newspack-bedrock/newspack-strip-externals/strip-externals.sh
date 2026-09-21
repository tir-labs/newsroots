#!/bin/bash
#
# Newspack External Stripper (Bedrock Edition)
# Optional SaaS helpers only. Does NOT strip Broadstreet, Slack, Discord, or data-event webhooks.
#
# Usage: bash strip-externals.sh [packages|web/app/plugins]
#
set -euo pipefail

PLUGINS_DIR="${1:-./packages}"
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'
log() { echo -e "${GREEN}[STRIP]${NC} $1"; }

nl_dir=""
for candidate in \
  "$PLUGINS_DIR/newspack-newsletters/includes/service-providers" \
  "$PLUGINS_DIR/newspack-newsletters/newspack-newsletters/includes/service-providers"; do
  if [ -d "$candidate" ]; then nl_dir="$candidate"; break; fi
done

if [ -n "$nl_dir" ]; then
  log "Stripping bundled SaaS ESP folders from newspack-newsletters."
  for esp in mailchimp active_campaign campaign_monitor constant_contact letterhead; do
    if [ -d "$nl_dir/$esp" ]; then
      rm -rf "$nl_dir/$esp"
      log "  Removed: $esp"
    fi
  done
  log "  Kept: base provider, webhooks, Broadstreet, Slack"
fi

np_dir=""
for candidate in "$PLUGINS_DIR/newspack-plugin/includes" "$PLUGINS_DIR/newspack-plugin/newspack-plugin/includes"; do
  if [ -d "$candidate" ]; then np_dir="$candidate"; break; fi
done

if [ -n "$np_dir" ]; then
  log "Stripping Google OAuth / Salesforce / recaptcha / pixels from newspack-plugin."
  for f in class-google-oauth.php class-google-oauth-ga4-client.php class-google-login.php class-google-services-connection.php class-mailchimp-api.php; do
    if [ -f "$np_dir/oauth/$f" ]; then rm -f "$np_dir/oauth/$f"; log "  Removed: oauth/$f"; fi
  done
  if [ -f "$np_dir/class-salesforce.php" ]; then rm -f "$np_dir/class-salesforce.php"; log "  Removed: class-salesforce.php"; fi
  if [ -f "$np_dir/class-recaptcha.php" ]; then rm -f "$np_dir/class-recaptcha.php"; log "  Removed: class-recaptcha.php"; fi
  for f in class-meta-pixel.php class-twitter-pixel.php; do
    if [ -f "$np_dir/tracking/$f" ]; then rm -f "$np_dir/tracking/$f"; log "  Removed: tracking/$f"; fi
  done
  log "  Kept: data-events webhooks and API"
fi

log "Done."
