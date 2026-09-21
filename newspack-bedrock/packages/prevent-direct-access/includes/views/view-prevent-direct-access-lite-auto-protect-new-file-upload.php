<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Auto Protect New File Upload
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_auto_protect_new_files">
            <input type="checkbox" id="pda_auto_protect_new_files"
                   name="pda_auto_protect_new_files" disabled="disabled"/>
            <span class="pda-slider round"></span>
        </label>
    </td>

	<td>
		<p>
			<label>
				<?php echo esc_html__( 'Auto-protect New File Uploads', 'prevent-direct-access' ); ?>
				<span class="pda_upgrade_advice">
					<a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<span class="pda_dashicons dashicons dashicons-lock">
							<span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
						</span>
					</a>
				</span>	
			</label>
			<span>
		        <?php echo esc_html__( 'Automatically protect all new file uploads', 'prevent-direct-access' ); ?>
            </span>
		</p>
	</td>
</tr>
