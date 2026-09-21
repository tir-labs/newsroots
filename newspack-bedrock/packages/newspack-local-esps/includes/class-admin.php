<?php
/**
 * Admin settings page for provider selection.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs;

defined( 'ABSPATH' ) || exit;

class Admin {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'save_settings' ] );
        add_action( 'wp_ajax_newspack_local_esp_test', [ __CLASS__, 'ajax_test_send' ] );
    }

    public static function add_menu() {
        add_submenu_page(
            'newspack-newsletters',
            'Email Providers',
            'Email Providers',
            'manage_options',
            'newspack-local-esps',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function save_settings() {
        if ( ! isset( $_POST['newspack_local_esp_save'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'newspack_local_esp_save' ) ) {
            return;
        }

        $active_provider = sanitize_text_field( $_POST['active_provider'] ?? '' );
        update_option( 'newspack_local_esp_active', $active_provider );

        foreach ( Plugin::get_provider_definitions() as $slug => $definition ) {
            $provider = Plugin::get_provider( $slug );
            if ( $provider ) {
                $provider->save_settings();
            }
        }

        add_settings_error( 'newspack_local_esps', 'saved', 'Settings saved.', 'updated' );
    }

    public static function ajax_test_send() {
        check_ajax_referer( 'newspack_local_esp_test' );

        $provider_slug = sanitize_text_field( $_POST['provider'] ?? '' );
        $test_email    = sanitize_email( $_POST['test_email'] ?? '' );

        if ( empty( $provider_slug ) || empty( $test_email ) ) {
            wp_send_json_error( 'Missing provider or email.' );
        }

        $provider = Plugin::get_provider( $provider_slug );
        if ( ! $provider ) {
            wp_send_json_error( 'Unknown provider.' );
        }

        $result = $provider->send( [
            'to'      => $test_email,
            'subject' => 'Test Email from Newsroom',
            'html'    => '<h1>Test Email</h1><p>This is a test email from your Newsroom newsletter provider.</p><p>Provider: ' . esc_html( $provider->name ) . '</p>',
        ] );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( 'Test email sent successfully!' );
    }

    /**
     * Get SVG logo for a provider.
     */
    public static function get_logo( $slug ) {
        $logos = [
            'ses' => '<svg viewBox="0 0 120 30" fill="none" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="18" fill="#FF9900">Amazon</text><text x="68" y="22" font-family="Arial" font-size="18" fill="#232F3E">SES</text></svg>',
            'smtp' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="30" rx="4" fill="#4A90D9"/><text x="60" y="21" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="white">SMTP</text></svg>',
            'resend' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="20" fill="#000">resend</text></svg>',
            'plunk' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="30" rx="15" fill="#6C5CE7"/><text x="60" y="21" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="white">plunk</text></svg>',
            'elastic_email' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="14" fill="#00B4D8">Elastic</text><text x="56" y="22" font-family="Arial" font-size="14" fill="#023E8A">Email</text></svg>',
            'mailgun' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="18" fill="#E74C3C">Mailgun</text></svg>',
            'mailtrap' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="16" fill="#1D8FCA">Mailtrap</text></svg>',
            'mail250' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="30" rx="4" fill="#1a1a2e"/><text x="60" y="21" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#e94560">Mail250</text></svg>',
            'billionmail' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="14" fill="#27AE60">Billion</text><text x="52" y="22" font-family="Arial" font-size="14" fill="#2C3E50">Mail</text></svg>',
            'mautic' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><text x="0" y="22" font-family="Arial" font-weight="bold" font-size="18" fill="#4E5D6D">mautic</text></svg>',
            'listmonk' => '<svg viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg"><rect width="120" height="30" rx="4" fill="#333"/><text x="60" y="21" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#48C774">listmonk</text></svg>',
        ];

        return $logos[ $slug ] ?? '';
    }

    public static function render_page() {
        $active_provider = get_option( 'newspack_local_esp_active', '' );
        $definitions     = Plugin::get_provider_definitions();
        ?>
        <div class="wrap">
            <h1>Email Service Providers</h1>
            <?php settings_errors( 'newspack_local_esps' ); ?>

            <form method="post">
                <?php wp_nonce_field( 'newspack_local_esp_save' ); ?>

                <h2>Select Active Provider</h2>
                <div class="newspack-esp-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin:20px 0;">
                    <?php foreach ( $definitions as $slug => $def ) : ?>
                        <label class="newspack-esp-card" style="display:block; border:2px solid <?php echo $active_provider === $slug ? '#0073aa' : '#ddd'; ?>; border-radius:8px; padding:16px; cursor:pointer; background:<?php echo $active_provider === $slug ? '#f0f7ff' : '#fff'; ?>; transition:all 0.2s;">
                            <input type="radio" name="active_provider" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $active_provider, $slug ); ?> style="margin-right:8px;" />
                            <div style="margin-bottom:8px;">
                                <?php echo self::get_logo( $slug ); ?>
                            </div>
                            <strong style="display:block; margin-bottom:4px;"><?php echo esc_html( $def['name'] ); ?></strong>
                            <span style="font-size:12px; color:#666;">
                                <?php echo esc_html( $def['description'] ); ?>
                            </span>
                            <span style="display:inline-block; margin-top:8px; font-size:11px; padding:2px 8px; border-radius:3px; background:<?php echo $def['type'] === 'api' ? '#e8f5e9' : '#fff3e0'; ?>; color:<?php echo $def['type'] === 'api' ? '#2e7d32' : '#e65100'; ?>;">
                                <?php echo esc_html( strtoupper( $def['type'] ) ); ?>
                            </span>
                            <?php
                            $provider = Plugin::get_provider( $slug );
                            if ( $provider && $provider->is_configured() ) {
                                echo '<span style="display:inline-block; margin-top:8px; font-size:11px; padding:2px 8px; border-radius:3px; background:#e8f5e9; color:#2e7d32;">✓ Configured</span>';
                            }
                            ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2>Provider Settings</h2>
                <?php foreach ( $definitions as $slug => $def ) : ?>
                    <div class="newspack-esp-settings-panel" id="settings-<?php echo esc_attr( $slug ); ?>" style="display:<?php echo $active_provider === $slug ? 'block' : 'none'; ?>; border:1px solid #ddd; border-radius:8px; padding:20px; margin-bottom:16px;">
                        <h3 style="margin-top:0;">
                            <?php echo self::get_logo( $slug ); ?>
                            <?php echo esc_html( $def['name'] ); ?> Settings
                        </h3>
                        <?php
                        $provider = Plugin::get_provider( $slug );
                        if ( $provider ) {
                            $provider->render_settings();
                        }
                        ?>
                    </div>
                <?php endforeach; ?>

                <?php submit_button( 'Save All Settings', 'primary', 'newspack_local_esp_save' ); ?>
            </form>

            <script>
            jQuery(document).ready(function($) {
                // Show/hide settings panels when provider is selected
                $('input[name="active_provider"]').on('change', function() {
                    var selected = $(this).val();
                    $('.newspack-esp-settings-panel').hide();
                    $('#settings-' + selected).show();
                    $('.newspack-esp-card').css({'border-color': '#ddd', 'background': '#fff'});
                    $(this).closest('.newspack-esp-card').css({'border-color': '#0073aa', 'background': '#f0f7ff'});
                });

                // Test email button
                $('.newspack-local-esp-test').on('click', function() {
                    var provider = $(this).data('provider');
                    var $result = $(this).siblings('.newspack-local-esp-test-result');
                    var testEmail = prompt('Enter test email address:');
                    if (!testEmail) return;

                    $result.text('Sending...').css('color', '#666');

                    $.post(ajaxurl, {
                        action: 'newspack_local_esp_test',
                        _wpnonce: '<?php echo wp_create_nonce( "newspack_local_esp_test" ); ?>',
                        provider: provider,
                        test_email: testEmail
                    }, function(response) {
                        if (response.success) {
                            $result.text(response.data).css('color', '#2e7d32');
                        } else {
                            $result.text(response.data).css('color', '#c62828');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
}

