<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_enable_wc">
            <input type="checkbox" id="pda_enable_wc" disabled="disabled" />
            <span class="pda-slider round"></span>
        </label>
    </td>
    <td>
        <div>
            <label><?php echo esc_html__( 'Grant Web Crawlers Access', 'prevent-direct-access' ); ?>
            <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Select which search engines and social network bots ', 'prevent-direct-access' ); ?>
            <a href="https://preventdirectaccess.com/docs/settings/?utm_source=user-website&utm_medium=setting-general&utm_campaign=pda-gold#crawler" target="_blank" rel="noopener">
                <?php echo esc_html__( 'can access your protected files', 'prevent-direct-access' ); ?>
            </a>
        </div>
    </td>
</tr>