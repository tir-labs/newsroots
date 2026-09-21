<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Hide WordPress Version
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_prevent_access_version">
            <input type="checkbox" id="pda_prevent_access_version" disabled="disabled"/>
            <span class="pda-slider round"></span>
        </label>
    </td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Hide WordPress Version', 'prevent-direct-access' ) ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Remove WordPress generator meta tag showing its version and sensitive information', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>