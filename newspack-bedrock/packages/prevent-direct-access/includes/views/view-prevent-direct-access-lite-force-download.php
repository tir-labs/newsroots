<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Force to download file
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_force_download">
            <input type="checkbox" id="pda_force_download" name="pda_force_download" disabled="disabled"/>
            <span class="pda-slider round"></span>
        </label>
        <div class="pda_error" id="pda_l_error"/>
    </td>
	<td>
		<p>
			<label><?php echo esc_html__( 'Force Downloads', 'prevent-direct-access' ) ?>
			<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>	
			</label>
			<?php echo esc_html__( 'Force downloads instead of redirecting to protected files when clicking Private Links', 'prevent-direct-access' ) ?>
			<span>
                <?php echo esc_html( PDA_Lite_Constants::WARNING_PLAN ); ?>
            </span>
		</p>
	</td>
</tr>
