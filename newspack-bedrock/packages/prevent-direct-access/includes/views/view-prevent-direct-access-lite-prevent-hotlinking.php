<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* Prevent Hotlinking
*
*/
?>
<tr>
    <td>
        <label class="pda_switch" for="enable_image_hot_linking">
            <input type="checkbox" id="enable_image_hot_linking"
                   name="enable_image_hot_linking" <?php echo esc_attr( $enable_image_hot_linking ); ?>  />
            <span class="pda-slider round"></span>
        </label>
    </td>

    <td>
        <p>
            <label><?php echo esc_html__( 'Prevent Image Hotlinking', 'prevent-direct-access' ) ?>
            </label>
            <?php echo esc_html__( 'Prevent other people from stealing and using your images or files without permission.', 'prevent-direct-access' ) ?>
        </p>
    </td>
</tr>
