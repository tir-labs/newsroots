<?php
/**
 * Analytic Optout Popup
 *
 * @package WPFolio Pda Analytic
 * @since 1.0
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// Generate per-request state and store 10 min (bind to site_uid if available)
$state = bin2hex( random_bytes(16) );
$site_uid = isset( $optin_form_data['site_uid'] ) ? sanitize_text_field( $optin_form_data['site_uid'] ) : 'default';
set_transient( 'wpfolio_pda_state_' . $site_uid, $state, 10 * MINUTE_IN_SECONDS );
// pass to template as hidden field (add to $optin_form_data)
$optin_form_data['state'] = $state;
?>
<div class="wpfolio-pda-anylc-popup wpfolio-pda-anylc-popup-wrp wpfolio-pda-anylc-hide" id="wpfolio-pda-anylc-optout-<?php echo esc_attr( $module['id'] ); ?>">
	<div class="wpfolio-pda-anylc-popup-inr-wrp">
		<div class="wpfolio-pda-anylc-popup-block">

			<div class="wpfolio-pda-anylc-popup-header"><?php echo esc_html__( 'Opt Out', 'prevent-direct-access' );?></div>
			<div class="wpfolio-pda-anylc-popup-body">
				<p class="wpfolio-pda-anylc-popup-heading"><?php echo esc_html__( 'We appreciate your help to make the plugin better by letting us track some usage data.', 'prevent-direct-access' );?></p>
				<p><?php echo esc_html__( 'Usage tracking is done in the name of making','prevent-direct-access' );?> <b><?php echo esc_html( $module['name'] ); ?></b> <?php echo esc_html__( 'better', 'prevent-direct-access' );?>. <?php echo esc_html__('Making a better user experience, prioritizing new features, and more good things. We’d really appreciate it if you could reconsider letting us continue with the tracking.', 'prevent-direct-access');?>
			    </p>
				<p><?php echo esc_html__('By clicking "Opt Out", we will stop sending any data from WordPress.','prevent-direct-access');?> <b><?php echo esc_html( $module['name'] ); ?></b> to <a href="<?php echo esc_url( WPFOLIO_PDA_ACTION_URL );?>" target="_blank"><?php echo esc_html( WPFOLIO_PDA_ACTION_URL );?></a>.</p>
			</div>
			<div class="wpfolio-pda-anylc-popup-footer">
				<form method="POST" action="<?php echo esc_url( WPFOLIO_PDA_ACTION_URL );?>">
					<?php
					if( ! empty( $optin_form_data ) ) {
						foreach ($optin_form_data as $data_key => $data_value) {
							echo '<input type="hidden" name="'.esc_attr( $data_key ).'" value="'.esc_attr( $data_value ).'" />';
						}
					}
					?>
					<button type="submit" name="wpfolio_pda_anylc_action" class="button button-secondary" value="optout"><?php echo esc_html__('Opt Out','prevent-direct-access');?></button>
					<button type="button" class="button button-primary wpfolio-pda-anylc-popup-close"><?php echo esc_html__('Sure, Let Me Continue Helping','prevent-direct-access');?></button>
				</form>
			</div>

		</div><!-- end .wpfolio-pda-anylc-popup-block -->
	</div><!-- end .wpfolio-pda-anylc-popup-inr-wrp -->
</div><!-- end .wpfolio-pda-anylc-popup-wrp -->
<div class="wpfolio-pda-anylc-popup-overlay" id="wpfolio-pda-anylc-optout-overlay-<?php echo esc_attr( $module['id'] ); ?>"></div>