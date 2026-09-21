<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* File Access Permission
*
*/
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
$file_access = Pda_Helper::get_fap_setting( $pda_settings );
?>
<tr>
    <td class="feature-input"><span class="feature-input"></span></td>
    <td>
	    <p>
		    <label><?php echo esc_html__( 'Set File Access Permission', 'prevent-direct-access' ) ?>
		    </label>
		    <?php echo esc_html__( 'Select user roles who can access protected files through their file URLs.', 'prevent-direct-access' ) ?>
	    </p>
        <select id="file_access_permission">
	        <option value="admin_users" <?php if ( $file_access == "admin_users" ) { echo "selected";	} ?> ><?php echo esc_html__( 'Admin users', 'prevent-direct-access' ) ?></option>
	        <option value="author" <?php if ( $file_access == "author" ) { echo "selected"; } ?> ><?php echo esc_html__( 'The file\'s author', 'prevent-direct-access' ) ?></option>
        </select>
        <p>
		    <?php echo esc_html__( 'Want more options? E.g: Logged-in users, custom roles, and more ', 'prevent-direct-access' ); ?>
		    <a rel="noopener" target="_blank" href="https://preventdirectaccess.com/pricing/">
						<?php echo esc_html__( 'Upgrade to Gold', 'prevent-direct-access' ) ?>
			</a>
	    </p>
    </td>
</tr>
