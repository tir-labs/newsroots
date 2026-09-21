<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<tr id="pda-force-htaccess">
    <td>
        <label class="pda_switch" for="force_pda_htaccess">
            <input type="checkbox" id="force_pda_htaccess" name="force_pda_htaccess" disabled="disabled" />
            <span class="pda-slider round"></span>
        </label>
        <div class="pda_error" id="pda_l_error"></div>
    </td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Force Local Rewrite Rules', 'prevent-direct-access' ); ?></label>
            <?php echo esc_html__( 'Create .htaccess', 'prevent-direct-access' ); ?>
            <a rel="noopener noreferrer" target="_blank" href="https://preventdirectaccess.com/docs/pda-rewrite-rules/#rewrite-rules/">
                <?php echo esc_html__( 'rewrite rules under _pda folder', 'prevent-direct-access' ); ?>
            </a>. 
            <?php echo esc_html__( 'Enable this option when protected links do not work due to caching issues and/or potential conflicts with other plugins.', 'prevent-direct-access' ); ?>
        </p>
    </td>
</tr>
