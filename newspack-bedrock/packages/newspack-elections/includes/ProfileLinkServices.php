<?php

namespace Govpack;

class ProfileLinkServices {

	

	/**
	 * Array that stores the generated link objects for each service
	 *
	 * @psalm-var array<string,ProfileLinks\ProfileLink>
	 */
	private array $services;

	public function __construct() {
	}

	/**
	 * @return class-string[]
	 *
	 * @psalm-return array<int<0, 11>, class-string>
	 */
	public function get_linkable(): array {

		// a list of known classes
		$linkable = [
			'Ballotpedia',
			'Fec',
			'Gab',
			'Linkedin',
			'OpenSecrets',
			'OpenStates',
			'Rumble',
			'VoteSmart',
			'Wikipedia', 
		];

		

		//generate the FullyQualified Class names
		$classes = array_map(
			function ( $classname ) {
				return __NAMESPACE__ . '\\ProfileLinks\\' . $classname;
			},
			$linkable
		);

		// filter down to only classes the definitly exist
		$classes = array_filter(
			$classes,
			function ( $classname ) {
				return class_exists( $classname );
			}
		);

		
		
		return $classes;
	}

	/**
	 * @psalm-return array<string,ProfileLinks\ProfileLink>
	 */
	public function get_services(): array {
		
		if ( isset( $this->services ) ) {
			return $this->services;
		}   
		
		$this->services = [];
		
		foreach ( $this->get_linkable() as $class ) {
			/**
			 * @var \Govpack\ProfileLinks\ProfileLink
			 */
			$linkable                                = new $class( $this );
			$this->services[ $linkable->get_slug() ] = $linkable;
		}

		
		return $this->services;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		//$vals =  array_values(
		$vals = array_map(
				function ( $link ) {
					$s = $link->get_service();
					return $s;
				},
				$this->get_services()
		//	)
		);

		return $vals;
	}
}
