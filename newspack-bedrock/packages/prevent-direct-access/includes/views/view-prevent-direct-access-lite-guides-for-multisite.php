<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Guide for multisite
*
*/
?>
<tr>
    <td colspan="2"><h3><?php echo esc_html__( 'Multisite - Site Rules', 'prevent-direct-access' ) ?></h3></td>
</tr>
<tr>
    <td class="feature-input"><span class="feature-input"></span></td>
    <td>
        <textarea readonly rows="10" class="pda-textarea-for-multisite"><?php echo esc_html( $guides ); ?></textarea>
        <p class="description"> <?php echo esc_html__( 'Please update these rules on your main .htaccess file as per our', 'prevent-direct-access' ) ?>
            <a href="https://preventdirectaccess.com/docs/how-to-protect-your-media-files-on-wordpress-multisite/"><?php echo esc_html__( 'instructions', 'prevent-direct-access' ) ?></a>.
        </p>
    </td>
</tr>
<tr>
    <td colspan="2">
        <hr>
    </td>
</tr>
