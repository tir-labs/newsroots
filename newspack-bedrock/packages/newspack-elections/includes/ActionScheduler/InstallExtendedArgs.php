<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\ActionScheduler;

/**
 * Installs the extended Args Table
 */
class InstallExtendedArgs {

	/**
	 * Current Version of the Migration Schema (ie, what is represented in this file)
	 *
	 * @var int
	 *
	 * @psalm-var 1
	 */
	private static int $current_version = 1;

	/**
	 * The version is stored in the options table, where is it stored?
	 *
	 * @var string
	 *
	 * @psalm-var 'extended_args_version'
	 */
	private static string $version_options_key = 'extended_args_version';

	/**
	 * Table we are creating here
	 *
	 * @var string
	 *
	 * @psalm-var 'actionscheduler_actions_extended'
	 */
	private static string $table_name = 'actionscheduler_actions_extended';

	/**
	 * Run Migrations.
	 */
	public static function do(): void {
		self::register_table();
		if ( self::needs_migration() ) {
			self::run_migrations();
		}
	}

	/**
	 * Create the Table name from what we are using and the prefix.
	 *
	 * @param string $table table name we are creating.
	 */
	public static function get_full_table_name( $table ): string {
		return $GLOBALS['wpdb']->prefix . $table;
	}

	
	/**
	 * Make wpdb aware that the extended table exists.
	 */
	public static function register_table(): void {
		global $wpdb;
		$table_name        = self::$table_name;
		$wpdb->tables[]    = $table_name;
		$name              = self::get_full_table_name( $table_name );
		$wpdb->$table_name = $name;
	}

	/**
	 * Test the versions and detemin if we need to run migrations
	 */
	public static function needs_migration(): bool {
		$installed_version = \get_option( self::$version_options_key, 0 );

		if ( $installed_version >= self::$current_version ) {
			return false;
		}

		return true;
	}


	
	/**
	 * Run the Migrations.
	 *
	 * @return void
	 */
	public static function run_migrations() {

		$installed_version = \get_option( self::$version_options_key, 0 );

		if ( $installed_version >= self::$current_version ) {
			return;
		}

		$methods_to_execute = [];
	
	
		for ( $i = $installed_version; $i <= self::$current_version; $i++ ) {
			$method_name = 'migrate_v_' . $i;
			if ( method_exists( __CLASS__, $method_name ) ) {
				$methods_to_execute[] = [
					'method'  => $method_name,
					'version' => $i,
				];
			}
		}
	

		foreach ( $methods_to_execute as $method ) {
			call_user_func( __CLASS__ . '::' . $method['method'] );
			update_option( self::$version_options_key, $method['version'] );
		}
	}
	/**
	 * Migrations version 1
	 */
	public static function migrate_v_1(): void {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = self::get_table_definition();
		dbDelta( $table ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.dbDelta_dbdelta
	}

	/**
	 * Create the Table for extended args.
	 */
	private static function get_table_definition(): string {    
		global $wpdb;

		$table_name      = self::$table_name;
		$table_name      = $wpdb->$table_name;
		$charset_collate = $wpdb->get_charset_collate();
		
		return "CREATE TABLE {$table_name} (
            extended_action_id bigint(20) unsigned NOT NULL auto_increment,
            action_id bigint(20) unsigned NOT NULL default '0',
            extended_args text DEFAULT NULL,
            PRIMARY KEY  (extended_action_id)
            ) $charset_collate";
	}
}
