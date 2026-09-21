<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Use Redirect URL
*
*/
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="use_redirect_urls">
            <input type="checkbox" id="use_redirect_urls" name="use_redirect_urls" disabled="disabled"/>
            <span class="pda-slider round"></span>
        </label>
        <div class="pda_error" id="pda_l_error"></div>
    </td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Keep Raw URLs', 'prevent-direct-access' ) ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Keep Raw URLs for both Private and Original file URLs. Enable this option ONLY when you are using Wordpress.com or NGINX hostings that don\'t allow rewrite rules modification.', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>