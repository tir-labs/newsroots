<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
*
* Auto Load Output
*
*/
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// phpcs:disable WordPress.WP.AlternativeFunctions
if (!class_exists('PDA_ViewLoader')) {
    class PDA_ViewLoader
    {
        public static function render_custom_column($post_id)
        {
            $repo = new PDA_v3_Gold_Repository;
            $is_protected_file = $repo->is_protected_file($post_id);
            $pda_class = $is_protected_file ? '' : PDA_v3_Constants::PDA_V3_CLASS_FOR_FILE_UNPROTECTED;
            $pda_text = $is_protected_file ? PDA_v3_Constants::PDA_V3_FILE_PROTECTED : PDA_v3_Constants::PDA_V3_FILE_UNPROTECTED;
            $title_text = $is_protected_file ? PDA_v3_Constants::PDA_V3_TITLE_FOR_FILE_PROTECTED : PDA_v3_Constants::PDA_V3_TITLE_FOR_FILE_UNPROTECTED;
            $pda_icon = $is_protected_file ? '<i class="fa fa-check-circle" aria-hidden="true"></i>' : '<i class="fa fa-times-circle" aria-hidden="true"></i>';
            ?>
            
            <div id="pda-v3-column_<?php echo esc_attr( $post_id ); ?>" class="pda-gold-v3-tools">
                <p id="pda-v3-wrap-status_<?php echo esc_attr( $post_id ); ?>">
                    <span id="pda-v3-text_<?php echo esc_attr( $post_id ); ?>" class="protection-status <?php echo esc_attr( $pda_class ); ?>" title="<?php echo esc_attr( $title_text ); ?>">
                        <?php echo wp_kses_post( $pda_icon ); ?>
                        <?php echo esc_html( $pda_text ); ?>
                    </span>
                    <?php do_action( PDA_Private_Hooks::PDA_HOOK_SHOW_STATUS_FILE_IN_PDA_COLUMN, $post_id ); ?>
                </p>
                <div>
                    <a class="pda_gold_btn" id="pda_gold-<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html__( 'Configure file protection', 'prevent-direct-access' ) ?></a>
                </div>
            </div>
            <?php
        }

        public static function render_helpers()
        {
            $home_path = get_home_path();
            global $is_apache;
            if ( $is_apache ) { 
                $btn_name = Pda_Gold_Functions::is_fully_activated()
                    ? __( 'Check .htaccess file', 'prevent-direct-access' )
                    : __( 'Check .htaccess files', 'prevent-direct-access' );

                $open_message = __(
                    'If your .htaccess file were writable, Prevent Direct Access Gold could do this automatically,',
                    'prevent-direct-access' 
                );

                $end_message = __(
                    'but it isn’t, so these are the mod_rewrite rules you should have in your .htaccess file to start protecting your files. Click in the field and press CTRL + A to select all.',
                    'prevent-direct-access'
                );
            } else {
                $btn_name = __( 'Check rewrite rules', 'prevent-direct-access' );

                $open_message = __(
                    'It looks like you’re using the Nginx web server. Since Nginx does not have .htaccess-type capability,',
                    'prevent-direct-access'
                );

                $end_message = __(
                    'Prevent Direct Access Gold cannot update your server configuration automatically for you. Here’s how you can do it manually:',
                    'prevent-direct-access'
                );
            } ?>
            <div class="main_container">
                <?php if (!Pda_Gold_Functions::is_fully_activated()) : ?>
                <p>
                    <?php

                    echo esc_html( $open_message );

            if (! is_writable($home_path . '.htaccess')) {
				$errors['Nonwritable'] = sprintf(
					/* translators: %s: file name (e.g. .htaccess) */
					esc_html__( "The site's %s file is not writable.", 'prevent-direct-access' ),
					'.htaccess'
				);
            }

            $err_txt = '';

            $err_txt = '';

			if ( ! empty( $errors ) ) {
				$last = array_pop( $errors );

				if ( count( $errors ) > 0 ) {
					$err_txt .= implode( ', ', $errors ) . ' ' . __( 'and', 'prevent-direct-access' ) . ' ';
				}

				$err_txt .= $last . ', ';
			}

			printf(
				'%s%s',
				esc_html( $err_txt ),
				esc_html( $end_message )
			);

            echo ' ';
             ?>
                </p>
                <ol>
                    <li>
                        <?php if ($is_apache) : ?>
                            <p>
                                <?php
                                $rewrite_file_type = wp_kses_post( '<code>.htaccess</code>' );
                                $rewrite_file_loc  = wp_kses_post( '<code>' . esc_html( $home_path ) . '</code>' );
            $rewrite_rule_loc  = sprintf(wp_kses(
                /* translators: %1$s = start marker (# BEGIN WordPress), %2$s = end marker (# END WordPress), %3$s = sample rewrite rule line */
                __('<strong>in the WordPress rewrite block</strong> (the WordPress block usually starts with %1$s and ends with %2$s, <strong>just below</strong> the line reading %3$s', 'prevent-direct-access'), array( 'strong' => array() ), false), '<code># BEGIN WordPress</code>', '<code># END WordPress</code>', '<code>RewriteRule ^index\.php$ - [L]</code>');

            if (! is_multisite() && ! get_option('permalink_structure')) {
                $rewrite_rule_loc = __('<strong>above</strong> any other rewrite rules in the file.', 'prevent-direct-access');

                printf(wp_kses(
                    /* translators: %1$s is a link to a pretty permalinks help page, %2$s is a link to enabling permalinks. */
                    __('PDA Gold works best with %1$s enabled, so it is strongly recommended that you %2$s! If, however, you really <i>really</i> want to use ugly permalinks, then...', 'prevent-direct-access'), array( 'i' => array() ), false), '<a href="http://codex.wordpress.org/Introduction_to_Blogging#Pretty_Permalinks" target="_blank">' . esc_html__('Pretty Permalinks', 'prevent-direct-access') . '</a>', '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">' . esc_html__('enable them', 'prevent-direct-access') . '</a>');
                echo "\n";
            }
            printf(
                wp_kses(
                    /* translators: %1$s is the type of file (e.g. .htaccess), %2$s is the file path. */
                    __( 'Add the following rules to your %1$s file located at %2$s', 'prevent-direct-access' ),
                    array( 'code' => array() ),
                    false
                ),
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post on lines 110-111.
                $rewrite_file_type,
                $rewrite_file_loc // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post on lines 110-111.
            );
            echo ' ', wp_kses_post( $rewrite_rule_loc ); ?>
                                <?php $rules = Prevent_Direct_Access_Gold_Htaccess::get_the_rewrite_rules(); ?>
                            </p>
                        <?php else : ?>
                            <p>
                                Update our rewrite rules on your Ngnix server <a target="_blank" href="https://preventdirectaccess.com/docs/nginx-support/">as per this instructions</a>
                            </p>
	                        <?php $rules = Prevent_Direct_Access_Gold_Htaccess::get_nginx_rules(); ?>
                        <?php endif; ?>
                        <textarea class="code" readonly="readonly" cols="90" rows="<?php echo count($rules); ?>"><?php echo esc_textarea(implode("\n", $rules)); ?></textarea>
                    </li>
                    <li>
                        <p>
                            <?php esc_html_e('Once done, please click on the button below to check if the rewrite rules are inserted correctly', 'prevent-direct-access'); ?>
                        </p>
                        <form method="post" id="enable_pda_v3_form">
                            <?php wp_nonce_field('pda_ajax_nonce_v3', 'nonce_pda_v3'); ?>
                            <?php submit_button($btn_name, 'primary', 'enable_pda_v3', false); ?>
                        </form>
                    </li>
                </ol>
                <p>
                    Or using raw redirect URL
                    <div>
                        <form method="post" id="enable_pda_v3_raw_url">
			                <?php wp_nonce_field('pda_ajax_nonce_v3', 'nonce_pda_v3'); ?>
			                <?php submit_button(__('Use raw redirect URL', 'prevent-direct-access'), 'primary', 'enable_raw_url', false); ?>
                        </form>
                    </div>
                </p>
                <?php else: ?>
	                <?php if ($is_apache) : ?>
                        <p>
			                <?php
                            $rewrite_file_type = wp_kses_post( '<code>.htaccess</code>' );
                            $rewrite_file_loc  = wp_kses_post( '<code>' . esc_html( $home_path ) . '</code>' );
            $rewrite_rule_loc  = sprintf(wp_kses(
                            /* translators: %1$s = start marker (# BEGIN WordPress), %2$s = end marker (# END WordPress), %3$s = sample rewrite rule line */
                            __('<strong>within your WordPress rewrite block</strong>, which usually starts with %1$s and ends with %2$s, and <strong>just below</strong> the line reading %3$s', 'prevent-direct-access'), array( 'strong' => array() ), false), '<code># BEGIN WordPress</code>', '<code># END WordPress</code>', '<code>RewriteRule ^index\.php$ - [L]</code>');

            if (! is_multisite() && ! get_option('permalink_structure')) {
                $rewrite_rule_loc = __('<strong>above</strong> any other rewrite rules in the file.', 'prevent-direct-access');

                printf(wp_kses(
                            /* translators: %1$s is a link to a pretty permalinks help page, %2$s is a link to enabling permalinks. */
                            __('PDA Gold works best with %1$s enabled, so it is strongly recommended that you %2$s! If, however, you really <i>really</i> want to use ugly permalinks, then...', 'prevent-direct-access'), array( 'i' => array() ), false), '<a href="http://codex.wordpress.org/Introduction_to_Blogging#Pretty_Permalinks" target="_blank">' . esc_html__('Pretty Permalinks', 'prevent-direct-access') . '</a>', '<a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">' . esc_html__('enable them', 'prevent-direct-access') . '</a>');
                echo "\n";
            }
            printf(
                wp_kses(
                    __( 'If the original links of your protected files are still accessible and/or their private links don\'t work, please make sure our rewrite rules are inserted correctly on your .htaccess file by clicking on the button below.', 'prevent-direct-access' ),
                    array(),
                    false
                ),
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post
                $rewrite_file_type,
                $rewrite_file_loc // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post
            );
            echo "<p>";
            printf(
                wp_kses(
                    /* translators: %1$s is the type of file (e.g. .htaccess), %2$s is the file path. */
                    __( 'The following rules should be added into your %1$s file located at %2$s', 'prevent-direct-access' ),
                    array( 'code' => array() ),
                    false
                ),
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post
                $rewrite_file_type,
                $rewrite_file_loc // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via wp_kses_post
            );
            echo ' ', wp_kses_post( $rewrite_rule_loc ), "</p>"; ?>
			                <?php $rules = Prevent_Direct_Access_Gold_Htaccess::get_the_rewrite_rules(); ?>
                            <textarea class="code" readonly="readonly" cols="90" rows="<?php echo count( $rules ); ?>"><?php echo esc_textarea( implode( "\n", $rules ) ); ?></textarea>
                        </p>
                    <?php elseif( self::is_server( 'microsoft-iis' ) ) : ?>
                        <p>
                            <a target="_blank" href="https://preventdirectaccess.com/docs/prevent-direct-access-lite/#IIS">Guides for Microsoft IIS server!</a>
                        </p>
		                <?php $rules = Prevent_Direct_Access_Gold_Htaccess::get_iis_rules(); ?>
                        <textarea class="code" readonly="readonly" cols="90" rows="<?php echo count( $rules ); ?>"><?php echo esc_textarea( implode( "\n", $rules ) ); ?></textarea>
	                <?php elseif( self::is_server( 'nginx'  ) ) : ?>
                        <p>
                            Update our rewrite rules on your Ngnix server <a target="_blank" href="https://preventdirectaccess.com/docs/nginx-support/">as per this instructions</a>
                        </p>
		                <?php $rules = Prevent_Direct_Access_Gold_Htaccess::get_nginx_rules(); ?>
                        <textarea class="code" readonly="readonly" cols="90" rows="<?php echo count( $rules ); ?>"><?php echo esc_textarea( implode( "\n", $rules ) ); ?></textarea>
	                <?php endif; ?>
                    <p>
                        <form method="post" id="enable_pda_v3_form_no_reload">
		                    <?php wp_nonce_field( 'pda_ajax_nonce_v3', 'nonce_pda_v3' ); ?>
		                    <?php submit_button( $btn_name, 'primary', 'enable_pda_v3', false ); ?>
                        </form>
                    </p>
                <?php endif ?>
            </div>
		    <?php
	    }

	    public static function is_server( $server ) {
		    $server_info = isset( $_SERVER['SERVER_SOFTWARE'] ) ?  sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) )  : '';
		    return strpos( strtolower( $server_info ), $server ) !== false;
	    }

	    public static function render_iis_server() {

	    }
    }


}
