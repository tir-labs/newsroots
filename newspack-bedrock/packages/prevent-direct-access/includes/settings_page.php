<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 *
 * Setting page options
 *
 */

// Start Setting Page
class PDA_SettingsPage
{
    // Call constructor
    public function __construct() {
        add_option('FREE_PDA_SETTINGS', array(
                    'enable_image_hot_linking' => false,
                    'search_result_page_404' => null,
                    'enable_directory_listing' => null
                    ));
        $this->add_script_ip_lock();
    }

    /**
     *
     * Add Script
     *
     */
    function add_script_ip_lock() {

        // Enqueue Scripts
        wp_enqueue_script( 'pda_ip_lock', plugin_dir_url( __FILE__ ) . '../js/iplock.js', array( 'jquery' ) , PDAF_VERSION, false);
        wp_enqueue_script( 'pda_jquery_tagsinput_min', plugin_dir_url( __FILE__ ) . '../js/jquery.tagsinput.min.js', array(
            'jquery',
            'jquery-ui-core',
            'jquery-ui-tooltip',
        ), PDAF_VERSION, false );

        wp_enqueue_script( 'pda_search_js', plugin_dir_url( __FILE__ ) . '../js/search.js', array( 'jquery' ) , PDAF_VERSION, false );
        wp_localize_script( 'pda_search_js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        wp_enqueue_script( 'pda_setting_page', plugin_dir_url( __FILE__ ) . '../js/pda-settings.js', array( 'jquery' ) , PDAF_VERSION, false );
        wp_localize_script( 'pda_setting_page', 'newsletter_data',
            array(
                'newsletter_url'   => admin_url( 'admin-ajax.php' ),
                'newsletter_nonce' => wp_create_nonce( 'pda_free_subscribe' ),
            )
        );

        // Register Style
        wp_register_style( 'pda_css_jquery_ui', plugin_dir_url( __FILE__ ) . ( '../css/pda_lib.css' ), array(), PDAF_VERSION, 'all' );
        wp_enqueue_style( 'pda_css_tagsinput' );
        wp_enqueue_style( 'pda_css_jquery_ui' );
    }

    /**
     *
     * Render Setting page
     *
     */
    function render_settings_page() {
        ?>
        <div class="wrap">
            <div id="icon-themes" class="icon32"></div>
            <h2>
                <?php esc_html_e( 'Prevent Direct Access - PDA Lite', 'prevent-direct-access' ); ?>
                <span class="pda-version"><?php echo esc_html( PDAF_VERSION ); ?></span>
            </h2>

            <?php
                // phpcs:disable WordPress.Security.NonceVerification.Recommended
                $activate_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
                $this->render_tabs( $activate_tab );
                $this->render_content( $activate_tab );
            ?>
        </div>
        <div id="pda_right_column_metaboxes">
            <?php $this->render_right_column(); ?>
        </div>
        <?php
    }

    /**
     * Render Tabs
     *
     * @param string $active_tab
     *
     */
    function render_tabs($active_tab) {
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=wp_pda_options&tab=general" class="nav-tab <?php echo esc_attr( $active_tab === 'general' ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e('Settings', 'prevent-direct-access'); ?></a>
            <a href="?page=wp_pda_options&tab=iplock" class="nav-tab <?php echo esc_attr( $active_tab === 'iplock' ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e('IP Restriction', 'prevent-direct-access'); ?></a>
            <a href="?page=wp_pda_options&tab=faq" class="nav-tab <?php echo esc_attr( $active_tab === 'faq' ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e('FAQ/Troubleshooting', 'prevent-direct-access'); ?></a>
        </h2>
        <?php
    }

    /**
     * Render Content
     *
     * @param string $active_tab
     *
     */
    function render_content($active_tab) {
        switch ($active_tab) {
            case 'general':
                $this->render_general_tab();
                break;
            case 'iplock':
                $this->render_iplock_tab();
                break;
            default:
                $this->render_faq_tab();
                break;
        }
    }

    /**
     * Render IP Block Tab
     */
    function render_iplock_tab() {

        // Get IP Settings
        $pda_settings_ip = get_option('FREE_PDA_SETTINGS_IP');
        $ip_lock='';
        if(!empty($pda_settings_ip)){
            $ip_lock = $pda_settings_ip['ip_lock'];    
        }
        ?>
            <div class="main_container">
                <h3>Restrict access to private download links</h3>
                <form id="pda_free_ip_form" method="post">
                    <input type="hidden" value="<?php echo esc_attr( wp_create_nonce('pda_ajax_nonce_v3') ); ?>" id="nonce_pda_v3"/>
                    <div style="margin-bottom:10px; margin-top: 10px">
                        <p><?php echo esc_html__('Deny list these IP addresses: stop the following IP addresses from accessing private download links','prevent-direct-access') ?></p>
                    </div>
                    <input id="pda_free_pl_blacklist_ips" name="ip_lock" value="<?php echo esc_attr($ip_lock); ?>" /><br>
                    <p class="description">Use the asterisk (*) for wildcard matching, e.g. 7.7.7.* will match IP from 7.7.7.0 to 7.7.7.255</p><br>
                    <input type="submit" value="<?php esc_attr_e('Save Changes','prevent-direct-access'); ?>" class="button button-primary" name="btn_ip_lock" id="pda_free_submit_btn">
                </form>
            </div>
        <?php
    }

    /**
     * Check if option existed
     *
     * @param array $options
     * @param string $option_key
     *
     * @return Mixed
     */
    function check_option_existed($option, $option_key) {
        return is_array($option)
            && array_key_exists($option_key, $option);
    }

    /**
     * Render General Tabs
     */
    function render_general_tab() {

        // Get options settings
        $pda_settings = get_option('FREE_PDA_SETTINGS');
        $enable_image_hot_linking = $pda_settings['enable_image_hot_linking'] === 'on' ? 'checked="checked"' : '';
        $hide_protected_files_in_media = isset($pda_settings['hide_protected_files_in_media']) ? ($pda_settings['hide_protected_files_in_media'] === 'on' ? 'checked="checked"' : '') : '';
        $disable_right_click = isset($pda_settings['disable_right_click']) ? ($pda_settings['disable_right_click'] === 'on' ? 'checked="checked"' : '') : '';
        $pda_settings_download = get_option('FREE_PDA_SETTINGS_DOWNLOAD');
        $enable_directory_listing = "";
        
        if($pda_settings) {
            if(array_key_exists("enable_directory_listing", $pda_settings)) {
                $enable_directory_listing = $pda_settings['enable_directory_listing'] === 'on' ? 'checked="checked"' : '';
            }
        }
        
        $title_page = "";
        $data_page = "";
        
        $enable_download = isset( $pda_settings_download['enable_download'] ) && $pda_settings_download['enable_download'] === 'on' ? 'checked="checked"' : '';
        $title = $this->get_link_page_404();
        
        if (isset($title) && !empty($title) && $title != null) {
            $data_page = implode(";", $title);
            $title_page = $title['title'];
        }
        ?>
            <div class="main_container">
                <form id="pda_free_options">
                    <div>
                        <input type="hidden" value="<?php echo esc_attr( wp_create_nonce('pda_ajax_nonce_v3') ); ?>" id="nonce_pda_v3"/>
                        <div>
                            <div class="inside">
                                <table class="pda_v3_settings_table" cellpadding="8">
                            <?php include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-nginx.php';
                            ?>
                            <tr>
                                <td colspan="2"><h3><?php echo esc_html__( 'FILE PROTECTION', 'prevent-direct-access' ) ?></h3></td>
                            </tr>
                            <?php
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-auto-protect-new-file-upload.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-encryption-info.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-file-access-permission.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-no-access-page.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-replace-content-options.php';
                            ?>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>
                            <tr id="pda-private-download-link">
                                <td colspan="2"><h3><?php echo esc_html__( 'PRIVATE DOWNLOAD LINKS', 'prevent-direct-access' ) ?></h3></td>
                            </tr>
                            <?php
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-private-url-prefix.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-auto-create-private-link.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-force-download.php';
                            ?>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <h3>
                                        <?php echo esc_html__( 'OTHER SECURITY OPTIONS', 'prevent-direct-access' ) ?>
                                        <span title="<?php echo esc_html__( 'Image hotlinking and directory protection options only work on Apache servers by default', 'prevent-direct-access' ) ?>" class="dashicons dashicons-warning pda-v3-gold-tooltip"></span>
                                    </h3>
                                </td>

                            </tr>
                            <?php
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-prevent-right-click.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-hide-wordpress-version.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-prevent-hotlinking.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-disable-directory-listing.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-ptotect-file.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-block-access-info-file.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-allow-web-crawlers.php';
                            ?>
                            <tr>
                                <td colspan="2">
                                    <hr>
                                </td>
                            </tr>
                            <tr id="pda-advance-opts">
                                <td colspan="2"><h3><?php echo esc_html__( 'ADVANCED OPTIONS', 'prevent-direct-access' ) ?></h3></td>
                            </tr>

    
                            <?php
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-grant-protection-roles.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-enable-remote-log.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-use-redirect-url.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-handle-big-file.php';
                            include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-remove-license-and-all-data.php';
                            ?>
                            
                            <tr>
                                <td></td>
                                <td>
                                    <div class="save_general_btn">
                                        <input type="submit" value="<?php esc_attr_e('Save Changes', 'prevent-direct-access'); ?>" class="button button-primary" id="pda_free_submit_btn" name="pda_free_general">
                                    </div>
                                </td>
                            </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        <?php
    }

    /**
     * Get Title Page 404
     */
    function get_title_page_404(){
        // Get settings
        $pda_settings = get_option('FREE_PDA_SETTINGS');

        // Check in settings
        if (isset($pda_settings['search_result_page_404'])) {
            $page_404 = $pda_settings['search_result_page_404'];
            $title_page_404 = explode(";", $page_404);
            return /*"<b>Selected page: </b>".*/$title_page_404[1];
        }
    }

    /**
     * Get the link of 404
     */
    function get_link_page_404(){
        $pda_settings = get_option(PDA_Lite_Constants::OPTION_NAME);
        if (isset($pda_settings['search_result_page_404'])) {
            $page_404 = $pda_settings['search_result_page_404'];
            $link_page_404 = explode(";", $page_404);
            if (count($link_page_404) === 2) {
                return array("link" => $link_page_404[0], "title" => $link_page_404[1]);
            }
        }
        return null;
    }

    /**
     * Render FAQ Tabs
     */
    function render_faq_tab() {
 
        include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-faq-tab.php';

    }

    /**
     * Render Notification
     */
    function render_notification_toast() {
        ?>
        <div class="notice updated is-dismissible pda-notice pda-install-elementor">
            <div class="pda-notice-inner">
                <div class="pda-notice-icon">
                <img width="64" height="64" src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/icon-128x128.jpg' ); ?>" alt="<?php esc_attr_e( 'PDA Logo', 'prevent-direct-access' ); ?>" />
                </div>
                <div class="pda-notice-content">
                    <h3><?php esc_html_e( 'Do you like Prevent Direct Access? You\'ll love its Gold version!','prevent-direct-access'); ?></h3>
                    <p><?php esc_html_e( 'Please upgrade to ','prevent-direct-access' ); ?>
                        <a target="_blank" href="<?php echo esc_url( sprintf(constant( 'PDA_PRICING_PAGE' ), 'user-website' , "settings-notification-link") ); ?>" target="_blank"><?php esc_html_e( 'Gold version','prevent-direct-access' ); ?></a> <?php echo esc_html__('to change default settings!','prevent-direct-access') ?></p>
                </div>
                <div class="pda-install-now">
                    <a class="button pda-install-button" target="_blank" href="<?php echo esc_url( sprintf(constant( 'PDA_PRICING_PAGE' ), 'settings', 'sidebar-cta') ); ?>"><i class="dashicons dashicons-download"></i><?php esc_html_e( 'Get it now!','prevent-direct-access' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the advance settings
     */
    function render_advanced_settings() {
        ?>
            <div class="main_container">
                <h3><?php esc_html_e('General Options', 'prevent-direct-access')?></h3>
                <form method="post"  action="">
                    <div class="metabox-holder">
                        <div class="postbox">
                            <div class="inside">
                                <table cellpadding="8">
                                    <tbody>
                                    <tr>
                                        <td><?php esc_html_e('Enable remote log?', 'prevent-direct-access'); ?></td>
                                        <td>
                                            <input id='prefix_url' name='prefix_url' type='checkbox' />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e('Apply for logged users?', 'prevent-direct-access'); ?></td>
                                        <td>
                                            <input id='view_by_logged_user' type='checkbox' />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e('Prefix url', 'prevent-direct-access'); ?></td>
                                        <td>
                                            <input id='prefix_url' name='prefix_url' type='text' value="private" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e('Auto protect new uploaded files 123?', 'prevent-direct-access'); ?></td>
                                        <td>
                                            <input id='pda_auto_protect_new_files' name='prefix_url' type='checkbox' />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="<?php esc_attr_e('Save Changes', 'prevent-direct-access'); ?>" class="button button-primary" id="submit_general" name="submit_general">
                </form>
            </div>
        <?php
    }

    /**
     * Render Subscribe Form
     */
    function render_subscribe_form() {
        ?>
        <?php
        include PDA_LITE_BASE_DIR . '/includes/views/view-prevent-direct-access-lite-subscribe-form.php';

    }

    /**
     * Render go pro page
     */
    function render_go_pro_page() {
        include('partials/go-pro.php');
    }

    /**
     * Render right column of subscriber form
     */
    function render_right_column() {
        $this->render_subscribe_form();
    }

    /**
     * Render Like Plugin Column
     */
    function render_like_plugin_column() {
        ?>
            <div class="main_container">
                <h3><?php esc_html_e('Like this Plugin?', 'prevent-direct-access'); ?></h3>
                <div class="inside">

                    <p><?php echo wp_kses_post( __('If you like <b>Prevent Direct Access</b>, please leave us a <span class="pda-star dashicons dashicons-star-filled"></span> rating to motivate the team to work harder, add more powerful features and support you even better :) </br> A huge thanks in advance!', 'prevent-direct-access') ); ?></p>
                    <p><a href="https://wordpress.org/support/plugin/prevent-direct-access/reviews/#new-post" target="_blank" class="button-primary"><?php esc_html_e("Let's do it", 'prevent-direct-access'); ?></a></p>

                    <?php
                    if ( ! function_exists( 'plugins_api' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
                    }
                    ?>

                </div>
            </div>
        <?php
    }
}
