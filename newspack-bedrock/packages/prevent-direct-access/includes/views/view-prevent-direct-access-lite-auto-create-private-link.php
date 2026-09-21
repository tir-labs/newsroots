<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Auto Create Private Link
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_auto_create_new_private_link">
            <input type="checkbox" id="pda_auto_create_new_private_link"
                         name="pda_auto_create_new_private_link" disabled="disabled"/>
            <span class="pda-slider round"></span></label>
        </label>
    </td>
	<td>
		<p>
			<label><?php echo esc_html__( 'Generate Private Link Once Protected', 'prevent-direct-access' ) ?>
			<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>	
			</label>
			<?php echo esc_html__( 'Automatically create a new private link once the file is protected', 'prevent-direct-access' ) ?><span>
                <?php echo esc_html( PDA_Lite_Constants::WARNING_PLAN ); ?>
            </span>
		</p>
	</td>
</tr>
