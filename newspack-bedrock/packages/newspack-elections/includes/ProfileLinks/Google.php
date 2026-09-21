<?php
namespace Govpack\ProfileLinks;

class Google extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'google';

	public function enabled(): bool {
		return false;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'google_entity_id'
	 */
	public function meta_key(): string {
		return 'google_entity_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'Google Trends'
	 */
	public function label(): string {
		return 'Google Trends';
	}

	/**
	 * @return string
	 */
	public function prep_meta_value( mixed $meta_value ): mixed {
		return rawurlencode( (string) $meta_value );
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://trends.google.com/trends/explore?date=all&q={google_entity_id}'
	 */
	public function url_template(): string {
		///m/01rbs3 = %2Fm%2F01rbs3
		return 'https://trends.google.com/trends/explore?date=all&q={google_entity_id}';
	} 
}
