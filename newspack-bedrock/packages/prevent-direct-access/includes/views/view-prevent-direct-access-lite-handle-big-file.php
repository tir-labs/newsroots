<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr class="pda-gray-out">
    <td>
        <label class="pda_switch" for="pda_should_handle_big_file">
            <input type="checkbox" id="pda_should_handle_big_file" name="pda_should_handle_big_file" disabled="disabled" />
            <span class="pda-slider round"></span>
        </label>
    </td>
    <td>
        <p>
            <label>
                <?php echo esc_html__( 'Download Large-size Files', 'prevent-direct-access' ); ?>
                <span class="pda_beta">BETA</span>
                <span class="pda_upgrade_advice">
                    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
                        <span class="pda_dashicons dashicons dashicons-lock">
                            <span class="pda_upgrade_tooltip"><?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?></span>
                        </span>
                    </a>
                </span> 
            </label>
            <?php echo esc_html__( 'Enable this option when you allow ', 'prevent-direct-access' ); ?>
            <a href="https://preventdirectaccess.com/docs/settings/?utm_source=user-website&utm_medium=setting-general&utm_campaign=pda-gold#large-files" target="_blank">
                <?php echo esc_html__( 'downloading large-size files', 'prevent-direct-access' ); ?>
            </a>
            <?php echo esc_html__( '. The option will be turned on by default in the upcoming versions.', 'prevent-direct-access' ); ?>
        </p>
    </td>
</tr>
