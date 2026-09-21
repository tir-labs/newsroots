<?php
/**
*
* Access Lite NGINX
*
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

 // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
$server_name    = Pda_Helper::get_server_name();
switch ( $server_name ) {
	case 'apache':
		break;
		
	case 'nginx':
		$rules = Pda_Helper::get_nginx_rules();
		$rows   = count( $rules ) + 1;
		$guides = esc_textarea( implode( "\n", $rules ) );
		?>
		<tr>
			<td colspan="2"><h3><?php echo esc_html__( 'PDA REWRITE RULES', 'prevent-direct-access' ) ?></h3></td>
		</tr>
		<tr>
			<td class="feature-input"><span class="feature-input"></span></td>
			<td>
				<p>
					<?php echo esc_html__( "It looks like you're using NGINX web server. NGINX doesn’t have .htaccess-type capability, Prevent Direct Access Free cannot modify your server configuration automatically for you. Here's how you can do it manually:", 'prevent-direct-access' ); ?>
				</p>
				<p>
					<?php echo esc_html__( "Update our rewrite rules on your NGINX server", 'prevent-direct-access' ); ?>
					<a target="_blank"
					   href="https://preventdirectaccess.com/docs/prevent-direct-access-lite/#nginx"
					   rel="noreferrer noopener"><?php echo esc_html__( 'as per this instruction', 'prevent-direct-access' ); ?></a>:
				</p>
				<textarea readonly rows="<?php echo esc_attr( $rows ); ?>"
						  class="pda-textarea-for-multisite"><?php echo esc_html( $guides ) ?>
				</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr>
			</td>
		</tr>
		<?php
		break;
	case 'iis':
		$str_rule = Pda_Helper::get_iis_rule();
		$guides = esc_textarea( $str_rule );
		?>
		<tr>
			<td colspan="2"><h3><?php echo esc_html__( 'PDA REWRITE RULES', 'prevent-direct-access' ) ?></h3></td>
		</tr>
		<tr>
			<td class="feature-input"><span class="feature-input"></span></td>
			<td>
				<p>
					<?php esc_html_e( "It looks like you're using Internet Information Services (IIS) web server. IIS doesn't read and understand .htaccess rules. Instead, you need to create and update rules in the web.config file", 'prevent-direct-access' ); ?>
					<a target="_blank" rel="noopener noreferrer" href="https://preventdirectaccess.com/docs/prevent-direct-access-lite/#IIS"><?php esc_html_e( 'as per this instruction', 'prevent-direct-access' ); ?></a>:
				</p>
				<textarea readonly rows="8"
						  class="pda-textarea-for-multisite"><?php echo esc_html( $guides ) ?>
				</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr>
			</td>
		</tr>
		<?php
		break;
	default:
		?>
		<tr>
			<td colspan="2"><h3><?php echo esc_html__( 'PDA REWRITE RULES', 'prevent-direct-access' ) ?></h3></td>
		</tr>
		<tr>
			<td class="feature-input"><span class="feature-input"></span></td>
			<td>
				<p>
					<?php esc_html_e( "It looks like you're using other servers rather than Apache, NGINX and IIS. Your server may not have .htaccess-type capability, and Prevent Direct Access cannot modify your server configuration automatically. Please ", 'prevent-direct-access' ); ?>
					<a target="_blank" rel="noopener noreferrer"
					   href="https://preventdirectaccess.com/contact/"><?php esc_html_e( 'contact us', 'prevent-direct-access' ); ?></a>
					<?php esc_html_e( 'for support.', 'prevent-direct-access' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<hr>
			</td>
		</tr>
	<?php
}
