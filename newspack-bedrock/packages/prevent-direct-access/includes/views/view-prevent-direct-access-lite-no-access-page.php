<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* No Access Page
*
*/
?>
<tr>
    <td class="feature-input"><span class="feature-input"></span></td>
    <td>
        <p>
            <label><?php echo esc_html__( 'Customize "No Access" Page', 'prevent-direct-access' ) ?></label>
            <?php if ( isset( $title ) && ! empty( $title ) && $title != null && ! empty( $title['title'] ) ) { ?>
        </p>
        <div class='no-access-selected-page'>
            <b class="no-access-selected-page-label"><?php echo esc_html__( 'Selected page: ', 'prevent-direct-access' ) ?></b>
            <span class="no-access-selected-page-title"><?php echo esc_html( $title['title'] ) ?></span>
            <span class="dashicons dashicons-no remove-no-access-page"></span>
        </div>
        <?php } else { ?>
            <div class='no-access-default-page no-access-selected-page'>
                <b class='selected_page'><?php echo esc_html__( 'Default page:', 'prevent-direct-access') ?></b> <span class='value_page'><?php echo esc_html__( '404 Not Found Page.', 'prevent-direct-access') ?></span>
                <span style="display: none" id="remove_page" class="dashicons dashicons-no remove-no-access-page"></span>
            </div>
        <?php } ?>
        <div>
            <?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false );?>
            <input type="search" id="search" placeholder="Type to search" class="valid" autocomplete="off" aria-invalid="false"/>
        </div>
        <div class="no-access-search-container">
            <ul id="pda_search_result"></ul>
            <div class="title_page_404">
                <input id="title_page_404_input" type="hidden" value="<?php echo esc_attr( $title_page ); ?>">
                <input id="search_page_404_input" type="hidden" name="search_result_page_404" value="<?php echo esc_html( $data_page ); ?>"/>
            </div>
        </div>
    </td>
</tr>
