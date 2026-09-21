<?php
/**
 *
 * Download Functions and Protections
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// phpcs:disable WordPress.Security.NonceVerification.Recommended


// Call Required Files
require_once 'includes/repository.php';
require_once 'includes/helper.php';

ignore_user_abort( true );
// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_time_limit
if ( function_exists( 'set_time_limit' ) ) {
	@set_time_limit( 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
}// disable the time limit for this script
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_direct_access = isset( $_GET['is_direct_access'] ) ? sanitize_text_field( wp_unslash( $_GET['is_direct_access'] ) ) : '';
if ( $is_direct_access === 'true' ) {
	/**
	 * Quick fix for FAP
	 * Current FAP is "No user roles" => send file not found to client
	 * Later if we need to improve FAP, then we can handle in the check_file_is_prevented function
	 */
	check_file_is_prevented();
} else {
	show_file_from_private_link();
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// phpcs:disable WordPress.Security.NonceVerification.Recommended

/**
 * Check if option existed
 */
function check_stop_image_hotlinking() {

	// Check File is set or not
	if ( ! isset( $_GET['file_type'] ) ) {
		return;
	}

	$pda_option = get_option( 'FREE_PDA_SETTINGS' );
	if ( is_array( $pda_option ) && array_key_exists( 'enable_image_hot_linking', $pda_option ) && $pda_option['enable_image_hot_linking'] === "on" ) {

		$file_type = sanitize_text_field( wp_unslash( $_GET['file_type'] ) );
		$images    = [ 'jpg', 'png', 'PNG', 'gif' ];

		if ( in_array( $file_type, $images ) ) {
			if ( ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) ) ) {
				$referer_host = ! empty( $_SERVER['HTTP_REFERER'] ) ? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), PHP_URL_HOST ) : '';
				$my_domain = ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
				if ( $referer_host !== $my_domain ) {
					file_not_found();
				}
			}
		}
	}
}

/**
 * Get Page 404 from settings
 */
function get_page_404() {
	$pda_settings = get_option( 'FREE_PDA_SETTINGS' );
	if ( isset( $pda_settings['search_result_page_404'] ) ) {
		$page_404      = $pda_settings['search_result_page_404'];
		$link_page_404 = explode( ";", $page_404 );

		return $link_page_404[0];
	} else {
		return null;
	}
}

/**
 * Check file is prevented
 */
function check_file_is_prevented() {
	$configs   = Pda_Helper::get_plugin_configs();
	$endpoint  = $configs['endpoint'];
	if ( ! isset( $_GET[ $endpoint ], $_GET['file_type'] ) ) {
		file_not_found();
	}
	$file_name = sanitize_text_field( wp_unslash( $_GET[ $endpoint ] ) );
	$file_type = sanitize_text_field( wp_unslash( $_GET['file_type'] ) );

	$original_file = "$file_name.$file_type";

	$attachment_id = pda_free_get_attachment_id_from_url( $original_file );
	$mime          = wp_check_filetype( $original_file );
	$attachment_id = apply_filters( 'pda_handle_attachment_id', $attachment_id, $original_file, $mime );
	if ( empty( $attachment_id ) ) {
		file_not_found();
	} else {
		_check_advance_file( $attachment_id, $original_file );
	}

}

/**
 * Get attachment ID from file path
 *
 * @param string $file_path The file path from .htaccess without _pda.
 *
 * @return int|bool
 */
function pda_free_get_attachment_id_from_url( $file_path ) {
	// Need to pre append the _pda due to no _pda in current .htaccess rule.
	$file_path = '/_pda' . strtok( $file_path, '?' );
	$extension = wp_check_filetype( $file_path );

	$upload_dir = wp_upload_dir();
	if ( false === $extension['type'] || false === strpos( $extension['type'], 'image' ) ) {
		$attachment_id = attachment_url_to_postid( $upload_dir['baseurl'] . $file_path );
	} else {
		$attachment    = pda_free_attachment_image_url_to_post( $upload_dir['baseurl'], $file_path );
		$attachment_id = empty( $attachment ) ? false : (int) $attachment->post_id;
	}

	return $attachment_id;
}

/**
 * Attachment UEL to Post
 *
 * @param array $options
 * @param string $option_key
 *
 * @return Mixed
 */
function pda_free_attachment_image_url_to_post( $baseurl, $filepath ) {
	global $wpdb;
	list( $size, $file_no_size ) = get_image_size_of_link( $filepath );

	// Massage attachment URL before handle.
	$url_has_size = massage_file_url( $baseurl . $filepath );

	/**
	 * Only return post_id if attachment have not file size.
	 */
	if ( empty( $size ) ) {
		$sql = $wpdb->prepare(
			"SELECT * FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
			$url_has_size
		);

		// phpcs:disable WordPress.DB
		$post = $wpdb->get_row( $sql );
       // phpcs:enable WordPress.DB

		return $post;
	}

	// Massage attachment URL before handle.
	$url_no_size = massage_file_url( $baseurl . $file_no_size );

	/**
	 * Input image: test.jpg
	 * Output image: test-scaled.jpg
	 */
	$url_no_size_scaled = pda_free_get_scaled_url( $url_no_size );

	/**
	 * Get all file which has size and no size.
	 */
	if ( $url_no_size_scaled ) {
		$sql = $wpdb->prepare(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value IN (%s, %s, %s)",
			$url_has_size,
			$url_no_size,
			$url_no_size_scaled
		);
	} else {
		$sql = $wpdb->prepare(
			"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value IN (%s, %s)",
			$url_has_size,
			$url_no_size
		);
	}

	// phpcs:disable WordPress.DB
	$posts = $wpdb->get_results( $sql );
	// phpcs:enable WordPress.DB

	if ( count( $posts ) === 1 ) {
		return $posts[0];
	}

	/**
	 * Priority:
	 *    1. Get file which has size first
	 *    2. Get file which has no size.
	 */
	foreach ( $posts as $post ) {
		if ( $url_has_size === $post->meta_value ) {
			return $post;
		}
	}
	foreach ( $posts as $post ) {
		if ( $url_no_size === $post->meta_value ) {
			return $post;
		}
	}
	if ( $url_no_size_scaled ) {
		foreach ( $posts as $post ) {
			if ( $url_no_size_scaled === $post->meta_value ) {
				return $post;
			}
		}
	}

	return pda_free_might_get_post_id_from_backup_sizes( $url_no_size, $url_no_size_scaled );
}


/**
 * Get scaled image.
 *
 * @param string $url           URL.
 * @param string $optimize_name Optimize name for image.
 *
 * @return bool|string
 */
function pda_free_get_scaled_url( $url, $optimize_name = '-scaled' ) {
	$url_no_size_scaled  = false;
	$url_no_size_pattern = explode( '.', $url );
	$len_url_no_size     = count( $url_no_size_pattern );

	/**
	 * Check file have extension and concat '-scaled' to image URL.
	 * -scaled WP release 5.3 version.
	 */
	if ( $len_url_no_size > 1 ) {
		$url_no_size_pattern[ $len_url_no_size - 2 ] = $url_no_size_pattern[ $len_url_no_size - 2 ] . $optimize_name;
		$url_no_size_scaled                          = implode( '.', $url_no_size_pattern );
	}

	return $url_no_size_scaled;
}

/**
 * Try to guess the post ID from backup sizes data.
 *
 * @param string $url_no_size        The request URL.
 * @param string $url_no_size_scaled The scaled file URL.
 *
 * @return object|bool
 *  object having the post_id key.
 *  bool (false) cannot find any attachment file.
 */
function pda_free_might_get_post_id_from_backup_sizes( $url_no_size, $url_no_size_scaled ) {
	$file        = wp_basename( $url_no_size );
	$scaled_file = wp_basename( $url_no_size_scaled );
	// phpcs:disable WordPress.DB
	$query_args  = array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'fields'      => 'ids',
		'meta_query'  => array(
			'relation' => 'OR',
			array(
				'value'   => $file,
				'compare' => 'LIKE',
				'key'     => '_wp_attachment_backup_sizes', // Case when rotate the images.
			),
			array(
				'value'   => $scaled_file,
				'compare' => 'LIKE',
				'key'     => '_wp_attachment_backup_sizes', // Case when crop scaled images with small size
			),
		),
	);
	$query       = new WP_Query( $query_args );
	// phpcs:enable WordPress.DB
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post_id ) {
			// Need to query the backup sizes and double check with the input file.
			$backup_sizes       = get_post_meta( $post_id, '_wp_attachment_backup_sizes', true );
			$backup_image_files = wp_list_pluck( $backup_sizes, 'file' );
			if ( in_array( $file, $backup_image_files, true ) || in_array( $scaled_file, $backup_image_files, true ) ) {
				return (object) array(
					'post_id' => $post_id,
				);
			}
		}
	}

	return false;
}

/**
 * Massage File URL
 *
 * @param string $url
 *
 * @return Mixed
 */
function massage_file_url( $url ) {
	$dir  = wp_get_upload_dir();
	$path = $url;

	$site_url   = wp_parse_url( $dir['url'] );
	$image_path = wp_parse_url( $path );

	//force the protocols to match if needed
	if ( isset( $image_path['scheme'] ) && ( $image_path['scheme'] !== $site_url['scheme'] ) ) {
		$path = str_replace( $image_path['scheme'], $site_url['scheme'], $path );
	}

	if ( 0 === strpos( $path, $dir['baseurl'] . '/' ) ) {
		$path = substr( $path, strlen( $dir['baseurl'] . '/' ) );
	}

	return $path;
}

/**
 * Get image size
 *
 * @param string $file
 *
 * @return Mixed
 */
function get_image_size_of_link( $file ) {
	$default_results = array( '', $file );
	if ( ! is_image( $file ) ) {
		return $default_results;
	}
	preg_match_all( '(-\d+x\d+\.\w+$)', $file, $matches, PREG_PATTERN_ORDER );

	$found = end( $matches[0] );

	if ( empty( $found ) ) {
		return $default_results;
	}

	$arr      = explode( '.', $found );
	$size     = $arr[0];
	$ext      = $arr[1];
	$url_file = str_replace( $found, ".$ext", $file );

	return array( $size, $url_file );
}

/**
 * Check download limitation
 *
 * @param array $advance_file
 *
 * @return Mixed
 */
function is_under_limited_downloads( $advance_file ) {
	if ( isset( $advance_file->limit_downloads ) ) {
		return $advance_file->hits_count >= $advance_file->limit_downloads;
	} else {
		return false;
	}
}

/**
 * Is Expired or not
 *
 * @param array $advance_file
 *
 * @return Mixed
 */
function is_expired( $advance_file ) {
	if ( ! isset( $advance_file->expired_date ) ) {
		return false;
	}
	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$expired_date = date( 'm/d/Y', $advance_file->expired_date );
	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$today        = date( 'm/d/Y' );

	return $today >= $expired_date;
}

/**
 * Show File From Private Link
 */
function show_file_from_private_link() {
	$configs  = Pda_Helper::get_plugin_configs();
	$endpoint = $configs['endpoint'];
	if ( isset( $_GET[ $endpoint ] ) ) {
		$private_url  = sanitize_text_field( wp_unslash( $_GET[ $endpoint ] ) );
		$repository   = new PDA_Repository;
		$advance_file = $repository->get_advance_file_by_url( $private_url );
		if ( isset( $advance_file ) &&
		     $advance_file->is_prevented === "1" &&
		     ! is_under_limited_downloads( $advance_file ) &&
		     ! is_expired( $advance_file ) ) {
			$post_id = $advance_file->post_id;

			$post = $repository->get_post_by_id( $post_id );
			if ( isset( $post ) ) {
				$new_hits_count = isset( $advance_file->hits_count ) ? $advance_file->hits_count + 1 : 1;
				$repository->update_advance_file_by_id( $advance_file->ID, array( 'hits_count' => $new_hits_count ) );
			} else {
				printf(
					'<h2>%s</h2>',
					esc_html__( 'Sorry! Invalid post!', 'prevent-direct-access' )
				);
			}
			if ( isset( $post ) ) {
				download_file( $post );
			} else {
				$post = $repository->get_post_meta_by_post_id( $post_id );
				download_file_by_meta_value( $post );
			}
		} else {
			file_not_found();
		}
	} else {
		file_not_found();
	}
}

/**
 * Try to send file
 *
 * @param array $file
 *
 */
function try_to_send_file( $file ) {}

/**
 * Check mime type of file
 *
 * @param string $mime_type
 *
 * @return mime_type
 */
function is_pdf( $mime_type ) {
	return $mime_type == "application/pdf";
}

/**
 * Check mime type of file
 *
 * @param string $mime_type
 *
 * @return mime_type
 */
function is_video( $mime_type ) {
	return strstr( $mime_type, "video/" );
}

/**
 * Check images format
 *
 * @param array $file
 *
 * @return Mixed
 */
function is_image( $file ) {
	preg_match( '/\.(gif|jpg|jpe?g|tiff|png|bmp|webp)$/i', $file, $matches );

	return ! empty( $matches );
}

/**
 * Check mime type of file
 *
 * @param array $mime_type
 *
 * @return mime_type
 */
function is_audio( $mime_type ) {
	return strstr( $mime_type, "audio/" );
}

/**
 * Send file to client
 *
 * @param array $file
 *
 * @return Mixed
 */
function send_file_to_client( $file ) {

	if ( ! is_file( $file ) ) {
		file_not_found();
	}

	$mime = wp_check_filetype( $file );

	if ( false === $mime['type'] && function_exists( 'mime_content_type' ) ) {
		$mime['type'] = mime_content_type( $file );
	}
	if ( $mime['type'] ) {
		$mimetype = $mime['type'];
	} else {
		$mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );
	}

	if ( is_image( $file ) == false && is_pdf( $mimetype ) == false && is_video( $mimetype ) == false && is_audio( $mimetype ) == false ) {
		$file_name = wp_basename( $file );
		$file_name = str_replace( array( "\r", "\n", '"' ), '', $file_name );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
	}

	//set header
	header( 'Content-Type: ' . $mimetype ); // always send this
	$server_software = ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	if ( false === strpos( $server_software, 'Microsoft-IIS' ) ) {
		header( 'Content-Length: ' . filesize( $file ) );
	}

	$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
	$etag          = '"' . md5( $last_modified ) . '"';

	header( "Last-Modified: $last_modified GMT" );
	header( 'ETag: ' . $etag );
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
	header( 'X-Robots-Tag: none' );
	// Support for Conditional GET
	$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) ) : false;
	if ( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
		$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
	}
	$client_last_modified = trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) );
	// If string is empty, return 0. If not, attempt to parse into a timestamp
	$client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;
	// Make a timestamp for our most recent modification...
	$modified_timestamp = strtotime( $last_modified );

	if ( ( $client_last_modified && $client_etag )
		? ( ( $client_modified_timestamp >= $modified_timestamp ) && ( $client_etag == $etag ) )
		: ( ( $client_modified_timestamp >= $modified_timestamp ) || ( $client_etag == $etag ) )
	) {
		status_header( 304 );
		exit;
	}

	status_header( 200 );
	// phpcs:disable WordPress.WP.AlternativeFunctions
    readfile( $file );
    // phpcs:enable WordPress.WP.AlternativeFunctions
}

/**
 * Get full path of file
 *
 * @param array $original_file
 *
 * @return file_path
 */
function get_full_file_path( $original_file ) {
	$upload_base_dir = wp_upload_dir()['basedir'];
	$file_path       = $upload_base_dir . "/_pda" . $original_file;

	return $file_path;
}

/**
 * Check Advance File
 *
 * @param integer $id
 * @param array $original_file
 *
 * @return Mixed
 */
function _check_advance_file( $id, $original_file ) {
	$repository        = new PDA_Repository;
	$advance_file      = $repository->get_status_advance_file_by_post_id( $id, true );
	$is_file_protected = isset( $advance_file ) && $advance_file->is_prevented === "1";

	if ( $is_file_protected ) {
		$fap = Pda_Helper::get_fap_setting();
		if ( ! pda_free_check_fap_for_file( $id, $fap ) ) {
			file_not_found();
			exit();
		}
	}

	$file_path = get_full_file_path( $original_file );
	send_file_to_client( $file_path );
}

/**
 * Check FAP.
 *
 * @param integer $id Attachment ID.
 * @param string $fap File access permission.
 *
 * @return bool
 */
function pda_free_check_fap_for_file( $id, $fap ) {
	switch ( $fap ) {
		case 'admin_users':
			return Pda_Helper::is_admin_user_role();
		default:
			return apply_filters( 'pda_handle_file_author_permission', is_post_author( $id ), $id );
	}
}

/**
 * Check file found or not
 */
function file_not_found() {
	$page_404 = get_page_404();
	if ( isset( $page_404 ) && ! empty( $page_404 ) ) {
		header( "Location: " . $page_404, true, 301 );
	} else {
		header( "Location: " . get_site_url() . "/pda_404", true, 301 );
	}
}

/**
 * Remove number.
 *
 * @param integer $guid Attachment ID.
 * @param string $file_type
 *
 * @return Mixed
 */
function remove_crop_numbers( $guid, $file_type ) {
	$pattern = "/-\d+x\d+.$file_type$/";
	$result  = preg_replace( $pattern, ".$file_type", $guid );

	return $result;
}

/**
 * Download file by metavalue
 *
 * @param array $post
 *
 */
function download_file_by_meta_value( $post ) {
	$meta_value      = $post->meta_value;
	$upload_base_dir = wp_upload_dir()['basedir'] . '/';
	$filePath        = $upload_base_dir . $meta_value;

	send_file_to_client( $filePath );
}

/**
 * Download file
 *
 * @param array $post
 *
 * @return bool
 */
function download_file( $post ) {
	$fullPath = $post->guid;
	$wpDir           = ABSPATH; //Applications/MAMP/htdocs/abc/cdf/wordpress-2/
	$upload_base_dir = wp_upload_dir()['basedir']; //Applications/MAMP/htdocs/abc/cdf/wordpress-2/wp-content/uploads
	$upload_path     = str_replace( $wpDir, '', $upload_base_dir );
	$filePath = $upload_base_dir . '/' . get_post_meta( $post->ID, '_wp_attached_file', true );
	// $pattern = '/^((http|https|ftp):\/\/)?([^\/]+\/)/i';
	// $fullPath = preg_replace( $pattern, $wpDir, $fullPath );
	send_file_to_client( $filePath );
}

/**
 * Check whitelist list for user role
 */
function is_in_whitelist() {
	$user = wp_get_current_user();
	if ( 0 === $user->ID ) {
		return false;
	} else {
		$white_list_roles = get_option( 'whitelist_roles' );
		if ( is_array( $white_list_roles ) ) {
			$result = array_intersect( $white_list_roles, $user->roles );

			return ! empty( $result );
		} else {
			return false;
		}
	}
}


/**
 * Wrapper function to check whether the current user is post's author
 *
 * @param int $attachment_id The Attachment's ID
 *
 * @return bool
 *  false: User is anonymous or the post doesn't have the author.
 *  true: Current user ID equals to post's author ID.
 */
function is_post_author( $attachment_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Post does not have the author or attachment ID cannot find.
	if ( empty( get_post_field( 'post_author', $attachment_id, 'raw' ) ) ) {
		return false;
	}

	return (int) get_current_user_id() === (int) get_post_field( 'post_author', $attachment_id, 'raw' );
}
