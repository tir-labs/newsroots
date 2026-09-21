<?php

class NPE_Bootstrap_Helper extends Govpack_Bootstrap_Helper {

	private static $notice_defaults = [
		'type'               => 'error',
		'dismissible'        => true,
		'additional_classes' => [ 'inline', 'notice-alt' ],
		'attributes'         => [ 
			'data-slug' => 'govpack',
			'style'     => 'margin-left: 2px;',
		],
	];
	
	/**
	 * Displays a notice in wp-admin telling the user they have installed the plugin twice or under two different name. 
	 * 
	 * @since 1.2
	 */
	public static function notice_double_install(): void {

		\wp_admin_notice(
			__( 'Newspack Elections: Plugin installed twice. Possibly under different names. Please check and disable unwanted versions.', 'newspack-elections' ),
			self::$notice_defaults
		);
	}
}
