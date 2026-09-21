<?php
/**
 *
 * Managing Repositoty Resources
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:disable WordPress.DB.PreparedSQL

// Class started
class PDA_Repository {

	// Declare some variable
	private $wpdb;
	private $table_name;

	// Call Constructor
	public function __construct() {
		
		// Assign Global Variable
		global $wpdb;
		$this->wpdb = &$wpdb;
        $this->table_name = $wpdb->prefix . 'prevent_direct_access_free';
	}

	/**
     * Create Advance File
     *
     * @param string $file_info
     *
     * @return Mixed
     */
	function create_advance_file( $file_info ) {

		$post_id = $file_info['post_id'];
		$post = $this->get_post_by_id( $post_id );

		$result = false;

		if ( isset( $post ) ) {
			 $file_advance = $this->get_advance_file_by_post_id( $post_id );
			// Comment because one post has many private links.
			//$result = $this->wpdb->insert( $this->table_name, $file_info );
			 if ( !isset( $file_advance ) ) {
			 	$file_info['hits_count'] = 0;
			 	$result = $this->wpdb->insert( $this->table_name, $file_info );
			 }
			 else {
			 	$isUpdate = $file_advance->is_prevented !== $file_info['is_prevented'];
			 	if ( $isUpdate ) {
			 		$result = $this->update_advance_file_by_post_id( $file_info );
			 	}
			 }
		}

		return $result;

	}

	/**
     * Setup Prevent Files
     *
     * @param integer $post_id
     *
     */
	function set_prevent_files( $post_id ) {
		$found = $this->get_advance_file_by_post_id( $post_id );
		if(isset( $found )) {
			$file_info = array( 'post_id' => $post_id, 'is_prevented' => true);
			$this->update_advance_file_by_post_id($file_info);
		} else {
			$file_info = array( 'time' => current_time( 'mysql' ), 'post_id' => $post_id, 'is_prevented' => true, 'url' => Pda_Helper::generate_unique_string() );
			$this->create_advance_file( $file_info );
		}
	}

	/**
     * Setup Prevent Files
     *
     * @param integer $post_id
     *
     */
	function unset_all_links( $post_id ) {
		$file_info = array( 'post_id' => $post_id, 'is_prevented' => false);
		$this->update_advance_file_by_post_id($file_info);
	}

	/**
     * Setup Prevent Files
     *
     * @param integer $post_id
     *
     * return Mixed
     */
	function get_post_by_id( $post_id ) {
		$post = get_post( $post_id );
		return $post;
	}

	/**
     * Get the postmeta by value
     *
     * @param string $value
     *
     * return Mixed
     */
	function get_post_meta_by_value ( $value ) {
		$value = '%' . $value;
		$table_name = $this->wpdb->postmeta;
		$queryString = "SELECT * FROM $table_name WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s";
		$preparation = $this->wpdb->prepare( $queryString, $value );
		$post = $this->wpdb->get_row( $preparation );
		return $post;
	}

	/**
     * Get the postmeta by id
     *
     * @param string $post_id
     *
     * return Meta
     */
	function get_post_meta_by_post_id ( $post_id ) {
		$table_name = $this->wpdb->postmeta;
		$queryString = "SELECT * FROM $table_name WHERE meta_key='_wp_attached_file' AND post_id = %s";
		$preparation = $this->wpdb->prepare( $queryString, $post_id );
		$post_meta = $this->wpdb->get_row( $preparation );
		return $post_meta;
	}

	/**
     * Get the post by guid
     *
     * @param integer $guid
     *
     * return Mixed
     */
	function get_post_by_guid( $guid ) {
		$guid = '%' . $guid;
		$table_name = $this->wpdb->posts;
		$queryString = "SELECT * FROM $table_name WHERE post_type='attachment' AND guid LIKE %s";
		$preparation = $this->wpdb->prepare( $queryString, $guid );
		$post = $this->wpdb->get_row( $preparation );
		return $post;
	}

	/**
     * Get the file by name
     *
     * @param string $name
     *
     * return Mixed
     */
	function get_file_by_name( $name ) {
		$table_name = $this->wpdb->posts;
		$queryString = "SELECT * FROM $table_name WHERE post_type='attachment' AND post_name LIKE %s";
		$preparation = $this->wpdb->prepare( $queryString, $name );
		$post = $this->wpdb->get_row( $preparation );
		return $post;
	}

	/**
     * Get the advance file by post id
     *
     * @param integer $post_id
     *
     * return string
     */
	function get_advance_file_by_post_id( $post_id ) {
		$query_string = $this->wpdb->prepare(
			'SELECT * FROM ' . $this->table_name . ' WHERE post_id = %d',
			$post_id
		);
		$advance_file = $this->wpdb->get_row( $query_string );
		return $advance_file;
	}

	/**
     * Get the status of advance file by post id
     *
     * @param integer $post_id
     * @param boolean $is_prevented
     *
     * return Mixed
     */
	function get_status_advance_file_by_post_id( $post_id, $is_prevented ) {
		$query_string = $this->wpdb->prepare(
			'SELECT * FROM ' . $this->table_name . ' WHERE post_id = %d AND is_prevented = %s',
			$post_id,
			$is_prevented
		);
		$advance_file = $this->wpdb->get_row( $query_string );
		return $advance_file;
	}

	/**
     * Get the advance file by host id
     *
     * @param integer $post_id
     *
     * return string
     */
	function get_advance_files_by_host_id( $post_id ) {
		$query_string = $this->wpdb->prepare(
			'SELECT * FROM ' . $this->table_name . ' WHERE post_id = %d',
			$post_id
		);
		$advance_file = $this->wpdb->get_results( $query_string );
		return $advance_file;
	}

	/**
     * Get the protected post
     * return string
     */
	function get_protected_post () {
		$post_table  = $this->wpdb->prefix . 'posts';
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names from $wpdb->prefix and class property, no user input.
		$query_string = "SELECT * FROM {$this->table_name} as tb1 INNER JOIN {$post_table} as tb2 ON tb1.post_id = tb2.ID WHERE tb1.is_prevented = 1 GROUP BY tb1.post_id";
		$files = $this->wpdb->get_results( $query_string );
		// phpcs:enable PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $files;
	}

	/**
     * Get the advance file by URL
     *
     * @param string $url
     *
     * return string
     */
	function get_advance_file_by_url( $url ) {
		$advance_file = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE url = %s", $url ) );
		return $advance_file;
	}

	/**
     * Get the advance file by id
     *
     * @param integer $id
     *
     * return string
     */
	function get_advance_file_by_id( $id ) {
		$advance_file = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE ID = %s", $id ) );
		return $advance_file;
	}

	/**
     * Delete advance file
     */
	function delete_advance_file( $id ) {
		$result = $this->wpdb->delete( $this->table_name, array( 'ID' => $id ), array( '%d' ) );
	}

	/**
     * Update the advance file by id
     *
     * @param integer $id
     * @param string $data
     *
     * return string
     */
	function update_advance_file_by_id( $id, $data ) {
		$where = array('ID' => $id);
		$result = $this->wpdb->update( $this->table_name, $data, $where );
		return $result;
	}

	/**
     * Update the advance file by post id
     *
     * @param array $file_info
     *
     * return string
     */
	function update_advance_file_by_post_id( $file_info ) {
		$data = array( 'is_prevented' => $file_info['is_prevented'], );
		$where = array( 'post_id' => $file_info['post_id'] );
		$result = $this->wpdb->update( $this->table_name, $data, $where );
		return $result;
	}

	/**
     * Check Advance file limitation
     * return string
     */
	function check_advance_file_limitation() {
		$is_prevented = 1;
		$number_of_records = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT count(*) FROM $this->table_name WHERE is_prevented = %d", $is_prevented ) );
		return $number_of_records;
	}

	/**
     * Delete the advance file by post id
     *
     * @param integer $post_id
     *
     */
	function delete_advance_file_by_post_id( $post_id ) {
		$advance_file = $this->get_advance_file_by_post_id( $post_id );
		if ( isset( $advance_file ) || $advance_file != null ) {
			$this->delete_advance_file( $advance_file->ID );
		}
	}

	/**
	 * Update the new private link by post id
	 *
	 * @param int     $post_id post's id
	 * @return int|false       The number of rows updated, or false on error
	 */
	function update_private_link_by_post_id( $post_id ) {
		$data = array( 'url' => Pda_Helper::generate_unique_string() );
		$where = array( 'post_id' => $post_id );
		$result = $this->wpdb->update( $this->table_name, $data, $where );
		return $result;
	}

	/**
     * Update Customize Private Link by Post id
     *
     * @param integer $post_id
     * @param string $customize_link
     *
     * return result
     */
	function update_customize_private_link_by_post_id( $post_id, $customize_link ) {
		// $advance_file = $this->get_advance_file_by_url($customize_link);
		// if (isset($advance_file)) {
		// 	return false;
		// }
		// $data = array( 'url' => $customize_link );
		// $where = array( 'post_id' => $post_id );
		$file_info = array( 'time' => current_time( 'mysql' ), 'post_id' => $post_id, 'is_prevented' => false, 'url' => $customize_link );
		// $result = $this->wpdb->update( $this->table_name, $data, $where );
		$result = $this->wpdb->insert( $this->table_name, $file_info );
		return $result;
	}

	/**
     * Get Protected Post
     *
     * @param boolean $is_prevented
     *
     * return string
     */
	function get_protected_posts( $is_prevented ){
		$queryString = "SELECT DISTINCT post_id FROM $this->table_name WHERE is_prevented = %s";
		$preparation = $this->wpdb->prepare($queryString, $is_prevented);
		$advance_file = $this->wpdb->get_results($preparation);
		return $advance_file;
	}

	/**
     * Migrate data to new table
     */
	function migrate_data_to_new_table() {
		// Assign Global variable
		global $wpdb;
		$old_table = $wpdb->prefix . 'prevent_direct_access';
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_table );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $query ) !== $old_table ) {
			return;
		}
		
		$old_data = $this->get_all_data_of_old_table();
		foreach ( $old_data as $data ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->insert(
                $this->table_name,
                array(
                    'post_id' => $data->post_id,
                    'time' => $data->time,
                    'url' => $data->url,
                    'is_prevented' => $data->is_prevented,
                    'hits_count' => isset( $data->hits_count ) ? $data->hits_count : 0,
                    'limit_downloads' => isset( $data->limit_downloads ) ? $data->limit_downloads : NULL,
                    'expired_date' => isset( $data->expired_date ) ? $data->expired_date : NULL,
                )
            );
		}
		
		// Drop old table
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
		$old_table = esc_sql( $wpdb->prefix . 'prevent_direct_access' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$old_table}" );
		// phpcs:enable PluginCheck.Security.DirectDB.UnescapedDBParameter
		
		delete_option( 'jal_db_version' );
		
	}

	/**
     * Get all data of old table
     *
     * return result
     */
	function get_all_data_of_old_table() {
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
		global $wpdb;
		$old_table = $wpdb->prefix . 'prevent_direct_access';
		$query = "SELECT * FROM $old_table";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$results = $wpdb->get_results( $query );
		// phpcs:enable PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $results;
	}

	/**
     * Get Private Link by post id
     *
     * @param integer $post_id
     *
     * return array
     */
    function get_private_links_by_post_id_and_type_is_null( $post_id ) {
		global $wpdb;
        $prepare    = $this->wpdb->prepare( "
				SELECT * FROM $this->table_name
				WHERE post_id = %s
				ORDER BY time DESC
			", $post_id );
        return $this->wpdb->get_results( $prepare, ARRAY_A );
    }

    /**
     * Check protected file
     *
     * @param integer $post_id
     *
     * return result
     */
    function is_protected_file( $post_id ) {
		$handler = new Pda_Free_Handle();
        $file                     = get_post_meta( $post_id, '_wp_attached_file', true );
        $is_in_protected_folder   = strpos( $file, $handler->mv_upload_dir( '/' ) ) !== false;
        $is_protected_in_metadata = get_post_meta( $post_id, PDA_Lite_Constants::PROTECTION_META_DATA, true ) === "1";

        return $is_in_protected_folder && $is_protected_in_metadata;
    }

    /**
     * Check Unprotected Files
     */
    function un_protect_files() {
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
        $table_name = $this->wpdb->prefix . 'postmeta';
        $query      = "SELECT post_id FROM $table_name WHERE meta_key = '_pda_protection' and meta_value = 1";
        $post_id    = $this->wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable PluginCheck.Security.DirectDB.UnescapedDBParameter
        $handle = new Pda_Free_Handle();
        foreach ( $post_id as $key => $value ) {
            $handle->un_protect_file( $value['post_id'] );
            delete_post_meta( $value['post_id'], '_pda_protection', 1 );
        }
    }

	/**
	 * Used by PDA Gold that removes the private links of protected files.
	 * 1. Find they protected files by postmeta name _pda_protection
	 * 2. Make sure the protected post ID existed in wp_prevent_direct_access_free table.
	 * 3. Delete private links in wp_prevent_direct_access_free table.
	 */
    function remove_private_links() {
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
	    $table_name = $this->wpdb->prefix . 'postmeta';
	    $query      = "SELECT post_id FROM $table_name WHERE meta_key = '_pda_protection' and meta_value = 1";
	    $post_ids    = $this->wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable PluginCheck.Security.DirectDB.UnescapedDBParameter
	    foreach ( $post_ids as $key => $value ) {
	    	$post_id = $value['post_id'];
		    $advance_file = $this->get_advance_file_by_post_id( $post_id );
		    if ( isset( $advance_file ) ) {
			    $this->delete_advance_file_by_post_id( $post_id );
		    }
	    }
    }
}

// phpcs:enable WordPress.DB.PreparedSQL
?>
