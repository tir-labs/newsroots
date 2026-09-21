<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr class="pda-gray-out">
	<td class="feature-input"><span class="feature-input"></span></td>
	<td>
		<p>
			<label><?php esc_html_e( 'File Protection Control', 'prevent-direct-access' ) ?>
			<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>		
			</label>
			<?php esc_html_e( 'Select user roles who can ', 'prevent-direct-access' ) ?>
			<a href="https://preventdirectaccess.com/docs/settings/?utm_source=user-website&utm_medium=setting-general&utm_campaign=pda-gold#file-protection-control" target="_blank"><?php esc_html_e( 'protect or unprotect your media files', 'prevent-direct-access' ) ?></a><?php esc_html_e( '. Default: admins (always included), authors and editors.', 'prevent-direct-access' ) ?>
		</p>
        
	</td>
</tr>
