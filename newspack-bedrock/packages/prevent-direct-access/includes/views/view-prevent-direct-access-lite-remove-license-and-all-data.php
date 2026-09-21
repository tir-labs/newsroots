<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Remove License and all data
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="remove_license_and_all_data">
            <input type="checkbox" id="remove_license_and_all_data" name="remove_license_and_all_data" disabled/>
            <span class="pda-slider round"></span>
        </label>
        <div class="pda_error" id="pda_l_error"></div>
    </td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Remove Data Upon Uninstall', 'prevent-direct-access' ) ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Remove your license and ALL related data upon uninstall. Your license may not be used on this website again or elsewhere anymore.', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>
