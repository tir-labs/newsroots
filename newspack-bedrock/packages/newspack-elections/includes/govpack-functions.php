<?php

if ( ! function_exists( 'gp' ) ) {
	function gp(): \Govpack\Govpack {
		return \Govpack\Govpack::instance();
	}
}

if ( ! function_exists( 'gp_template_loader' ) ) {
	function gp_template_loader(): \Govpack\TemplateLoader {
		return gp()->front_end()->template_loader();
	}
}

if ( ! function_exists( 'gp_get_template_part' ) ) {
	function gp_get_template_part( string $slug, string $name = '', array $data = [] ): string {
		return gp_template_loader()->get_template_part( $slug, $name, true );
	}
}

if ( ! function_exists( 'gp_get_block_part' ) ) {
	function gp_get_block_part( string $slug, string $name = '', array $attributes = [], string $content = '', string|WP_Block|null $block = null, string|null|array $extra = null ): void {
		gp_template_loader()->get_block_part( $slug, $name, $attributes, $content, $block, $extra );
	}
}

if ( ! function_exists( 'gp_get_permalink_structure' ) ) {
	function gp_get_permalink_structure(): array {
		return Govpack\Permalinks::instance()->permalinks();
	}
}

if ( ! function_exists( 'gp_is_url_valid' ) ) {
	function gp_is_url_valid( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}
}


/**
 * Provides Developer feedback if using a hook, method or function is being used incorrectly.
 * 
 * Trigers a userland error if WP_DEBUG is true.
 * 
 * Wraps the core `_doing_it_wrong` function as it provides all we need for now but it may change 
 * or our needs may evolve.
 * 
 * See https://developer.wordpress.org/reference/functions/_doing_it_wrong/ for info.
 */
if ( ! function_exists( 'gp_doing_it_wrong' ) ) {
	function gp_doing_it_wrong( string $function_name, string $message, string $version ): void {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		_doing_it_wrong( $function_name, $message, $version );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}


if ( ! function_exists( 'gp_deprecated' ) ) {
	function gp_deprecated( string $function_name, string $version ): void {
		$message = sprintf( 'The function <code>%s</code> has been deprecated from version <code>%s</code> and will be removed in an upcoming release.', $function_name, $version );
		gp_doing_it_wrong( $function_name, $message, $version );
	}
}



if ( ! function_exists( 'gp_dump' ) ) {
	function gp_dump( ...$args ): void {

		echo '<pre>';

		foreach ( $args as $index => $arg ) {
			var_dump( $arg ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump

			if ( $index !== count( $args ) ) {
				echo '<hr />';
			}
		}

		echo '</pre>';
	}
}
