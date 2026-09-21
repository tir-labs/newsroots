<?php
namespace Govpack\ProfileLinks;

class BallotPedia extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'ballotpedia';

	/**
	 * @return string
	 *
	 * @psalm-return 'ballotpedia_id'
	 */
	public function meta_key(): string {
		return 'ballotpedia_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'Ballotpedia'
	 */
	public function label(): string {
		return 'Ballotpedia';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://ballotpedia.org/{ballotpedia_id}'
	 */
	public function url_template(): string {
		return 'https://ballotpedia.org/{ballotpedia_id}';
	}
}
