<?php
namespace Govpack\ProfileLinks;

class VoteView extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'voteview';

	public function enabled(): bool {
		return false;
	}
	
	/**
	 * @return string
	 *
	 * @psalm-return 'icpsr_id'
	 */
	public function meta_key(): string {
		return 'icpsr_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'VoteView'
	 */
	public function label(): string {
		return 'VoteView';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://voteview.com/person/{icpsr_id}/'
	 */
	public function url_template(): string {
		return 'https://voteview.com/person/{icpsr_id}/';
	} 
}
