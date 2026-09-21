<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
*
* FAQ Tab
*
*/
?>
<div class="main_container">
	<div class="pda_faq">
		<h3> <?php echo esc_html__('Q: I get this error "Plugin could not be activated because it triggered a fatal error" when activating
			the plugin, what should I do?','prevent-direct-access');?> </h3>
		<p> <?php echo esc_html__("Please check with your web hosting provider about its PHP version and make sure it supports PHP version
			5.4 or greater. Our plugin's codes are not compatible with outdated PHP versions. As a matter of fact,
			WordPress also recommend your host supports:","prevent-direct-access");?> </p>
		<ul style="list-style-type: disc; margin-left: 17px; ">
			<li><?php echo esc_html__("PHP version 7 or greater","prevent-direct-access");?></li>
			<li> <?php echo esc_html__("MySQL version 5.6 or greater OR MariaDB version 10.0 or greater","prevent-direct-access");?></li>
			<li><?php echo esc_html__("HTTPS support","prevent-direct-access")?></li>
			<li><?php echo esc_html__("Older PHP or MySQL versions may expose your site to security vulnerabilities.","prevent-direct-access"); ?></li>

		</ul>

		<a target="_blank" href="<?php echo esc_url("https://preventdirectaccess.com/faq/#faq1");?>"><?php echo esc_html__("Official FAQ","prevent-direct-access");?></a></p>


		<h3><?php echo esc_html__("Q: Why can I still access my files through non-protected URLs?","prevent-direct-access");?></h3>

		<p><?php echo esc_html__("Please clear your browser's cache (press CTRL+F5 on PC, CMD+SHIFT+R on Mac) as files and especially
			images are usually cached by your browsers.","prevent-direct-access");?>
			</p>

		<p><?php echo esc_html__("Also, if you're using a caching plugin such as W3 Total Cache or WP Super Cache to speed up your
			WordPress website, please make sure you clear your cache as well. Your browsers and caching plugin could
			still be showing a cached (older) version of your files.","prevent-direct-access"); ?> </p>

		
		<a target="_blank" href="<?php echo esc_url("https://preventdirectaccess.com/faq/#faq2");?>"><?php echo esc_html__("Official FAQ","prevent-direct-access");?></a></p>	

		<h3><?php echo esc_html__("Q: Why am I getting “page not found” 404 error when accessing private links?","prevent-direct-access");?> </h3>

		<p> <?php echo esc_html__(" It seems our custom rewrite rules are not inserted into your .htaccess file properly. There are a few reasons for this:","prevent-direct-access");?></p>

		<ul style="list-style-type: disc; margin-left: 17px; ">
			<li><?php echo esc_html__("You edit and mess up your .htaccess rules","prevent-direct-access");?> 
			</li>
			<li><?php echo esc_html__("Your WordPress folders are structured differently from usual","prevent-direct-access");?> </li>
		</ul>
		<p><?php echo esc_html__("For example, your domain’s root folder is located at, let's say,","prevent-direct-access")?> <code><?php echo esc_html__("home/","prevent-direct-access");?></code> <?php echo esc_html__("directory but your WordPress files are put under ","prevent-direct-access");?><code><?php echo esc_html__("home/wp/","prevent-direct-access");?></code> <?php echo esc_html__("directory. In such cases, our plugin can't insert our .htaccess codes properly, and so, you have to manually update your .htaccess located at ","prevent-direct-access");?> <code><?php echo esc_html__("home/wp/","prevent-direct-access");?></code> <?php echo esc_html__("directory with our plugin's custom rewrite rules.","prevent-direct-access");?></p>
		
		<a target="_blank" href="<?php echo esc_url("https://preventdirectaccess.com/faq/#faq3");?>"><?php echo esc_html__("Official FAQ","prevent-direct-access");?></a></p>	


		<p><?php echo esc_html__("For more information, please visit our","prevent-direct-access");?> 




        <a target="_blank" href="<?php echo esc_url("https://preventdirectaccess.com/faq/?utm_source=gold&utm_content=settings-link&utm_campaign=pda_gold");?>"><?php echo esc_html__("Official FAQ.","prevent-direct-access");?></a></p>
	</div>
</div>