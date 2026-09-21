<?php
namespace Govpack\ProfileLinks;

class Fec extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'fec';

	/**
	 * @return string
	 *
	 * @psalm-return 'fec_id'
	 */
	public function meta_key(): string {
		return 'fec_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'Federal Election Comission'
	 */
	public function label(): string {
		return 'Federal Election Comission';
	}


	public function enabled(): bool {
		return true;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://www.fec.gov/data/candidate/{fec_id}/'
	 */
	public function url_template(): string {
		return 'https://www.fec.gov/data/candidate/{fec_id}/';
	}
}
