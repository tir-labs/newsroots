<?php
/**
 * Base provider class for all local ESPs.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

abstract class Base_Provider {

    /**
     * Provider slug.
     *
     * @var string
     */
    protected $slug = '';

    /**
     * Provider display name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Provider type: 'api' or 'smtp'.
     *
     * @var string
     */
    protected $type = 'api';

    /**
     * Singleton instances.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * Get singleton instance.
     */

    public function get_slug(): string {
        return $this->slug;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function get_type(): string {
        return $this->type;
    }

    public function __get(string $key) {
        return $this->$key ?? null;
    }

    public static function instance() {
        $class = static::class;
        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new $class();
        }
        return self::$instances[ $class ];
    }

    /**
     * Get the provider settings from wp_options.
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    protected function get_setting( $key, $default = '' ) {
        return get_option( 'newspack_local_esp_' . $this->slug . '_' . $key, $default );
    }

    /**
     * Save a provider setting.
     *
     * @param string $key Setting key.
     * @param mixed  $value Setting value.
     * @return bool
     */
    protected function save_setting( $key, $value ) {
        return update_option( 'newspack_local_esp_' . $this->slug . '_' . $key, $value, false );
    }

    /**
     * Check if the provider is configured.
     *
     * @return bool
     */
    abstract public function is_configured(): bool;

    /**
     * Send an email.
     *
     * @param array $args {
     *     @type string $to      Recipient email.
     *     @type string $subject Email subject.
     *     @type string $html    HTML body.
     *     @type string $from    Sender email.
     *     @type string $from_name Sender name.
     * }
     * @return true|\WP_Error
     */
    abstract public function send( array $args );

    /**
     * Add a contact to a list.
     *
     * @param array  $contact Contact data.
     * @param string $list_id List ID.
     * @return true|\WP_Error
     */
    public function add_contact( array $contact, $list_id = '' ) {
        // Default: no-op. Providers that support lists override this.
        return true;
    }

    /**
     * Get available lists.
     *
     * @return array|\WP_Error
     */
    public function get_lists() {
        return [];
    }

    /**
     * Get admin settings fields.
     *
     * @return array
     */
    abstract public function get_settings_fields(): array;

    /**
     * Render the provider settings form.
     */
    public function render_settings() {
        $fields = $this->get_settings_fields();
        ?>
        <div class="newspack-local-esp-settings" data-provider="<?php echo esc_attr( $this->slug ); ?>">
            <table class="form-table">
                <?php foreach ( $fields as $field ) : ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr( $this->slug . '_' . $field['name'] ); ?>">
                                <?php echo esc_html( $field['label'] ); ?>
                            </label>
                        </th>
                        <td>
                            <?php $this->render_field( $field ); ?>
                            <?php if ( ! empty( $field['description'] ) ) : ?>
                                <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php if ( $this->type === 'api' ) : ?>
                <p>
                    <button type="button" class="button button-secondary newspack-local-esp-test" data-provider="<?php echo esc_attr( $this->slug ); ?>">
                        <?php esc_html_e( 'Send Test Email', 'newspack-local-esps' ); ?>
                    </button>
                    <span class="newspack-local-esp-test-result"></span>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a single settings field.
     */
    protected function render_field( $field ) {
        $name  = 'newspack_local_esp_' . $this->slug . '_' . $field['name'];
        $value = $this->get_setting( $field['name'] );

        switch ( $field['type'] ) {
            case 'text':
            case 'password':
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />',
                    esc_attr( $field['type'] ),
                    esc_attr( $this->slug . '_' . $field['name'] ),
                    esc_attr( $name ),
                    esc_attr( $value )
                );
                break;
            case 'textarea':
                printf(
                    '<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>',
                    esc_attr( $this->slug . '_' . $field['name'] ),
                    esc_attr( $name ),
                    esc_textarea( $value )
                );
                break;
            case 'select':
                printf( '<select id="%s" name="%s">', esc_attr( $this->slug . '_' . $field['name'] ), esc_attr( $name ) );
                foreach ( $field['options'] as $opt_value => $opt_label ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $opt_value ),
                        selected( $value, $opt_value, false ),
                        esc_html( $opt_label )
                    );
                }
                echo '</select>';
                break;
        }
    }

    /**
     * Handle settings save.
     */
    public function save_settings() {
        $fields = $this->get_settings_fields();
        foreach ( $fields as $field ) {
            $key = $field['name'];
            if ( isset( $_POST[ 'newspack_local_esp_' . $this->slug . '_' . $key ] ) ) {
                $value = sanitize_text_field( wp_unslash( $_POST[ 'newspack_local_esp_' . $this->slug . '_' . $key ] ) );
                $this->save_setting( $key, $value );
            }
        }
    }

    /**
     * Make an HTTP request.
     */
    protected function request( $url, $args = [] ) {
        $defaults = [
            'timeout' => 15,
            'headers' => [],
        ];
        $args = wp_parse_args( $args, $defaults );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code >= 400 ) {
            return new \WP_Error(
                'esp_api_error',
                sprintf( 'API error %d: %s', $code, wp_remote_retrieve_response_message( $response ) )
            );
        }

        $decoded = json_decode( $body, true );
        return $decoded ?? $body;
    }
}

