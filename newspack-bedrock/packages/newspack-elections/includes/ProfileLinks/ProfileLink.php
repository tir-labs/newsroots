<?php
namespace Govpack\ProfileLinks;

use Govpack\ProfileLinkServices as Profile;
abstract class ProfileLink {

	protected string $label;

	protected string $slug;
	protected mixed $post_meta;
	protected Profile $profile;

	abstract public function meta_key(): string;
	abstract public function label(): string;


	public function url_template(): string {
		return '';
	}

	public function __construct( Profile $profile ) {
		$this->profile = $profile;
	}

	public function test(): bool {
		
		if ( ! $this->profile_has_meta_key() ) {
			return false;
		}

		return true;
	}

	public function get_slug(): string {

		if ( ! $this->slug ) {
			die( 'No Slug In Linkable' );
		}

		return $this->slug;
	}

	public function profile_has_meta_key(): bool {

		/**
		 * @psalm-suppress MixedAssignment
		 */
		$post_meta = $this->meta_value();
		
		if ( ! $post_meta ) {
			return false;
		}

		if ( $post_meta === '' ) {
			return false;
		}

		return true;
	}

	public function meta_value(): mixed {
		if ( isset( $this->post_meta ) ) {
			return $this->post_meta;
		}

		$this->post_meta = $this->profile->get_meta( $this->meta_key() );
		return $this->post_meta;
	}

	public function prep_meta_value( mixed $meta_value ): mixed {
		return $meta_value;
	}


	public function enabled(): bool {
		return true;
	}

	/**
	 * @psalm-return array{slug: mixed, label: mixed, enabled: mixed, meta_key: mixed, template: mixed}
	 */
	public function get_service(): array {
		return [
			'slug'     => $this->get_slug(),
			'label'    => $this->label(),
			'enabled'  => $this->enabled(),
			'meta_key' => $this->meta_key(),
			'template' => $this->url_template(),
		];
	}

	/**
	 * @return (array|mixed|null|string)[]
	 *
	 * @psalm-return array{meta: mixed, target: '_blank', href: mixed, text: mixed, slug: mixed, id: null, rel: null, class: array<never, never>}
	 */
	public function to_array(): array {
		return [
			'meta'   => $this->meta_value(),
			'target' => '_blank',
			'href'   => $this->href(),
			'text'   => $this->label(),
			'slug'   => $this->get_slug(),
			'id'     => null,
			'rel'    => null,
			'class'  => [],
		];
	}

	public function generate_url(): string {
		$template          = $this->url_template();
		$tag               = '{' . $this->meta_key() . '}';
		$with_placeholders = str_replace( $tag, '%s', $template );
		return sprintf( $with_placeholders, (string) $this->prep_meta_value( $this->meta_value() ) );
	}

	public function is_url_valid( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}
	public function href(): bool|string {

		if ( $this->is_url_valid( (string) $this->meta_value() ) ) {
			return (string) $this->meta_value();
		}
		
		$new_url = $this->generate_url();
		
		if ( $this->is_url_valid( $new_url ) ) {
			return $new_url;
		}

		return false;
	}
}
