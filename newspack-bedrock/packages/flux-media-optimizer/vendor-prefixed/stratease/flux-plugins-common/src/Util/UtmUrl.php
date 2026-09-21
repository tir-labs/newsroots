<?php
/**
 * Helpers for appending standard Flux UTM parameters to outbound marketing URLs.
 *
 * IMPORTANT: This file is part of the externally managed `stratease/flux-plugins-common` library.
 * Do not edit copies inside consuming plugins (including Strauss-prefixed `vendor-prefixed/`).
 *
 * @package FluxMedia\FluxPlugins\Common\Util
 * @since 1.2.1
 */

namespace FluxMedia\FluxPlugins\Common\Util;

/**
 * Builds fluxplugins.com marketing URLs with consistent UTM query args.
 *
 * @since 1.2.1
 */
class UtmUrl {

	/**
	 * Append arbitrary query args (typically UTM) to a URL.
	 *
	 * @since 1.2.1
	 * @param string               $url    Base URL.
	 * @param array<string,string> $params Query parameters to merge.
	 * @return string URL with query args applied.
	 */
	public static function append( $url, array $params ) {
		return add_query_arg( $params, $url );
	}

	/**
	 * Append suite-standard UTM parameters for in-plugin outbound links.
	 *
	 * Convention: utm_source=flux-suite, utm_medium=plugin, plus campaign and content.
	 *
	 * @since 1.2.1
	 * @param string $url      Base marketing URL.
	 * @param string $campaign utm_campaign value.
	 * @param string $content  utm_content value (placement / surface id).
	 * @return string
	 */
	public static function suite( $url, $campaign, $content ) {
		return self::append(
			$url,
			[
				'utm_source'   => 'flux-suite',
				'utm_medium'   => 'plugin',
				'utm_campaign' => $campaign,
				'utm_content'  => $content,
			]
		);
	}
}
