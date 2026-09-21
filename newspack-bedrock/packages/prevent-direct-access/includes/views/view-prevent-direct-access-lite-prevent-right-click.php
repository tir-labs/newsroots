<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Prevent Right Click
*
*/
?>
<tr>
    <td>
        <label class="pda_switch" for="disable_right_click">
            <input type="checkbox" id="disable_right_click"
                   name="disable_right_click" <?php echo esc_attr( $disable_right_click ); ?>  />
            <span class="pda-slider round"></span>
        </label>
    </td>

    <td>
        <p>
            <label><?php echo esc_html__( 'Disable Copy and Right Click', 'prevent-direct-access' ) ?>
            </label>
            <?php printf(
                    wp_kses_post(
                        /* translators: 1: opening anchor tag, 2: closing anchor tag around “prevent content theft”. */
                        __(
                            'Disable text selection and right-click to %1$sprevent content theft%2$s on all your web pages.',
                            'prevent-direct-access'
                        )
                    ),
                    '<a target="_blank" rel="noopener noreferrer" href="https://preventdirectaccess.com/docs/prevent-direct-access-lite/?utm_source=user-website&utm_medium=settings-other-security&utm_campaign=pda-lite#right-clicks">',
                    '</a>'
                ); ?>
        </p>
    </td>
</tr>

