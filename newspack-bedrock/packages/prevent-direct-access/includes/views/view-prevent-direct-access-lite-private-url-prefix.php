<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Private URL Prefix
*
*/
?>
<tr class="pda-gray-out">
	<td class="feature-input"><span class="feature-input"></span></td>
	<td>
		<p>
			<label>
				<?php echo esc_html__( 'Change Private Link Prefix', 'prevent-direct-access' ) ?>
				<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>
			</label>
		</p>
		<div class="pda_error" id="pda_l_error"></div>
		<p class="description">
			<?php echo esc_html__( 'Your Private URL will be: ', 'prevent-direct-access' ) ?><?php echo esc_url( get_site_url() ) . '/' ?><span id="pda_prefix"><?php echo esc_html__( 'private', 'prevent-direct-access' ) ?></span>/<?php esc_html_e( 'your-custom-filename', 'prevent-direct-access' ) ?>
		</p>
		<input type="text" id="pda_prefix_url" name="pda_prefix_url" value="private" disabled="disabled"/>
	</td>
</tr>
