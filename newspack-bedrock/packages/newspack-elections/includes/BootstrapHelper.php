<?php

class Govpack_Bootstrap_Helper {

	private static array $notice_defaults = [
		'type'               => 'error',
		'dismissible'        => true,
		'additional_classes' => [ 'inline', 'notice-alt' ],
		'attributes'         => [ 
			'data-slug' => 'govpack',
			'style'     => 'margin-left: 2px;',
		],
	];

	/**
	 * Displays a notice in wp-admin telling the user that the vendor folder is missing.
	 *  
	 * @since 1.1.0
	 */
	public static function notice_vendor_missing(): void {
		\wp_admin_notice(
			__( 'Newspack Elections: Dependencies Not Installed. Please run <code>composer install --no-dev</code> in the plugin directory.', 'newspack-elections' ),
			self::$notice_defaults
		);
	}

	
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

	/**
	 * Displays a notice in wp-admin telling the user that the prefixed-vendor folder is missing.
	 *  
	 * @since 1.1.0
	 */
	public static function notice_prefixed_vendor_missing(): void {
		\wp_admin_notice(
			__( 'Newspack Elections: Dependencies Not Prefixed. Please run <code>composer prefix-namespaces</code> in the plugin directory.', 'newspack-elections' ),
			self::$notice_defaults
		);
	}


	/**
	 * Displays a Notice in wp-admin telling the user that the build folder or its assets are missing. 
	 * 
	 * @since 1.1.0
	 */
	public static function notice_build_missing(): void {
		\wp_admin_notice(
			__( 'Newspack Elections: Compiled CSS and JavaScript are missing. Please run <code>npm install && npm run build</code> in the plugin directory.', 'newspack-elections' ),
			self::$notice_defaults
		);
	}


	/**
	 * Checks if a particular folder or directory exists.
	 * 
	 * @since 1.1.0
	 */
	public static function is_dir_empty( string $dir ): bool {
		return ! ( new \FilesystemIterator( $dir ) )->valid();
	}
}
