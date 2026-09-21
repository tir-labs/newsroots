<?php
/** App-level External API client — DISABLED for local-only operation. */
namespace FluxMedia\App\Services;
use FluxMedia\FluxPlugins\Common\Logger\Logger;
class ExternalApiClient {
    private $logger;
    public function __construct(Logger $logger) { $this->logger = $logger; }
    public function submit_job($d) { return ['success' => false, 'error' => 'External API disabled.']; }
    public function check_job_status($id) { return ['success' => false, 'error' => 'External API disabled.']; }
    public function get_job_result($id) { return ['success' => false, 'error' => 'External API disabled.']; }
}
