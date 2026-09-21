<?php
/**
*
* Database Initialization
*
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Class PDA Database
class Pda_Database {

	private $jal_db_version;
	private $pda_jal_db_version_free;

	public function __construct() {
		$this->jal_db_version = '1.0';
		$this->pda_jal_db_version_free = '1.0';
	}

	/**
	 * Will be removed in the next version
	 */
	function install() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'prevent_direct_access';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from $wpdb->prefix; identifier cannot be prepared.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			//table is not created. you may create the table here.
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
	    	ID mediumint(9) NOT NULL AUTO_INCREMENT,
	    	post_id mediumint(9) NOT NULL,
	    	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    	url varchar(55) DEFAULT '' NOT NULL,
	    	is_prevented tinyint(1) DEFAULT 1,
	    	UNIQUE KEY id (id)
	    ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			// $wpdb->query( $sql );
			add_option( 'jal_db_version', $this->jal_db_version );
		}

		$installed_ver = get_option( "jal_db_version" );
		if ( $installed_ver == '1.0' ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
		    	hits_count mediumint(9) NOT NULL
		    ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			$this->jal_db_version = '1.1';
			update_option( 'jal_db_version', $this->jal_db_version );
		} else if ( $installed_ver == '1.1' ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
		    	limit_downloads mediumint(9)
		    ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			$this->jal_db_version = '1.2';
			update_option( 'jal_db_version', $this->jal_db_version );
		} else if ( $installed_ver == '1.2' ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
		    	expired_date BIGINT DEFAULT NULL
		    ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			$this->jal_db_version = '1.3';
			update_option( 'jal_db_version', $this->jal_db_version );
		}
	}

	/**
	 * Uninstall and Drop Table
	 */
	function uninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'prevent_direct_access_free';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from $wpdb->prefix; identifier cannot be prepared.
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		$this->remove_db_options();
	}

	/**
	 * Uninstall Statics
	 */
	static function uninstall_static() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'prevent_direct_access_free';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name from $wpdb->prefix; identifier cannot be prepared.
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
		delete_option( 'pda_jal_db_version_free' );

	}

	/**
	 * Remove DB version free options
	 */
	function remove_db_options() {
		delete_option( 'pda_jal_db_version_free' );
	}

	/**
	 * Create New Table
	 */
	function create_new_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'prevent_direct_access_free';
        $query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- $query is prepared above.
        if ( $wpdb->get_var( $query ) !== $table_name ) {
            //table is not created. you may create the table here.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
	    	ID mediumint(9) NOT NULL AUTO_INCREMENT,
	    	post_id mediumint(9) NOT NULL,
	    	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	    	url varchar(55) DEFAULT '' NOT NULL,
	    	is_prevented tinyint(1) DEFAULT 1,
	    	hits_count mediumint(9) NOT NULL,
	    	limit_downloads mediumint(9),
	    	expired_date BIGINT DEFAULT NULL,
	    	UNIQUE KEY id (id)
	    ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );

            add_option( 'pda_jal_db_version_free', $this->pda_jal_db_version_free );
        }
	}

}

?>
