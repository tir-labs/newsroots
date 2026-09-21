<?php
namespace Govpack\ProfileLinks;

class VoteSmart extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'votesmart';

	/**
	 * @return string
	 *
	 * @psalm-return 'votesmart_id'
	 */
	public function meta_key(): string {
		return 'votesmart_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'VoteSmart'
	 */
	public function label(): string {
		return 'Vote Smart';
	}

	public function enabled(): bool {
		return true;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://justfacts.votesmart.org/candidate/biography/{votesmart_id}/'
	 */
	public function url_template(): string {
		return 'https://justfacts.votesmart.org/candidate/biography/{votesmart_id}/';
	} 
}
