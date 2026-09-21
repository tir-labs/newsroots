<?php
/** External API client — DISABLED for local-only operation. Local processing unaffected. */
namespace FluxMedia\FluxPlugins\Common\Api;
use FluxMedia\FluxPlugins\Common\Logger\Logger;
class ExternalApiClient {
    private $logger;
    public function __construct(Logger $logger, $base_url = null, $timeout = null) { $this->logger = $logger; }
    public function activate_license($k, $v = '') { return ['success' => true, 'valid' => true, 'message' => 'Local mode.']; }
    public function validate_license($k) { return ['success' => true, 'valid' => true, 'message' => 'Local mode.']; }
    public function check_compatibility($id, $v) { return ['success' => true, 'compatible' => true]; }
    public function post($r, $d = []) { return ['success' => false, 'error' => 'External API disabled.']; }
    public function get($r, $p = []) { return ['success' => false, 'error' => 'External API disabled.']; }
    public function put($r, $d = []) { return ['success' => false, 'error' => 'External API disabled.']; }
    public function delete($r, $p = []) { return ['success' => false, 'error' => 'External API disabled.']; }
}
