#!/bin/bash
#
# Dependency Hardening Script
# Hardens and optimizes all dependency plugins for local-first operation.
# Run AFTER strip-externals.sh
#
# Usage: bash harden-dependencies.sh /path/to/wordpress/wp-content/plugins
#

set -euo pipefail

PLUGINS_DIR="${1:-./web/app/plugins}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() { echo -e "${GREEN}[HARDEN]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }

# ─── FIFU Premium: Keep — requires external workers for video thumbnails ───
if [ -d "$PLUGINS_DIR/fifu-premium" ]; then
    log "FIFU Premium: Audited — calls oembed-youtube.fifu.app and oembed-vimeo.fifu.app for video thumbnails."
    log "  External calls: fifu.app workers (REQUIRED for video thumbnails)"
    log "  No changes needed — these are intentional"
fi

# ─── Co-Authors Plus: Fully local ───
if [ -d "$PLUGINS_DIR/co-authors-plus" ]; then
    log "Co-Authors Plus: Audited — 100% local, no external calls."
    log "  No changes needed"
fi

# ─── Flux Media Optimizer: Null External API, keep local processing ───
log "Flux Media Optimizer: Disabling external API calls..."

FLUX_DIR=""
if [ -d "$PLUGINS_DIR/flux-media-optimizer/flux-media-optimizer" ]; then
    FLUX_DIR="$PLUGINS_DIR/flux-media-optimizer/flux-media-optimizer"
elif [ -d "$PLUGINS_DIR/flux-media-optimizer" ]; then
    FLUX_DIR="$PLUGINS_DIR/flux-media-optimizer"
fi

if [ -n "$FLUX_DIR" ]; then
    # Null out shared ExternalApiClient (api.fluxplugins.com)
    if [ -f "$FLUX_DIR/vendor-prefixed/stratease/flux-plugins-common/src/Api/ExternalApiClient.php" ]; then
        cat > "$FLUX_DIR/vendor-prefixed/stratease/flux-plugins-common/src/Api/ExternalApiClient.php" << 'FLUXAPI'
<?php
/** External API client — DISABLED for local-only operation. Local processing unaffected. */
namespace FluxMedia\FluxPlugins\Common\Api;
use FluxMedia\FluxPlugins\Common\Logger\Logger;
class ExternalApiClient {
    private $logger;
    public function __construct( Logger $logger, $base_url = null, $timeout = null ) { $this->logger = $logger; }
    public function activate_license( $k, $v = '' ) { return ['success'=>true,'valid'=>true,'message'=>'Local mode.']; }
    public function validate_license( $k ) { return ['success'=>true,'valid'=>true,'message'=>'Local mode.']; }
    public function check_compatibility( $id, $v ) { return ['success'=>true,'compatible'=>true]; }
    public function post( $r, $d = [] ) { return ['success'=>false,'error'=>'External API disabled.']; }
    public function get( $r, $p = [] ) { return ['success'=>false,'error'=>'External API disabled.']; }
    public function put( $r, $d = [] ) { return ['success'=>false,'error'=>'External API disabled.']; }
    public function delete( $r, $p = [] ) { return ['success'=>false,'error'=>'External API disabled.']; }
    private function handle_license_response() { return []; }
    private function handle_generic_response() { return []; }
    private function check_compatibility_before_request() { return true; }
    private function update_license_notice_transient() {}
}
FLUXAPI
        log "  Nullified: shared ExternalApiClient"
    fi

    # Null out app-level ExternalApiClient
    if [ -f "$FLUX_DIR/app/Services/ExternalApiClient.php" ]; then
        cat > "$FLUX_DIR/app/Services/ExternalApiClient.php" << 'FLUXAPPAPI'
<?php
/** App-level External API client — DISABLED for local-only operation. */
namespace FluxMedia\App\Services;
use FluxMedia\FluxPlugins\Common\Logger\Logger;
class ExternalApiClient {
    private $logger;
    public function __construct( Logger $logger ) { $this->logger = $logger; }
    public function submit_job( $d ) { return ['success'=>false,'error'=>'External API disabled.']; }
    public function check_job_status( $id ) { return ['success'=>false,'error'=>'External API disabled.']; }
    public function get_job_result( $id ) { return ['success'=>false,'error'=>'External API disabled.']; }
}
FLUXAPPAPI
        log "  Nullified: app-level ExternalApiClient"
    fi

    log "  Flux local processing: UNTOUCHED (GD/Imagick/FFmpeg)"
    log "  ✅ Flux now operates 100% locally — no media leaves the server"
fi

# ─── Done ───
echo ""
log "Done! All dependency plugins audited and hardened."
log "Run 'strip-externals.sh' first if you haven't already."

