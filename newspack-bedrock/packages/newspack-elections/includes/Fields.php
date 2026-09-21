<?php

namespace Govpack;

use Govpack\Fields\FieldTypeRegistry;
use Govpack\Fields\FieldType;
use Govpack\Fields\FieldManager;
use Govpack\Fields\FieldType\Service;
use Govpack\Fields\ProfileServiceRegistry;

class Fields {

	private FieldTypeRegistry $types;
	private ProfileServiceRegistry $services;
	
	public function __construct() {
		$this->types = FieldTypeRegistry::instance();
		$this->register_field_types();

		$this->services = ProfileServiceRegistry::instance();
		$this->register_services();
	}

	public function register_field_types(): void {

		$types = [
			FieldType\Text::class,
			FieldType\Date::class,
			FieldType\Link::class,
			FieldType\Email::class,
			FieldType\Phone::class,
			FieldType\Service::class,
			FieldType\Taxonomy::class,
			FieldType\PostProperty::class,
		];

		foreach ( $types as $type ) {
			$this->types->register( new $type() );
		}
	}

	public function register_services(): void {

		$services = [
			[
				'slug'  => 'facebook',
				'label' => 'Facebook',
				'color' => '#1877f2',
			],
			[
				'slug'  => 'instagram',
				'label' => 'Instagram',
				'color' => '#405de6',
			],
			[
				'slug'  => 'twitter',
				'label' => 'Twitter',
				'color' => '#1da1f2',
			],
			[
				'slug'  => 'x',
				'label' => 'X',
				'color' => '#000000',
			],
			[
				'slug'  => 'youtube',
				'label' => 'YouTube',
				'color' => '#ff0000',
			],
			[
				'slug'  => 'ballotpedia',
				'label' => 'Ballotpedia',
				'color' => '#0645ad',
			],
			[
				'slug'  => 'fec',
				'label' => 'FEC',
				'color' => '#631010',
			],
			[
				'slug'  => 'gab',
				'label' => '"Gab',
				'color' => '#30CE7D',
			],
			[
				'slug'  => 'google',
				'label' => 'Google',
				'color' => '#4285f4',
			],
			[
				'slug'  => 'govtrack',
				'label' => 'GovTrack',
				'color' => '#9D2146',
			],
			[
				'slug'  => 'linkedin',
				'label' => 'Linkedin',
				'color' => '#0a66c2',
			],
			[
				'slug'  => 'open-secrets',
				'label' => 'OpenSecrets',
				'color' => '#669962',
			],
			[
				'slug'  => 'open-states',
				'label' => 'OpenStates',
				'color' => '#005E5C',
			],
			[
				'slug'  => 'rumble',
				'label' => 'Rumble',
				'color' => '#85c742',
			],
			[
				'slug'  => 'vote-smart',
				'label' => 'VoteSmart',
				'color' => '#EE3E37',
			],
			[
				'slug'  => 'vote-view',
				'label' => 'VoteView"',
				'color' => '#3284bf',
			],
			[
				'slug'  => 'wikipedia',
				'label' => 'Wikipedia',
				'color' => '#000000',
			],
		];


		foreach ( $services as $service ) {

			$s = new Fields\Service( $service['slug'], $service['label'] );
			if ( isset( $service['color'] ) ) {
				$s->set_color( $service['color'] );
			}
			$this->services->register( $s );
		}
	}

	public function services(): ProfileServiceRegistry {
		return $this->services;
	}

	public function types(): FieldTypeRegistry {
		return $this->types;
	}

	public function create_field_set(): FieldManager {
		return new FieldManager();
	}
}
