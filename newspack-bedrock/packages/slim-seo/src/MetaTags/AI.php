<?php
namespace SlimSEO\MetaTags;

use WP_REST_Server;
use WP_REST_Request;

class AI {
	public const CAP_GENERATE = 'slim_seo_generate_ai';
	public const CAP_BULK     = 'slim_seo_bulk_ai';

	public function setup(): void {
		add_action( 'init', [ $this, 'register_capabilities' ] );
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		add_filter( 'wpai_meta_description_seo_plugins', [ $this, 'register_with_wordpress_ai' ] );
	}

	public function register_capabilities(): void {
		// Primitive mapping happens in map_meta_cap. No extra role grants required.
	}

	/**
	 * @param string[] $caps
	 * @param mixed[]  $args
	 * @return string[]
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		if ( self::CAP_GENERATE === $cap ) {
			return [ 'edit_posts' ];
		}
		if ( self::CAP_BULK === $cap ) {
			return [ 'edit_others_posts' ];
		}
		return $caps;
	}

	public static function is_available(): bool {
		return function_exists( 'wp_get_ability' ) || class_exists( '\WordPress\AiClient\AiClient' );
	}

	public function register_with_wordpress_ai( array $plugins ): array {
		$plugins['slim-seo'] = [
			'file'     => 'slim-seo/slim-seo.php',
			'meta_key' => 'slim_seo',
		];
		return $plugins;
	}

	public function register_routes(): void {
		register_rest_route( 'slim-seo', 'meta-tags/ai', [
			'show_in_index'       => false,
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => [ $this, 'generate' ],
			'permission_callback' => function () {
				return current_user_can( self::CAP_GENERATE );
			},
		] );

		register_rest_route( 'slim-seo', 'ai/models', [
			'show_in_index'       => false,
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_models' ],
			'permission_callback' => function () {
				return current_user_can( self::CAP_GENERATE );
			},
		] );
	}

	public function get_models( WP_REST_Request $request ): array {
		if ( ! self::is_available() ) {
			return [];
		}
		return [ 'wordpress-ai' ];
	}

	public function generate( WP_REST_Request $request ): array {
		$title          = (string) $request->get_param( 'title' );
		$content        = (string) $request->get_param( 'content' );
		$previous_value = (string) $request->get_param( 'previousMetaByAI' );
		$object         = (array) $request->get_param( 'object' );
		$type           = $request->get_param( 'type' ) === 'description' ? 'description' : 'title';
		$object_type    = $object['type'] ?? '';
		$post_id        = 0;
		$term_id        = 0;

		if ( 'post' === $object_type ) {
			$post_id = (int) ( $object['ID'] ?? 0 );

			if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( self::CAP_GENERATE ) ) {
				return $this->response( __( 'You are not allowed to generate meta tags for this post.', 'slim-seo' ) );
			}

			$content = Data::get_post_content( $post_id, $content );
		} elseif ( 'term' === $object_type ) {
			$term_id = (int) ( $object['ID'] ?? 0 );

			if ( $term_id <= 0 || ! current_user_can( 'edit_term', $term_id ) || ! current_user_can( self::CAP_GENERATE ) ) {
				return $this->response( __( 'You are not allowed to generate meta tags for this term.', 'slim-seo' ) );
			}

			$content = Data::get_term_content( $term_id, $content );
		} else {
			return $this->response( __( 'Invalid object type.', 'slim-seo' ) );
		}

		$content = wp_strip_all_tags( $content );
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = trim( $content );

		$max_chars = 8000;
		if ( strlen( $content ) > $max_chars ) {
			$content = substr( $content, 0, $max_chars );
		}

		if ( $type === 'description' && empty( $content ) ) {
			return $this->response( __( 'Content is required to generate meta description.', 'slim-seo' ) );
		}

		if ( $type === 'title' && ( empty( $title ) || empty( $content ) ) ) {
			return $this->response( __( 'Title and content are required to generate meta title.', 'slim-seo' ) );
		}

		$context = [
			'post_id'        => $post_id,
			'term_id'        => $term_id,
			'title'          => $title,
			'previous_value' => $previous_value,
		];

		return $type === 'description'
			? $this->generate_description( $content, $previous_value, $context )
			: $this->generate_title( $content, $previous_value, $title, $context );
	}

	private function generate_title( string $content, string $previous_value, string $title, array $context ): array {
		$prompt = <<<'PROMPT'
You are a professional SEO assistant for WordPress websites.
Write exactly ONE SEO-friendly meta title in the SAME language as the content.
Clear, concise, 50-60 characters. No emojis, quotation marks, or separators such as |, -, or :.
Return ONLY the meta title text.
PROMPT;
		$user = "Title:\n{$title}\n\nContent:\n{$content}";
		if ( $previous_value ) {
			$prompt .= "\nKeep the same meaning, use different wording, do not repeat the previous value.";
			$user   .= "\n\nPrevious meta title:\n{$previous_value}";
		}
		return $this->request( $prompt, $user, 'title', $context );
	}

	private function generate_description( string $content, string $previous_value, array $context ): array {
		$prompt = <<<'PROMPT'
You are a professional SEO assistant for WordPress websites.
Write exactly ONE meta description in the SAME language as the content.
140-160 characters, active voice, no emojis or quotation marks.
Return ONLY the meta description text.
PROMPT;
		$user = "Content:\n{$content}";
		if ( $previous_value ) {
			$prompt .= "\nKeep the same meaning, use different wording, do not repeat the previous value.";
			$user   .= "\n\nPrevious meta description:\n{$previous_value}";
		}
		return $this->request( $prompt, $user, 'description', $context );
	}

	private function request( string $prompt, string $content, string $type, array $context ): array {
		if ( ! current_user_can( self::CAP_GENERATE ) ) {
			return $this->response( __( 'You are not allowed to use AI.', 'slim-seo' ) );
		}

		if ( function_exists( 'wp_get_ability' ) ) {
			$ability_name = 'description' === $type ? 'ai/meta-description' : 'ai/title-generation';
			$ability      = wp_get_ability( $ability_name );
			if ( $ability ) {
				$input = 'description' === $type
					? [
						'content' => $content,
						'title'   => (string) ( $context['title'] ?? '' ),
						'post_id' => (int) ( $context['post_id'] ?? 0 ),
					]
					: [
						'content' => $content,
						'context' => ! empty( $context['post_id'] ) ? (string) $context['post_id'] : $content,
					];
				$result = $ability->execute( $input );
				if ( is_wp_error( $result ) ) {
					return $this->response( $result->get_error_message() );
				}
				$text = 'description' === $type
					? (string) ( $result['description']['text'] ?? '' )
					: (string) ( $result['title'] ?? '' );
				if ( $text !== '' ) {
					return $this->response( trim( $text ), 'success' );
				}
			}
		}

		if ( class_exists( '\WordPress\AiClient\AiClient' ) ) {
			try {
				$text = \WordPress\AiClient\AiClient::prompt( $content )
					->usingSystemInstruction( $prompt )
					->generateText();
				$text = trim( (string) $text );
				if ( $text !== '' ) {
					return $this->response( $text, 'success' );
				}
			} catch ( \Throwable $e ) {
				return $this->response( $e->getMessage() );
			}
		}

		return $this->response( __( 'Install and activate the WordPress AI plugin (wordpress.org/plugins/ai), then connect a provider under Settings → AI.', 'slim-seo' ) );
	}

	private function response( string $message, string $status = 'error' ): array {
		return compact( 'message', 'status' );
	}
}
