<?php
/**
*
* Handle files as protected, move, attachment
*
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Class PDA Helper
class Pda_Helper {

	/**
	* Generate Unique String
	*/
	public static function generate_unique_string() {
		// Return uniq id
		return bin2hex(random_bytes(7));
	}

	/**
	* Get Plugin Confligs
	*/
	public static function get_plugin_configs() {
		return array('endpoint' => 'pda_v3_pf');
	}

	/**
     * Get guid
     *
     * @param string $file_name
     * @param string $request_url
     * @param string $file_type
     *
     * @return mixed
     */
	public static function get_guid($file_name, $request_url, $file_type) {
		$guid = preg_replace("/-\d+x\d+.$file_type$/", ".$file_type", $request_url);
	}

	/**
	 * Get value of a param.
	 *
	 * @param array|bool  $params  Value need to get.
	 * @param string $key     Key of value.
	 * @param string $default Default value.
	 *
	 * @return string Return $default or value.
	 */
	public static function get( $params, $key, $default = '' ) {
		if ( ! is_array( $params ) || ! isset( $params[ $key ] ) ) {
			return $default;
		}

		return $params[ $key ];
	}

	/**
     * Get file access permission settings
     *
     * @param boolean $pda_settings
     *
     * @return mixed
     */
	public static function get_fap_setting( $pda_settings = false ) {

		// PDA Settings
		if ( ! $pda_settings ) {
			$pda_settings = get_option( 'FREE_PDA_SETTINGS', array() );
		}
		$file_access = Pda_Helper::get( $pda_settings, 'file_access_permission', 'author' );

		return empty( $file_access ) ? 'author' : $file_access;
	}

	/**
	 * @return array
	 */
	public static function get_current_roles() {
		if ( is_multisite() && is_super_admin( wp_get_current_user()->ID ) ) {
			$current_roles = array( 'administrator' );
		} else {
			$current_roles = wp_get_current_user()->roles;
		}

		return is_array( $current_roles ) ? $current_roles: array();
	}

	/**
	 * Is admin user role.
	 *
	 * @return bool
	 */
	public static function is_admin_user_role() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$current_roles = self::get_current_roles();

		return in_array( 'administrator', $current_roles, true );
	}

	/**
	 * Get the Protected Upload DIR.
	 *
	 * @param string $path The file path.
	 * @param bool   $in_url Is using in URL to add slash.
	 *
	 * @return string
	 */
	public static function mv_upload_dir( $path = '', $in_url = false ) {
		$dirpath  = $in_url ? '/' : '';
		$dirpath .= '_pda';
		$dirpath .= $path;

		return $dirpath;
	}

	/**
	 * Get NGINX rule
	 *
	 * @return mixed
	 */
	public static function get_nginx_rules() {
		$upload              = wp_upload_dir();
		$upload_path         = str_replace( site_url( '/' ), '', $upload['baseurl'] );

		$pattern            = self::mv_upload_dir( '(\/[A-Za-z0-9_@.\/&+-]+)+\.([A-Za-z0-9_@.\/&+-]+)$', true );
		$old_protected_path = "$upload_path$pattern";

		$original_rules = array(
			"rewrite $old_protected_path \"/index.php?pda_v3_pf=$1&is_direct_access=true&file_type=$2\" last;",
		);

		$private_link_rules = array(
			"rewrite private/([a-zA-Z0-9-_.]+)$ \"/index.php?pda_v3_pf=$1\" last;",
		);

		$rewrite_rules = array_merge(
			$original_rules,
			$private_link_rules
		);

		return $rewrite_rules;
	}

	/**
	 * Get rules html.
	 *
	 * @return html
	 */
	public static function get_iis_rule() {
		$string = '
		<?xml version="1.0" encoding="UTF-8"?>
		<configuration>
		    <system.webServer>
		        <rewrite>
		            <rules>
		                <!-- START - Prevent Direct Access Lite rules - START -->
		                <rule name="Imported Rule 1" stopProcessing="true">
		                    <match url="private/([a-zA-Z0-9]+)$" ignoreCase="false" />
		                    <action type="Rewrite" url="index.php?pda_v3_pf={R:1}" appendQueryString="false" />
		                </rule>
		                <rule name="Imported Rule 2" stopProcessing="true">
		                    <match url="wp-content/uploads/_pda(\/[A-Za-z0-9_@.\/&amp;+-]+)+\.([A-Za-z0-9_@.\/&amp;+-]+)$" ignoreCase="false" />
		                    <action type="Rewrite" url="index.php?pda_v3_pf={R:1}&amp;is_direct_access=true&amp;file_type={R:2}" appendQueryString="true" />
		                </rule>
		                <!-- END - Prevent Direct Access Lite rules - END -->
		                <rule name="wordpress" patternSyntax="Wildcard">
		                    <match url="*" />
		                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
		                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
		                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
		                    </conditions>
		                    <action type="Rewrite" url="index.php" />
		                </rule>
		            </rules>
		        </rewrite>
		    </system.webServer>
		</configuration>
		';
		return $string;
	}

	/**
	 * Get server name
	 *
	 * @return string
	 */
	public static function get_server_name() {
		global $is_apache;

		if ( $is_apache ) {
			return 'apache';
		}

		$server_info = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		$servers = [
			'nginx',
			'iis',
		];

		foreach ( $servers as $server ) {
			if ( strpos( strtolower( $server_info ), $server ) !== false ) {
				return $server;
			}
		}

		return '';
	}

	/**
     * Check URL exists or not
     *
     * @param integer $attachment_id
     *
     * @return string
     */
	public static function does_url_exists($attachment_id)
	{
		$file           = get_post_meta($attachment_id, '_wp_attached_file', true);
		if (!$file) {
			return false;
		}
		$upload_dir     = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir'];

		return file_exists(path_join($upload_basedir, $file));
	}
}
?>