<?php

namespace PDAFree\modules\Grid_View;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Loader {
	private $service;

	public function __construct( $pda_admin ) {
		$this->service = new Service( $pda_admin );
	}

	public function register() {
		add_filter( 'wp_prepare_attachment_for_js', array(
			$this->service,
			'maybe_add_protection_border_class',
		), 10, 2 );
		add_filter( 'attachment_fields_to_edit', array( $this->service, 'maybe_add_checkbox_protection' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this->service, 'update_protection_status' ), 10, 2 );
		add_action( 'wp_enqueue_media', array( $this->service, 'enqueue_media' ) );
	}
}
