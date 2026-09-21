<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr class="pda-gray-out">
	<td class="feature-input"><span class="feature-input"></span></td>
	<td>
		<p>
			<label><?php esc_html_e( 'Encrypt Protected Files', 'prevent-direct-access' ); ?>
			<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>	
			</label>
			<?php esc_html_e( 'Prevent unauthorized downloads from viewing protected files by', 'prevent-direct-access' ); ?>
			<a rel="noopener noreferrer" target="_blank" href="https://preventdirectaccess.com/docs/encrypt-wordpress-media-files/?utm_source=user-website&utm_medium=setting-general&utm_campaign=pda-gold">
				<?php esc_html_e( 'advanced encryption', 'prevent-direct-access' ); ?>
			</a>
		</p>
	</td>
</tr>
