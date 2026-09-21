<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Enable Remote Log
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="view_by_logged_user">
            <input type="checkbox" id="view_by_logged_user"
                   name="view_by_logged_user" disabled="disabled"/>
            <span class="pda-slider round"></span>
        </label>
    </td>

    <td>
        <p>
            <label><?php echo esc_html__( 'Enable Debug Logs', 'prevent-direct-access' ) ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Log (fatal) errors of your entire website which speeds up the troubleshooting process when problems occur', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>