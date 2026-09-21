<?php

namespace Govpack;

class ProfileLinks extends ProfileLinkServices {

	/**
	 * Post ID of the govpack profile this will generate links for
	 */
	public int $profile_id;

	/**
	 * Array that stores the test results for each link service
	 */
	private array $tests = [];

	/**
	 * Array that stores the generated link objects for each service
	 * 
	 * @var array<string,ProfileLinks\ProfileLink>
	 */
	private array $links = [];

	public function __construct( int $profile_id ) {
		$this->profile_id = $profile_id;
	}


	public function generate(): void {
		foreach ( $this->get_linkable() as $class ) {
			/**
			 * @var ProfileLinks\ProfileLink
			 */
			$linkable = new $class( $this );

			if ( ! $linkable->enabled() ) {
				continue;
			}

			// test this link option, save the result and move on to the next of its a failure
			$this->tests[ $linkable->get_slug() ] = $linkable->test();
			if ( ! $this->tests[ $linkable->get_slug() ] ) {
				continue;
			}

			$this->links[ $linkable->get_slug() ] = $linkable;
		}
	}

	public function get_meta( string $key ): mixed {
		return get_post_meta( $this->profile_id, $key, true );
	}

	/**
	 * @return array
	 */
	public function to_array(): array {
		return array_map(
			function ( $link ) {
				return $link->to_array();
			},
			$this->links
		);
	}
}
