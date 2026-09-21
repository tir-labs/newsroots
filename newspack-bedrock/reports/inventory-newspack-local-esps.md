# Phase 0 Inventory: newspack-local-esps

=== newspack-local-esps ===
<?php
/**
 * Subscriber sync and merge tag helper.
 * Handles local merge tags, attribute syncing, and segment management
 * for all local ESP providers.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs;

defined( 'ABSPATH' ) || exit;

class Subscriber_Sync {

    /**
     * Hook into Newpack's merge tag system.
     */
    public static function init() {
        add_filter( 'newspack_newsletters_tracking_email_address_tag', [ __CLASS__, 'get_email_merge_tag' ] );
        add_filter( 'newspack_newsletters_tracking_merge_tag', [ __CLASS__, 'get_merge_tag' ], 10, 2 );
        add_action( 'newspack_newsletters_newsletter_rendered', [ __CLASS__, 'inject_local_merge_tags' ], 10, 2 );
    }

    /**
     * Provide the email merge tag for the active local provider.
     *
     * Newpack uses this to embed the recipient's email in the tracking pixel URL.
     * ESP merge tags (e.g. *|EMAIL|*) are replaced at send time by the ESP.
     * For local providers that don't support merge tags, we inject the email
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

<?php
/**
 * Listmonk provider.
 * API: https://listmonk.app/docs/apis/
 *
 * Supports:
 * - Send: POST /api/campaigns (create + start)
 * - Subscribers: POST /api/subscribers with attribs
 * - Lists: GET /api/lists
 * - Templates: Go templates: {{ .Subscriber.Email }}, {{ .Subscriber.Attrs.name }}
 * - Subscriber attribs: arbitrary JSON attributes per subscriber
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Listmonk extends Base_Provider {

    protected $slug = 'listmonk';
    protected $name = 'Listmonk';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) ) && ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
<?php
/**
 * Mail250 provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mail250 extends Base_Provider {

    protected $slug = 'mail250';
    protected $name = 'Mail250';
    protected $type = 'smtp';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'username' ) ) && ! empty( $this->get_setting( 'password' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'username',
                'label'       => 'SMTP Username',
                'type'        => 'text',
                'description' => 'Your Mail250 SMTP username (from the SMTP credentials page).',
            ],
            [
<?php
/**
 * Plunk provider.
 * API: https://docs.useplunk.com/
 *
 * Supports:
 * - Send: POST https://api.useplunk.com/v1/send
 * - Events: POST https://api.useplunk.com/v1/track
 * - Contacts: subscriber data object with custom fields
 * - Segments: Dynamic/static segments via Plunk dashboard
 * - Merge tags: {{email}}, {{firstName}}, etc. natively
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Plunk extends Base_Provider {

    protected $slug = 'plunk';
    protected $name = 'Plunk';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
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
<?php
/**
 * Amazon SES provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class SES extends Base_Provider {

    protected $slug = 'ses';
    protected $name = 'Amazon SES';
    protected $type = 'api';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'access_key' ) )
            && ! empty( $this->get_setting( 'secret_key' ) )
            && ! empty( $this->get_setting( 'region' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'access_key',
                'label'       => 'Access Key ID',
                'type'        => 'text',
                'description' => 'Your AWS IAM Access Key ID.',
<?php
/**
 * BillionMail provider (self-hosted).
 *
 * Supports:
 * - Send: POST /api/v1/send
 * - Subscribers: POST /api/v1/subscribers with metadata, tags, lists
 * - Lists: GET /api/v1/lists
 * - Merge tags: {{field}} syntax
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class BillionMail extends Base_Provider {

    protected $slug = 'billionmail';
    protected $name = 'BillionMail';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) ) && ! empty( $this->get_setting( 'api_key' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'api_url',
<?php
/**
 * Generic SMTP provider.
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class SMTP extends Base_Provider {

    protected $slug = 'smtp';
    protected $name = 'Generic SMTP';
    protected $type = 'smtp';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'host' ) ) && ! empty( $this->get_setting( 'port' ) );
    }

    public function get_settings_fields(): array {
        return [
            [
                'name'        => 'host',
                'label'       => 'SMTP Host',
                'type'        => 'text',
                'description' => 'e.g. smtp.gmail.com, smtp.sendgrid.net',
            ],
            [
<?php
/**
 * Mailtrap provider.
 * API: https://api-docs.mailtrap.io/docs/mailtrap-api-docs/
 *
 * Supports:
 * - Send: POST https://send.api.mailtrap.io/api/send
 * - Batch: POST https://bulk.api.mailtrap.io/api/batches
 * - Contacts: POST https://bulk.api.mailtrap.io/api/contacts
 * - Custom variables: {{variable}} syntax in email body
 * - Lists/segments: via bulk API
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mailtrap extends Base_Provider {

    protected $slug = 'mailtrap';
    protected $name = 'Mailtrap';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
<?php
/**
 * Elastic Email provider.
 * API: https://api.elasticemail.com/v2/
 *
 * Supports:
 * - Send: POST https://api.elasticemail.com/v2/email/send
 * - Contacts: POST https://api.elasticemail.com/v2/contact/add
 * - Custom fields: field[name]=value in contact API
 * - Lists: listNames parameter
 * - Merge tags: [merge_field] syntax natively
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class ElasticEmail extends Base_Provider {

    protected $slug = 'elastic_email';
    protected $name = 'Elastic Email';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
<?php
/**
 * Resend provider.
 * API: https://resend.com/docs/api-reference/emails/send-email
 * Contacts: https://resend.com/docs/api-reference/contacts
 *
 * Supports:
 * - Send: POST https://api.resend.com/emails
 * - Contacts: POST https://api.resend.com/audiences/{audience_id}/contacts
 * - Properties: Custom fields on contacts (firstName, lastName, etc.)
 * - Segments: Dynamic audience filtering via Resend dashboard
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Resend extends Base_Provider {

    protected $slug = 'resend';
    protected $name = 'Resend';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'from_email' ) );
    }

    public function get_settings_fields(): array {
        return [
<?php
/**
 * Mailgun provider.
 * API: https://documentation.mailgun.com/docs/mailgun/api-reference/openapi-final/tag/Messages/
 *
 * Supports:
 * - Send: POST https://api.mailgun.net/v3/{domain}/messages
 * - Recipient variables: per-recipient personalization via "recipient-variables" JSON
 * - Mailing Lists: POST https://api.mailgun.net/v3/lists/{address}/members
 * - Member vars: custom attributes per list member
 * - Merge tags: %recipient.email%, %recipient.name%, %recipient.custom%
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mailgun extends Base_Provider {

    protected $slug = 'mailgun';
    protected $name = 'Mailgun';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_key' ) ) && ! empty( $this->get_setting( 'domain' ) );
    }

    public function get_settings_fields(): array {
        return [
<?php
/**
 * Mautic provider.
 * API: https://developer.mautic.org/
 *
 * Supports:
 * - Send: POST /api/emails/{id}/contact/{contactId}/send (per-contact) or POST /api/emails/send (batch)
 * - Contacts: POST /api/contacts/new, PUT /api/contacts/{id}/edit
 * - Custom fields: Mautic manages fields via admin; any field alias can be set via API
 * - Segments: POST /api/segments/{id}/contact/{contactId}/add
 * - Tags: PUT /api/contacts/{id}/edit with tags array
 * - Tokens: {contactfield=email}, {contactfield=firstname}, {contactfield=alias}
 * - Point actions: trigger point actions via API
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs\Providers;

defined( 'ABSPATH' ) || exit;

class Mautic extends Base_Provider {

    protected $slug = 'mautic';
    protected $name = 'Mautic';

    public function is_configured(): bool {
        return ! empty( $this->get_setting( 'api_url' ) )
            && ! empty( $this->get_setting( 'client_id' ) )
            && ! empty( $this->get_setting( 'client_secret' ) )
<?php
/**
 * Plugin Name: Newspack Local ESPs
 * Description: Adds 11 email service providers to Newspack Newsletters with full tag, attribute, segment, and merge tag support. Providers: Amazon SES, Resend, Plunk, Elastic Email, Mailgun, Mailtrap, Mail250, BillionMail, Mautic, Listmonk, and generic SMTP.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Author: Newsroom
 * License: GPL-2.0-or-later
 *
 * @package Newspack_Local_ESPs
 */

namespace Newspack_Local_ESPs;

defined( 'ABSPATH' ) || exit;

// Load all providers
require_once __DIR__ . '/includes/providers/class-base-provider.php';
require_once __DIR__ . '/includes/providers/class-ses.php';
require_once __DIR__ . '/includes/providers/class-smtp.php';
require_once __DIR__ . '/includes/providers/class-resend.php';
require_once __DIR__ . '/includes/providers/class-plunk.php';
require_once __DIR__ . '/includes/providers/class-elastic-email.php';
require_once __DIR__ . '/includes/providers/class-mailgun.php';
require_once __DIR__ . '/includes/providers/class-mailtrap.php';
require_once __DIR__ . '/includes/providers/class-mail250.php';
require_once __DIR__ . '/includes/providers/class-billionmail.php';
require_once __DIR__ . '/includes/providers/class-mautic.php';
require_once __DIR__ . '/includes/providers/class-listmonk.php';
---FILES---
newspack-local-esps/assets/css/admin.css
newspack-local-esps/includes/class-admin.php
newspack-local-esps/includes/class-subscriber-sync.php
newspack-local-esps/includes/providers/class-base-provider.php
newspack-local-esps/includes/providers/class-billionmail.php
newspack-local-esps/includes/providers/class-elastic-email.php
newspack-local-esps/includes/providers/class-listmonk.php
newspack-local-esps/includes/providers/class-mail250.php
newspack-local-esps/includes/providers/class-mailgun.php
newspack-local-esps/includes/providers/class-mailtrap.php
newspack-local-esps/includes/providers/class-mautic.php
newspack-local-esps/includes/providers/class-plunk.php
newspack-local-esps/includes/providers/class-resend.php
newspack-local-esps/includes/providers/class-ses.php
newspack-local-esps/includes/providers/class-smtp.php
newspack-local-esps/newspack-local-esps.php
---FUNCTIONS---
newspack-local-esps/includes/class-subscriber-sync.php:19:    public static function init() {
newspack-local-esps/includes/class-subscriber-sync.php:33:    public static function get_email_merge_tag( $tag ) {
newspack-local-esps/includes/class-subscriber-sync.php:48:    public static function get_merge_tag( $tag, $field ) {
newspack-local-esps/includes/class-subscriber-sync.php:69:    private static function get_provider_email_tags() {
newspack-local-esps/includes/class-subscriber-sync.php:100:    private static function get_provider_field_tags() {
newspack-local-esps/includes/class-subscriber-sync.php:125:    public static function replace_merge_tags( $html, $contact, $slug ) {
newspack-local-esps/includes/class-subscriber-sync.php:171:    public static function sync_contact( $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:204:    private static function sync_contact_resend( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:235:    private static function sync_contact_plunk( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:286:    private static function sync_contact_elastic_email( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:316:    private static function sync_contact_mailgun( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:352:    private static function sync_contact_billionmail( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:382:    private static function sync_contact_mautic( $provider, $contact ) {
newspack-local-esps/includes/class-subscriber-sync.php:446:    private static function sync_contact_listmonk( $provider, $contact ) {
newspack-local-esps/includes/class-admin.php:14:    public static function init() {
newspack-local-esps/includes/class-admin.php:20:    public static function add_menu() {
newspack-local-esps/includes/class-admin.php:31:    public static function save_settings() {
newspack-local-esps/includes/class-admin.php:49:    public static function ajax_test_send() {
newspack-local-esps/includes/class-admin.php:80:    public static function get_logo( $slug ) {
newspack-local-esps/includes/class-admin.php:98:    public static function render_page() {
newspack-local-esps/includes/providers/class-listmonk.php:25:    public function is_configured(): bool {
newspack-local-esps/includes/providers/class-listmonk.php:29:    public function get_settings_fields(): array {
newspack-local-esps/includes/providers/class-listmonk.php:65:    public function send( array $args ) {
newspack-local-esps/includes/providers/class-listmonk.php:125:    public function add_contact( array $contact, $list_id = '' ) {
newspack-local-esps/includes/providers/class-listmonk.php:183:    public function get_lists() {
newspack-local-esps/includes/providers/class-mail250.php:18:    public function is_configured(): bool {
newspack-local-esps/includes/providers/class-mail250.php:22:    public function get_settings_fields(): array {
newspack-local-esps/includes/providers/class-mail250.php:51:    public function send( array $args ) {
newspack-local-esps/includes/providers/class-mail250.php:77:    public function configure_phpmailer( $phpmailer ) {
newspack-local-esps/includes/providers/class-plunk.php:25:    public function is_configured(): bool {
---HOOKS---

