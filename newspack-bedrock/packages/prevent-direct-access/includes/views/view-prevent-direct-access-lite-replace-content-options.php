<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Replace content options
*
*/
?>
<tr class="pda-gray-out">
	<td>
		<label class="pda_switch" for="pda_auto_replace_protected_file">
			<input type="checkbox" id="pda_auto_replace_protected_file"
			       name="pda_auto_replace_protected_file" disabled="disabled"/>
			<span class="pda-slider round"></span>
		</label>
	</td>
	<td>
		<p>
			<label><?php echo esc_html__( 'Search & Replace', 'prevent-direct-access' ) ?>
			<span class="pda_upgrade_advice">
					<a rel="noopener" >
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>	
			</label>
			<?php echo esc_html__( 'Search and auto-replace new protected files whose URLs are already embedded in content', 'prevent-direct-access' ) ?>
			<span>
                <?php echo esc_html__( '. Available in Gold version.', 'prevent-direct-access' ) ?>
            </span>

		</p>
	</td>

</tr>
