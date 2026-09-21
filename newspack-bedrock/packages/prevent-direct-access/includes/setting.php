<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 *
 * PDA Menu and settings
 *
 */

/**
 * Create Setting Menu
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function pda_menu() {
    // phpcs:disable
    add_menu_page(
        __( 'Prevent Direct Access Plugin Settings', 'prevent-direct-access' ),
        __( 'Prevent Direct Access', 'prevent-direct-access' ),
        'manage_options',
        // phpcs:ignore
         __FILE__,
        'pda_settings',
        'dashicons-unlock'
    );
    // phpcs:enable
}

 // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
/**
 * Manage PDA Setting
 */
function pda_settings() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound

    // Check current user role and access
    if(!current_user_can('manage_options')) {
        wp_die( esc_html__('You do not have sufficient permissions to access this page.', 'prevent-direct-access') );
    }
    ?>

    <div class="wrap">
        <style>
            .notice.pda-notice {
                border-left-color: #0194F3 !important;
                padding: 20px;
            }
            .rtl .notice.pda-notice {
                border-right-color: #0194F3 !important;
            }
            .notice.pda-notice .pda-notice-inner {
                display: table;
                width: 100%;
            }
            .notice.pda-notice .pda-notice-inner .pda-notice-icon,
            .notice.pda-notice .pda-notice-inner .pda-notice-content,
            .notice.pda-notice .pda-notice-inner .pda-install-now {
                display: table-cell;
                vertical-align: middle;
            }
            .notice.pda-notice .pda-notice-icon {
                color: #0194F3;
                font-size: 50px;
                width: 50px;
            }
            .notice.pda-notice .pda-notice-content {
                padding: 0 20px;
            }
            .notice.pda-notice p {
                padding: 0;
                margin: 0;
            }
            .notice.pda-notice h3 {
                margin: 0 0 5px;
            }
            .notice.pda-notice .pda-install-now {
                text-align: center;
            }
            .notice.pda-notice .pda-install-now .pda-install-button {
                background-color: #0194F3;
                color: #fff;
                border-color: #0da8f3;
                box-shadow: 0 1px 0 #0da8f3;
                padding: 5px 14px;
                height: auto;
                line-height: 20px;
                text-transform: capitalize;
                float: right;
                font-size: 1rem;
            }
            .notice.pda-notice .pda-install-now .pda-install-button i {
                padding-right: 5px;
            }
            .rtl .notice.pda-notice .pda-install-now .pda-install-button i {
                padding-right: 0;
                padding-left: 5px;
            }
            .notice.pda-notice .pda-install-now .pda-install-button:hover {
                background-color: #0da8f3;
            }
            .notice.pda-notice .pda-install-now .pda-install-button:active {
                box-shadow: inset 0 1px 0 #0da8f3;
                transform: translateY(1px);
            }
            @media (max-width: 767px) {
                .notice.pda-notice {
                    padding: 10px;
                }
                .notice.pda-notice .pda-notice-inner {
                    display: block;
                }
                .notice.pda-notice .pda-notice-inner .pda-notice-content {
                    display: block;
                    padding: 0;
                }
                .notice.pda-notice .pda-notice-inner .pda-notice-icon,
                .notice.pda-notice .pda-notice-inner .pda-install-now {
                    display: none;
                }
            }
        </style>
        <div class="notice updated is-dismissible pda-notice pda-install-elementor">
            <div class="pda-notice-inner">
                <div class="pda-notice-icon">
                    <img width="64" height="64" src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/icon-128x128.jpg' ); ?>" alt="<?php esc_attr_e( 'PDA Logo', 'prevent-direct-access' ); ?>" />
                </div>
                <div class="pda-notice-content">
                    <h3><?php esc_html_e( 'Do you like Prevent Direct Access? You\'ll love its Gold version!','prevent-direct-access'); ?></h3>
                    <p><?php esc_html_e( 'Please upgrade to ','prevent-direct-access' ); ?>
                        <a target="_blank" href="<?php echo esc_url( sprintf(constant( 'PDA_PRICING_PAGE' ), 'user-website' , "settings-notification-link") ); ?>" target="_blank"><?php esc_html_e( 'Gold version','prevent-direct-access' ); ?></a> to change default settings!</p>
                </div>
                <div class="pda-install-now">
                    <a class="button pda-install-button" target="_blank" href="<?php echo esc_url( sprintf(constant( 'PDA_PRICING_PAGE' ), 'user-website', 'settings-notification-cta') ); ?>"><i class="dashicons dashicons-download"></i><?php esc_html_e( 'Get it now!','prevent-direct-access' ); ?></a>
                </div>
            </div>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'pda_options' );
            do_settings_sections( 'pda-settings' );
            ?>
            <p class="submit">
                <input disabled type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes','prevent-direct-access') ?>" />
            </p>
        </form>
    </div>
    <?php
}

//Comment due to change to new setting page
//add_action('admin_init', 'register_pda_settings');

/**
 * Register PDA Setting
 */
function register_pda_settings() {
    register_setting('pda_options', 'pda_options', 'pda_options_validate');
    add_settings_section('pda_defaults', 'Default Settings', 'defaults_output', 'pda-settings');
    add_settings_field('pda_enable_remote_log', 'Enable remote log?', 'pda_enable_remote_log', 'pda-settings', 'pda_defaults');
    add_settings_field('pda_view_by_logged_user', 'Apply for logged users?', 'pda_view_by_logged_user', 'pda-settings', 'pda_defaults');
    add_settings_field('pda_prefix_url', 'Prefix url', 'pda_prefix_url', 'pda-settings', 'pda_defaults');
    add_settings_field('pda_auto_protect_new_files', 'Auto protect new uploaded files?', 'pda_enable_remote_log', 'pda-settings', 'pda_defaults');
}

/**
 * PDA Prefic URL
 */
function pda_prefix_url() {
    echo "<input id='prefix_url' name='prefix_url' type='text' value='private' />";
}

/**
 * PDA View by Logged user
 */
function pda_view_by_logged_user() {
    echo "<input id='view_by_logged_user' name='pda_options[view_by_logged_user]' type='checkbox' />";
}

/**
 * PDA Enable remote log
 */
function pda_enable_remote_log() {
    echo "<input id='pda_enable_remote_log' type='checkbox' />";
}

/* Set defaults */
register_activation_hook(plugin_dir_path( __FILE__ ) . 'prevent-direct-access.php', 'pda_add_defaults_fn');

/**
 * PDA Add Default Function
 */
function pda_add_defaults_fn() {
    $tmp = get_option('pda_options');
    if(!is_array($tmp)) {
        $arr = array(
            "view_by_logged_user" => false
        );
        update_option('pda_options', $arr);
    }
}