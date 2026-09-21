<?php
/**
*
* Handle files as protected, move, attachment
*
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.WP.AlternativeFunctions
// Check if class exists or not
if(!class_exists("Pda_Free_Handle")) {
    // Class PDA Free Handle
    class Pda_Free_Handle {

        /**
         * Move file to PDA
         *
         * @param integer $post_id
         *
         * @return mixed
         */
        function move_file_to_pda($post_id) {

            //Move file to _pda
            $file = get_post_meta( $post_id, '_wp_attached_file', true );
            if ( 0 === stripos( $file, $this->mv_upload_dir( '/' ) ) ) {
                return new WP_Error( 'protected_file_existed', sprintf(
                    __( 'This file is already protected. Please reload your page.', 'prevent-direct-access' ),
                    $file
                ), array( 'status' => 500) );
            }

            $reldir = dirname( $file );
            if ( in_array( $reldir, array( '\\', '/', '.' ), true ) ) {
                $reldir = '';
            }

            $protected_dir = path_join($this->mv_upload_dir(), $reldir );
            $move_result = $this->move_attachment_to_protected($post_id, $protected_dir);
            
            // Check error
            if ( is_wp_error($move_result) ) {
                return $move_result;
            } else {
                return $this->updated_file_protection( $post_id, true );
            }
        }

        /**
         * Unprotected file
         *
         * @param integer $post_id
         *
         * @return mixed
         */
        function un_protect_file( $post_id ) {

            // Get attachment file meta
            $file = get_post_meta( $post_id, '_wp_attached_file', true );

            // check if files are already not in the Media Vault protected folder
            if ( 0 !== stripos( $file, $this->mv_upload_dir( '/' ) ) )
                return true;

            $protected_dir = ltrim( dirname( $file ), $this->mv_upload_dir( '/' ));
            $move_result   = $this->move_attachment_to_protected( $post_id, $protected_dir );

            // Check error
            if( is_wp_error($move_result) ) {
                return $move_result;
            }

            return $this->updated_file_protection( $post_id, false );
        }

        /**
         * Move Upload Directory
         *
         * @param string $path
         * @param boolean $in_url
         *
         * @return mixed
         */
        public function mv_upload_dir( $path = '', $in_url = false ) {

            // Directory path
            $dirpath = $in_url ? '/' : '';
            $dirpath .= '_pda';
            $dirpath .= $path;

            return $dirpath;

        }

        /**
         * Move attachment to protected
         *
         * @param integer $attachment_id
         * @param string $protected_dir
         * @param array $meta_input
         *
         * @return mixed
         */
        public function move_attachment_to_protected($attachment_id, $protected_dir, $meta_input = []) {

            // Check attachment
            if( 'attachment' !== get_post_type( $attachment_id ) ) {
                return new WP_Error( 'not_attachment', sprintf(
                    // translators: %d is the ID of the post being checked.
                    __( 'The post with ID: %d is not an attachment post type.', 'prevent-direct-access' ),
                    $attachment_id
                ), array( 'status' => 404) );
            }

            // Check Absolute Path
            if( path_is_absolute( $protected_dir ) ) {
                return new WP_Error( 'protected_dir_not_relative', sprintf(
                    // translators: %s is the path provided by the caller.
                    __( 'The new path provided: %s is absolute. The new path must be a path relative to the WP uploads directory.', 'prevent-direct-access' ),
                    $protected_dir
                ), array( 'status' => 404));
            }

            $meta = empty($meta_input) ? wp_get_attachment_metadata( $attachment_id ) : $meta_input;
            $meta = is_array($meta) ? $meta : array();

            $file = get_post_meta( $attachment_id, '_wp_attached_file', true );
            $backups = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true);

            $upload_dir = wp_upload_dir();

            $old_dir = dirname($file);

            // Check with Old Directory
            if ( in_array( $old_dir, array( '\\', '/', '.' ), true ) ) {
                $old_dir = '';
            }

            if ( $protected_dir === $old_dir ) {
                return true;
            }

            $old_full_path = path_join( $upload_dir['basedir'], $old_dir );
            $protected_full_path =  path_join( $upload_dir['basedir'], $protected_dir );

            // Create Full path of directory
            if ( !wp_mkdir_p( $protected_full_path ) ) {
                return new WP_Error( 'wp_mkdir_p_error', sprintf(
                    // translators: %s is the filesystem path of the directory which failed to be created or verified.
                    __( 'There was an error making or verifying the directory at: %s', 'prevent-direct-access' ),
                    $protected_full_path
                ), array( 'status' => 500) );
            }

            //Get all files
            $sizes = array();
            if( array_key_exists('sizes', $meta ) ) {
                $sizes = $this->get_files_from_meta( $meta['sizes'] );
            }
            $backup_sizes = $this->get_files_from_meta( $backups );

            $old_basenames = $new_basenames = array_merge(
                array( wp_basename( $file ) ),
                $sizes,
                $backup_sizes
            );

            // Get basename
            $orig_basename = wp_basename( $file );
            if( is_array( $backups ) && isset( $backups['full-orig'] ) ) {
                $orig_basename = $backups['full-orig']['file'];
            }

            $orig_filename = pathinfo( $orig_basename );
            $orig_filename = $orig_filename['filename'];

            $result = $this->resolve_name_conflict( $new_basenames, $protected_full_path, $orig_filename );
            $new_basenames = $result['new_basenames'];

            $this->rename_files( $old_basenames, $new_basenames, $old_full_path, $protected_full_path );

            $base_file_name = 0;

            //$new_attached_file = path_join( $protected_dir, $new_basenames[0] );
            if( !empty( $protected_dir ) ) {
                $new_attached_file = path_join( $protected_dir, $new_basenames[0] );
            } else {
                $new_attached_file = $new_basenames[0];
            }
            if( array_key_exists('file', $meta ) ) {
                $meta['file'] = $new_attached_file;
            }
            update_post_meta( $attachment_id, '_wp_attached_file', $new_attached_file );

            // Check newbase with oldbase
            if( $new_basenames[$base_file_name] != $old_basenames[$base_file_name] ) {
                $pattern = $result['pattern'];
                $replace = $result['replace'];
                $separator = "#";
                $orig_basename = ltrim (
                    str_replace( $pattern, $replace, $separator . $orig_basename ),
                    $separator
                );
                $meta = $this->update_meta_sizes_file($meta, $new_basenames);
                $this->update_backup_files($attachment_id, $backups, $new_basenames);
            }

            update_post_meta( $attachment_id, '_wp_attachment_metadata', $meta );
            $guid = path_join( $protected_full_path, $orig_basename );
            wp_update_post( array( 'ID' => $attachment_id, 'guid' => $guid ) );

            return empty($meta_input) ? true : $meta;
        }

        /**
         * Update file protection meta
         *
         * @param ineteger $post_id
         * @param boolean $is_protected
         *
         * @return mixed
         */
        public function updated_file_protection($post_id, $is_protected) {
            return update_post_meta( $post_id, "_pda_protection", $is_protected );
        }

        /**
         * Get files from meta
         *
         * @param string $input
         *
         * @return mixed
         */
        public function get_files_from_meta($input) {
            $files = array();
            if( is_array( $input ) ) {
                foreach( $input as $size ) {
                    $files[] = $size['file'];
                }
            }
            return $files;
        }

        /**
         * Resolve name conflict
         *
         * @param string $new_basename
         * @param string $protected_full_path
         * @param string $orig_file_name
         *
         * @return mixed
         */
        public function resolve_name_conflict($new_basenames, $protected_full_path, $orig_file_name) {
            $conflict = true;
            $number = 1;
            $separator = "#";
            $med_filename = $orig_file_name;
            $pattern = "";
            $replace = "";
            while($conflict) {
                $conflict = false;
                foreach( $new_basenames as $basename ) {
                    if( is_file ( path_join( $protected_full_path, $basename ) ) ) {
                        $conflict = true;
                        break;
                    }
                }

                if($conflict) {
                    $new_filename = "$orig_file_name-$number";
                    $number++;
                    $pattern = "$separator$med_filename";
                    $replace = "$separator$new_filename";
                    $new_basenames = explode(
                        $separator,
                        ltrim(
                            str_replace( $pattern, $replace, $separator . implode( $separator, $new_basenames ) ),
                            $separator
                        )
                    );

                }
            }

            return array(
                'new_basenames' => $new_basenames,
                'pattern' => $pattern,
                'replace' => $replace
            );
        }

        /**
         * Rename files
         *
         * @param string $old_basenames
         * @param string $new_basenames
         * @param string $old_dir
         * @param string $protected_dir
         *
         * @return mixed
         */
        public function rename_files($old_basenames, $new_basenames, $old_dir, $protected_dir) {
            $unique_old_basenames = array_values( array_unique( $old_basenames ) );
            $unique_new_basenames = array_values( array_unique( $new_basenames ) );
            $i = count( $unique_old_basenames );
            while( $i-- ) {
                $old_fullpath = path_join( $old_dir, $unique_old_basenames[$i] );
                $new_fullpath = path_join( $protected_dir, $unique_new_basenames[$i] );
                if( is_file( $old_fullpath ) ) {
                    rename( $old_fullpath, $new_fullpath );

                    if( !is_file($new_fullpath) ) {
                        return new WP_Error(
                            'rename_failed',
                            sprintf(
                                // translators: %1$s is the source path, %2$s is the destination path. Keep placeholders numbered so translators can reorder if necessary.
                                __( 'Rename failed when trying to move file from: %1$s, to: %2$s', 'prevent-direct-access' ),
                                $old_fullpath,
                                $new_fullpath
                            )
                        );
                    }
                }

            }
        }

        /**
         * Update meta size files
         *
         * @param string $meta
         * @param string $new_basenames
         *
         * @return mixed
         */
        public function update_meta_sizes_file($meta, $new_basenames) {
            if ( is_array( $meta['sizes'] ) ) {
                $i = 0;

                foreach ( $meta['sizes'] as $size => $data ) {
                    $meta['sizes'][$size]['file'] = $new_basenames[++$i];
                }
            }
            return $meta;
        }

        /**
         * Update backup files
         *
         * @param integer $attachment_id
         * @param string $backups
         * @param string $new_basenames
         *
         * @return mixed
         */
        public function update_backup_files($attachment_id, $backups, $new_basenames) {
            if ( is_array( $backups ) ) {
                $i = 0;
                $l = count( $backups );
                $new_backup_sizes = array_slice( $new_basenames, -$l, $l );

                foreach ( $backups as $size => $data ) {
                    $backups[$size]['file'] = $new_backup_sizes[$i++];
                }
                update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backups );
            }
        }
    }
}
