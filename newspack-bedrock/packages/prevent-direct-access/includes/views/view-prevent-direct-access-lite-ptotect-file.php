<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Protect the File
*
*/
?>
<tr>
    <td>
        <label class="pda_switch" for="hide_protected_files_in_media">
            <input type="checkbox" id="hide_protected_files_in_media"
                   name="hide_protected_files_in_media" <?php echo esc_attr( $hide_protected_files_in_media ); ?>  />
            <span class="pda-slider round"></span>
        </label>
    </td>

    <td>
        <p>
            <label><?php echo esc_html__( 'Restrict Media Library Access', 'prevent-direct-access' ) ?>
            </label>
            <?php
            printf(
                wp_kses_post(
                    // translators: %1$s and %2$s are HTML link tags wrapping "their own file uploads".
                    __(
                        'Allow users to view %1$s their own file uploads%2$s in Media Library only. Admin users can see all files by default.',
                        'prevent-direct-access'
                    )
                ),
                '<a target="_blank" rel="noopener noreferrer" href="https://preventdirectaccess.com/docs/prevent-direct-access-lite/?utm_source=user-website&utm_medium=settings-other-security&utm_campaign=pda-lite#media-access">',
                '</a>'
            );
            ?>
        </p>
    </td>
</tr>
