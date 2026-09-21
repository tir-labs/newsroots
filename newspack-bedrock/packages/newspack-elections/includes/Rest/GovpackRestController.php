<?php

namespace Govpack\Rest;

use WP_REST_Controller;

abstract class GovpackRestController extends WP_REST_Controller {
	
	protected $namespace = 'govpack/v1';
}
