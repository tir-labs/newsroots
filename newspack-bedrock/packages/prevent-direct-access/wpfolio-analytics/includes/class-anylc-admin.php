<?php
/**
 * Admin Class
 *
 * Handles the admin functionality
 *
 * @package WPFolio Pda Analytic
 * @since 1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPFolio_Pda_Anylc_Admin {

	function __construct() {

		global $wpfolio_pda_analytics_module;

		// Plugin action links
		if( ! empty( $wpfolio_pda_analytics_module ) ) {
			foreach ($wpfolio_pda_analytics_module as $module_key => $module) {

				// Filter to add Opt In / Out row
				add_filter( 'plugin_action_links_' . $module_key, array($this, 'wpfolio_pda_anylc_add_action_links'), 10, 4 );
			}
		}

		// Action to remove admin menu
		add_action( 'admin_menu', array($this, 'wpfolio_pda_anylc_remove_admin_menu'), 999 );

		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpfolio_pda_anylc_register_admin_menu'), 15 );

		// Action to redirect plugin / theme on activation
		add_action( 'admin_init', array($this, 'wpfolio_pda_anylc_admin_init_process') );

		// Action to show optin notice
		add_action( 'admin_notices', array($this, 'wpfolio_pda_anylc_optin_notice') );

		// Action to add Attachment Popup HTML
		add_action( 'admin_footer', array($this,'wpfolio_pda_anylc_optout_popup') );

		add_action( 'admin_init', [ $this, 'redirect_optin_page' ] );

		// Action to perform analytic action
		add_action( 'wp_loaded', array($this, 'wpfolio_pda_anylc_action_process') );
	}

    /**
	 * Remove admin menus
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_remove_admin_menu() {
		global $menu, $submenu, $wpfolio_pda_analytics_module;
	    if( !empty( $wpfolio_pda_analytics_module ) ) {
	    	foreach ($wpfolio_pda_analytics_module as $module_key => $module) {
	    		$opt_in_data = wpfolio_pda_anylc_get_option( $module['anylc_optin'] );
	    		if( !empty( $module['slug'] ) && !isset( $opt_in_data['status'] ) ) {
	    			remove_menu_page( $module['slug'] );
	    		}
	    	}
	    }
	}

	/**
	 * Add menu
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_register_admin_menu() {

		global $menu, $submenu, $wpfolio_pda_analytics_module;

		



	    if( !empty( $wpfolio_pda_analytics_module ) ) {

	    	// WP Menu data
	    	$wpfolio_pda_menu_data = wp_list_pluck( $menu, 2 );
	    	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$anylc_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : null;

	    	foreach ($wpfolio_pda_analytics_module as $module_key => $module) {

	    		$opt_in_data 	= wpfolio_pda_anylc_get_option( $module['anylc_optin'] );
	    		$optin_status	= isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : null;

	    		// Offers Page
	    		if( !empty( $module['offers'] ) && $anylc_page == $module['slug'].'-offers' ) {
	    			add_submenu_page( $module['menu'], 'WPFOLIO Offers', '<span style="color:#2ECC71">Premium Offers</span>', 'manage_options', $module['slug'].'-offers', array($this, 'wpfolio_pda_anylc_offers_html') );
	    		}

				// If data is set
				if( $optin_status == 1 ) {
					continue;
				}

	    		// Taking some variables
	    		$menu_args = array();

	    		if( $optin_status === 0 || $optin_status === 2 ) {


	    			// Register admin menu
	    			if( $anylc_page == $module['tempslug'] ) {

						add_submenu_page( $module['menu'], $module['name'].' '.'Opt In', $module['name'].' '.'Opt In', 'manage_options', $module['tempslug'], array($this, 'wpfolio_pda_anylc_page_html') );
	    			}

	    		} else {

	    			if( !empty( $wpfolio_pda_menu_data ) ) {
			    		$orig_menu_pos = array_search( $module['menu'], $wpfolio_pda_menu_data );

			    		if( $orig_menu_pos !== false ) {

			    			$menu_args = array(
		    								'name' 		=> $menu[ $orig_menu_pos ][0],
		    								'icon' 		=> $menu[ $orig_menu_pos ][6],
		    								'position'	=> $orig_menu_pos,
		    							);
			    		}
			    	}

			    	// Taking default name and icon
			    	if( empty( $menu_args ) ) {
			    		$menu_args = array(
	    								'name' 		=> $module['name'],
	    								'icon' 		=> false,
	    								'position'	=> null,
	    							);
			    	}

			    	// Register admin menu
					add_menu_page( $menu_args['name'], $menu_args['name'], 'manage_options', $module['tempslug'], array($this, 'wpfolio_pda_anylc_page_html'), $menu_args['icon'], $menu_args['position'] );
	    		}

	    	} // End of for each
	    }
	}

	/**
	 * Display Opt in form HTML
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_page_html() {

		global $current_user, $wpfolio_pda_analytics_product;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$anylc_product_name = '';

		if ( isset( $_GET['page'] ) ) {
			$anylc_product_name = sanitize_text_field(
				wp_unslash( $_GET['page'] )
			);
		}

		$anylc_product_name = str_replace( '_optin', '', $anylc_product_name );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// if no data is set then return.
		if( ! isset( $wpfolio_pda_analytics_product[ $anylc_product_name ] ) ) {
			return;
		}

		// Taking soem data
		$optin_form_data	= wpfolio_pda_anylc_optin_data();

		$analy_product 		= $wpfolio_pda_analytics_product[ $anylc_product_name ];
		$opt_in_data 		= wpfolio_pda_anylc_get_option( $analy_product['anylc_optin'] );

		$opt_in 			= isset( $opt_in_data['status'] ) 		? $opt_in_data['status'] 	: null;
		$user_name 			= !empty( $current_user->first_name ) 	? $current_user->first_name : '';
		$user_name 			= empty( $user_name ) 					? $current_user->nickname 	: $user_name;
		$product_name 		= $analy_product['name'];

		$skip_url 	= add_query_arg( array( 'page' => $anylc_product_name, 'wpfolio_pda_anylc_action' => 'skip'), admin_url('admin.php') );
		$skip_url	= wp_nonce_url( $skip_url, 'wpfolio_pda_anylc_act' );

	    require_once WPFOLIO_PDA_ANYLC_DIR .'/templates/analytic.php';
	}

	/**
	 * Display Offers HTML
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_offers_html() {

		global $wpfolio_pda_analytics_product;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$anylc_product_name = isset( $_GET['page'] )
			? sanitize_text_field( wp_unslash( $_GET['page'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$anylc_product_name = str_replace( '-offers', '', $anylc_product_name );

		// if no data is set then return
		if( ! isset( $wpfolio_pda_analytics_product[ $anylc_product_name ] ) ) {
			return;
		}

		// Taking soem data
		$analy_product 	= $wpfolio_pda_analytics_product[ $anylc_product_name ];
		$opt_in_data 	= wpfolio_pda_anylc_get_option( $analy_product['anylc_optin'] );
		$opt_in 		= isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : null;

		include_once( WPFOLIO_PDA_ANYLC_DIR .'/templates/offers.php' );
	}

	/**
	 * Add Action links
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_add_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		global $wpfolio_pda_analytics_module;

		// Taking some data
		$module_data = !empty( $wpfolio_pda_analytics_module[ $plugin_file ] ) ? $wpfolio_pda_analytics_module[ $plugin_file ] : '';

		// If analytics module data is there
		if( $module_data ) {

			$opt_in_data 	= wpfolio_pda_anylc_get_option( $module_data['anylc_optin'] );
			$opt_in 		= isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : -1;

			// If user has opt in
			if( $opt_in == 1 ) {

				$new_links['wpfolio_pda_anylc'] = '<a href="#" class="wpfolio-pda-anylc-opt-out-link" data-id="'.esc_attr( $module_data['id'] ).'">'.esc_html__('Opt Out','prevent-direct-access').'</a>';

			} else {

				$opt_in_link = wpfolio_pda_anylc_optin_url( $module_data, $opt_in );

				$new_links['wpfolio_pda_anylc'] = '<a href="'.esc_url( $opt_in_link ).'" class="wpfolio-pda-anylc-opt-in-link">'.esc_html__('Opt In','prevent-direct-access').'</a>';
			}

			$actions = array_merge( $new_links, $actions );
		}
		return $actions;
	}

	/**
	 * Redirect plugin / theme on activation to its opt in menu
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_admin_init_process() {
		
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['message'], $_GET['anylc_id'], $_GET['_wpnonce'] ) ) {

			$message  = sanitize_text_field( wp_unslash( $_GET['message'] ) );
			$anylc_id = sanitize_text_field( wp_unslash( $_GET['anylc_id'] ) );
			$nonce    = sanitize_key( wp_unslash( $_GET['_wpnonce'] ) );
		
			if (
				'wpfolio-pda-anylc-dismiss-notice' === $message &&
				wp_verify_nonce( $nonce, 'wpfolio-pda-anylc-dismiss-notice-nonce' )
			) {
		
				set_transient(
					'wpfolio_pda_anylc_optin_notice_' . $anylc_id,
					true,
					172800
				);
			}
		}
		/* if( isset( $_GET['message'] ) && 'wpfolio-pda-anylc-dismiss-notice' == $_GET['message'] && ! empty( $_GET['anylc_id'] )
			&& isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'wpfolio-pda-anylc-dismiss-notice-nonce' ) 
			) {
				$anylc_id = sanitize_text_field( wp_unslash( $_GET['anylc_id'] ) );
				set_transient( 'wpfolio_pda_anylc_optin_notice_'.$anylc_id, true, 172800 );
		} */

		// Flush the redirect transient
		if ( isset( $_GET['anylc_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['anylc_nonce'] ) ), 'wpfolio-pda-anylc-redirect-nonce' ) ) {
			update_option( 'wpfolio_pda_anylc_redirect', '' );
		}

		// Check if any redirect is set after plugin activation
		$redirect = get_option( 'wpfolio_pda_anylc_redirect' );

		if ( $redirect ) {

			/**
			 * Little Tweak to avoid the infinite looping.
			 */
			parse_str( wp_parse_url( $redirect, PHP_URL_QUERY ), $url_data );
			$nonce_get = isset( $_GET['anylc_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['anylc_nonce'] ) ) : '';
			if( ! isset( $url_data['anylc_nonce'] ) || ( isset( $url_data['anylc_nonce'] ) && ! wp_verify_nonce( $nonce_get, 'wpfolio-pda-anylc-redirect-nonce' ) ) ) {
				$redirect = add_query_arg( array( 'anylc_nonce' => wp_create_nonce( 'wpfolio-pda-anylc-redirect-nonce' ) ), $redirect );
			}

			// Redirect to page
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Display Analytic Optin notice
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_optin_notice() {

		global $current_screen, $wpfolio_pda_analytics_module, $wpfolio_pda_analytics_product;

		// Taking some variables
		$screen_id = isset( $current_screen->id ) ? $current_screen->id : '';

		// Plugin action links
		if( $screen_id == 'dashboard' && current_user_can('manage_options') && !empty( $wpfolio_pda_analytics_module ) ) {
			foreach ($wpfolio_pda_analytics_module as $module_key => $module) {

				$anylc_pdt_id		= $module['id'];
				$notice_transient 	= get_transient( 'wpfolio_pda_anylc_optin_notice_'.$anylc_pdt_id );

				if( $notice_transient == false ) {

					$opt_in_data 	= wpfolio_pda_anylc_get_option( $module['anylc_optin'] );
					$opt_in 		= isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : -1;
					$notice_link = add_query_arg( array( 'message' => 'wpfolio-pda-anylc-dismiss-notice', 'anylc_id' => $anylc_pdt_id, '_wpnonce' => wp_create_nonce( 'wpfolio-pda-anylc-dismiss-notice-nonce' ) ), admin_url('index.php') );

					// If user has opt in
					if( $opt_in == -1 ) {

						$anylc_pdt_name 	= $module['name'];
						$anylc_optin_url 	= wpfolio_pda_anylc_optin_url( $module, $opt_in );

						echo '<div class="updated notice wpfolio-pda-anylc-notice wpfolio-pda-anylc-optin-notice">
						<p><strong>'.wp_kses_post( $anylc_pdt_name ).'</strong> - We made a few tweaks to the plugin, <a href="'.esc_url( $anylc_optin_url ).'">Opt in to make it Better!</a></p>
						<a href="'.esc_url( $notice_link ).'" class="notice-dismiss"></a>
						</div>';


					}
				}
			}
		} // End of if
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if( isset($_GET['message']) && $_GET['message'] == 'optout_success' ) {
			echo '<div class="updated notice wpfolio-pda-anylc-optin-notice is-dismissible">
					<p><strong>Sorry to let you go. You are now opted out from the plugin.</strong></p>
				</div>';
		}

		// Process Promotion Data
    	if( !empty($_GET['message']) && $_GET['message'] == 'wpfolio_pda_anylc_promotion' && !empty($_GET['wpfolio_pda_anylc_pdt']) && !empty($_GET['wpfolio_pda_anylc_promo_pdt']) ) {

    		$promotion 				= 1;
    		$wpfolio_pda_anylc_promo_pdt	= sanitize_text_field( wp_unslash( $_GET['wpfolio_pda_anylc_promo_pdt'] ) );
    		$promotion_pdt			= explode( ',', $wpfolio_pda_anylc_promo_pdt );

    		$anylc_pdt 		= sanitize_text_field( wp_unslash( $_GET['wpfolio_pda_anylc_pdt'] ) );
			$anylc_pdt_data = isset( $wpfolio_pda_analytics_product[ $anylc_pdt ] ) ? $wpfolio_pda_analytics_product[ $anylc_pdt ] : false;

			if( !empty($promotion_pdt) ) {
				foreach ($promotion_pdt as $pdt_key => $pdt) {
					if( isset( $anylc_pdt_data['promotion'][$pdt]['file'] ) ) {
						$promotion_pdt_data[] = '<a href="'.esc_url( $anylc_pdt_data['promotion'][$pdt]['file'] ).'">'.esc_html( $anylc_pdt_data['promotion'][$pdt]['name'] ).'</a>';
					}
				}
			}

			if( $promotion_pdt_data ) {
				echo '<div class="updated notice wpfolio-pda-anylc-optin-notice is-dismissible" style="display:block !important;">
						<p><strong>Your Download has been started. In case if it is intrupted then download it from here. '.wp_kses_post( join(' | ', $promotion_pdt_data) ).'</strong></p>
					</div>';
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Analytic Optout Popup HTML
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_optout_popup() {

		global $pagenow, $wpfolio_pda_analytics_module;

		if( $pagenow == 'plugins.php' && !empty( $wpfolio_pda_analytics_module ) ) {
			foreach ($wpfolio_pda_analytics_module as $module_key => $module) {

				$opt_in_data 	= wpfolio_pda_anylc_get_option( $module['anylc_optin'] );
				$opt_in 		= isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : false;

				// If user has opt in
				if( $opt_in == 1 ) {

					// Creating redirect URL (plugins list view state only; redirect URL is nonced via wp_nonce_url below).
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view state for redirect URL only.
					$plugin_status 	= isset( $_GET['plugin_status'] ) 	? sanitize_text_field( wp_unslash( $_GET['plugin_status'] ) ) 	: false;
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view state for redirect URL only.
					$paged 			= isset( $_GET['paged'] ) 			? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) 			: false;
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading view state for redirect URL only.
					$s 				= isset( $_GET['s'] ) 				? sanitize_text_field( wp_unslash( $_GET['s'] ) ) 				: false;

					$redirect_url 	= add_query_arg( array( 'plugin_status' => $plugin_status, 'paged' => $paged, 's' => $s, 'wpfolio_pda_anylc_pdt' => $module['slug'] ), admin_url( 'plugins.php' ) );
					$redirect_url	= wp_nonce_url( $redirect_url, 'wpfolio_pda_anylc_act'.'|'.$module['slug'] );

					// Form Data
					$optin_form_data = wpfolio_pda_anylc_optin_data( $module['slug'], $redirect_url );

					include( WPFOLIO_PDA_ANYLC_DIR .'/templates/optout-popup.php' );
				}
			}
		}
	}

	/**
	 * Handles analytic action redirection.
	 *
	 * @package WPFolio_PDA_Analytics
	 * @since 1.0
	 */

	public function redirect_optin_page() {

        // Ensure user is logged in and in wp-admin
        if ( ! is_user_logged_in() || ! is_admin() ) {
            return;
        }

        // Your existing logic
        global $wpfolio_pda_analytics_module;

        $plugin = 'prevent-direct-access/prevent-direct-access.php';
        
        $opt_in_data  = get_option( $wpfolio_pda_analytics_module[ $plugin ]['anylc_optin'] );
        $optin_status = isset( $opt_in_data['status'] ) ? $opt_in_data['status'] : -1;

        // Get the current page slug.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading page for redirect check only.
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        // Redirect only when opt-in not done
        if ( $optin_status == -1 && $page === 'wp_pda_options' ) {
            $redirect_url = add_query_arg(
                'page',
                'wp_pda_options_optin',
                admin_url( 'admin.php' )
            );

           wp_safe_redirect( $redirect_url );
            exit;
        }
    }
	/**
	 * Analytic Action Process
	 * 
	 * @package WPFolio Pda Analytic
	 * @since 1.0
	 */
	function wpfolio_pda_anylc_action_process() {

		// Skip if not admin area
		if ( !is_admin() ) {
			return false;
		}

		if( !empty($_GET['wpfolio_pda_anylc_action']) && isset($_GET['_wpnonce']) ) {

			global $wpfolio_pda_analytics_product;

			$anylc_pdt 		= !empty( $_GET['wpfolio_pda_anylc_pdt'] ) 				? sanitize_text_field( wp_unslash( $_GET['wpfolio_pda_anylc_pdt'] ) ) 	: '';
			$anylc_pdt 		= ( ! $anylc_pdt && !empty( $_GET['page'] ) ) 		? sanitize_text_field( wp_unslash( $_GET['page'] ) ) 				: $anylc_pdt;

			$anylc_pdt = str_replace('_optin','',$anylc_pdt);

			$anylc_pdt_data = isset( $wpfolio_pda_analytics_product[ $anylc_pdt ] )	? $wpfolio_pda_analytics_product[ $anylc_pdt ]				: false;

			// If valid product data found
			if( $anylc_pdt_data ) {

				// Process Optin
				if ( isset( $_GET['wpfolio_pda_anylc_action'] ) && 'optin' === sanitize_key( wp_unslash( $_GET['wpfolio_pda_anylc_action'] ) ) ) {

					// Verify nonce
					if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpfolio_pda_anylc_act' ) ) {
						wp_die( esc_html__('Sorry, Something happened wrong.', 'prevent-direct-access'), 'wpfolio_pda_anylc_err', array('back_link' => true) );
					}


					$state_in = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
					$site_uid = isset( $_GET['site_uid'] ) ? sanitize_text_field( wp_unslash( $_GET['site_uid'] ) ) : '';
					
					if ( $site_uid !== '' ) {
					    $trans_key = 'wpfolio_pda_state_' . $site_uid;
					    $stored_state = get_transient( $trans_key );

					    if ( $stored_state ) {
					
					        if ( $state_in === '' || ! hash_equals( (string) $stored_state, (string) $state_in ) ) {
					            return false;
					        }
					
					        delete_transient( $trans_key );
					    }
					
					}

					$opt_in_data = wpfolio_pda_anylc_update_option( $anylc_pdt_data['anylc_optin'], array('status' => 1) );

					// Redirect to original menu
					$redirect_url = wpfolio_pda_anylc_pdt_url( $anylc_pdt_data, 'offer-promotion' );
					if (get_option('pda_is_licensed') && defined('PDA_GOLD_V3_VERSION') ) {
						$redirect_url = str_replace( 'wp_pda_options', 'pda-gold', $redirect_url );
					}
					

					if( $redirect_url ) {
						wp_safe_redirect( $redirect_url );
						exit;
					}
				}


				// Process Skip
				if ( isset( $_GET['wpfolio_pda_anylc_action'] ) && 'skip' === sanitize_key( wp_unslash( $_GET['wpfolio_pda_anylc_action'] ) ) ) {

					// Verify nonce
					if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpfolio_pda_anylc_act' ) ) {
						wp_die( esc_html__('Sorry, Something happened wrong.', 'prevent-direct-access'), 'wpfolio_pda_anylc_err', array('back_link' => true) );
					}

					$state_in = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
					$site_uid = isset( $_GET['site_uid'] ) ? sanitize_text_field( wp_unslash( $_GET['site_uid'] ) ) : '';
					
					if ( $site_uid !== '' ) {
					    $trans_key = 'wpfolio_pda_state_' . $site_uid;
					    $stored_state = get_transient( $trans_key );

					    if ( $stored_state ) {
					
					        if ( $state_in === '' || ! hash_equals( (string) $stored_state, (string) $state_in ) ) {
					            return false;
					        }
					
					        delete_transient( $trans_key );
					    }
					
					}

					$opt_in_data = wpfolio_pda_anylc_update_option( $anylc_pdt_data['anylc_optin'], array('status' => 2) );

					// Redirect to original menu
					$redirect_url = wpfolio_pda_anylc_pdt_url( $anylc_pdt_data, 'offer' );
					if (get_option('pda_is_licensed') && defined('PDA_GOLD_V3_VERSION') ) {
						$redirect_url = str_replace( 'wp_pda_options', 'pda-gold', $redirect_url );
					}
					if( $redirect_url ) {
						wp_safe_redirect( $redirect_url );
						exit;
					}
				}


				// Process Opt Out
				if ( isset( $_GET['wpfolio_pda_anylc_action'] ) && 'optout' === sanitize_key( wp_unslash( $_GET['wpfolio_pda_anylc_action'] ) ) ) {

					// Verify nonce (use $anylc_pdt for action; already sanitized above).
					if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpfolio_pda_anylc_act' . '|' . $anylc_pdt ) ) {
						wp_die( esc_html__('Sorry, Something happened wrong.', 'prevent-direct-access'), 'wpfolio_pda_anylc_err', array('back_link' => true) );
					}


					$state_in = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
					$site_uid = isset( $_GET['site_uid'] ) ? sanitize_text_field( wp_unslash( $_GET['site_uid'] ) ) : '';
					
					if ( $site_uid !== '' ) {
					    $trans_key = 'wpfolio_pda_state_' . $site_uid;
					    $stored_state = get_transient( $trans_key );

					    if ( $stored_state ) {
					
					        if ( $state_in === '' || ! hash_equals( (string) $stored_state, (string) $state_in ) ) {
					            return false;
					        }
					
					        delete_transient( $trans_key );
					    }
					
					}

					$opt_in_data = wpfolio_pda_anylc_update_option( $anylc_pdt_data['anylc_optin'], array('status' => 0) );

					// Redirect with success message
					$redirect_url = add_query_arg( array( 'message' => 'optout_success', 'wpfolio_pda_anylc_action' => false, 'wpfolio_pda_anylc_pdt' => false, '_wpnonce' => false ) );
					if( $redirect_url ) {
						wp_safe_redirect( $redirect_url );
						exit;
					}
				}
			}
		} // End of main if
	}
}

$wpfolio_pda_anylc_admin = new WPFolio_Pda_Anylc_Admin();