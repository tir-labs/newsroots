<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Block Access Information file
*
*/
?>
<tr class="pda-gray-out">
	<td>
		<label class="pda_switch" for="pda_prevent_access_license">
            <input type="checkbox" id="pda_prevent_access_license" disabled="disabled"/>
            <span class="pda-slider round"></span>
		</label>
	</td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Block Access to Sensitive Files', 'prevent-direct-access' ) ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Block access to readme.html, license.txt, and wp-config-sample.php files', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>