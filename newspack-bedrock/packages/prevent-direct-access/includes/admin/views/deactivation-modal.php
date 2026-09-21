<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="pda-feedback-overlay">
	<div id="pda-feedback-modal">
		<h3>
			<?php esc_html_e( "Before you go, could you tell us why you're deactivating", 'prevent-direct-access' ); ?>
			<span class="pda-plugin-name"></span>?
		</h3>

		<label>
			<input type="radio" name="reason" value="not_needed" checked>
			<?php esc_html_e( 'I no longer need the plugin', 'prevent-direct-access' ); ?>
		</label>

		<label>
			<input type="radio" name="reason" value="better_plugin">
			<?php esc_html_e( 'I found a better plugin', 'prevent-direct-access' ); ?>
		</label>
        <div class="better_plugin_name" style="display:none;">
			<input type="text" name="better_plugin_name" id="better_plugin_name" placeholder="<?php esc_attr_e( 'Would you mind sharing which plugin you chose instead?', 'prevent-direct-access' ); ?>">
        </div>
		<label>
			<input type="radio" name="reason" value="not_working">
			<?php esc_html_e( 'It didn’t work as expected', 'prevent-direct-access' ); ?>
		</label>
        <div class="pda-reason-extra" style="display:none;">
			<textarea name="not_working_reason" id="not_working_reason" placeholder="<?php esc_attr_e( 'Please explain in detail so we can improve our plugin', 'prevent-direct-access' ); ?>"></textarea>
        </div>
		<label>
			<input type="radio" name="reason" value="temporary">
			<?php esc_html_e( 'Temporary deactivation', 'prevent-direct-access' ); ?>
		</label>

		<label>
			<input type="radio" name="reason" value="other">
			<?php esc_html_e( 'Other', 'prevent-direct-access' ); ?>
		</label>

		<textarea name="optional_detail" id="optional_detail" placeholder="<?php esc_attr_e( 'Optional details', 'prevent-direct-access' ); ?>"></textarea>

		<div class="pda-actions">
			<button class="button button-primary pda-submit">
				<span class="pda-btn-text">
					<?php esc_html_e( 'Submit & Deactivate', 'prevent-direct-access' ); ?>
				</span>
				<span class="spinner"></span>
			</button>

			<button class="button pda-skip">
				<?php esc_html_e( 'Skip & Deactivate', 'prevent-direct-access' ); ?>
			</button>
		</div>
	</div>
</div>