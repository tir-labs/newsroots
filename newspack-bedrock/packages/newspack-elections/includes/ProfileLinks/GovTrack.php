<?php
namespace Govpack\ProfileLinks;

class GovTrack extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'govtrack';

	public function enabled(): bool {
		return false;
	}
	
	/**
	 * @return string
	 *
	 * @psalm-return 'govtrack_id'
	 */
	public function meta_key(): string {
		return 'govtrack_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'GovTrack'
	 */
	public function label(): string {
		return 'GovTrack';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://www.govtrack.us/congress/members/{govtrack_id}/'
	 */
	public function url_template(): string {
		return 'https://www.govtrack.us/congress/members/{govtrack_id}/';
	}
}
