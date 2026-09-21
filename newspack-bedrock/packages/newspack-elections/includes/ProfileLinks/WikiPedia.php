<?php
namespace Govpack\ProfileLinks;

class Wikipedia extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'wikipedia';

	/**
	 * @return string
	 *
	 * @psalm-return 'wikipedia'
	 */
	public function meta_key(): string {
		return 'wikipedia';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'Wikipedia'
	 */
	public function label(): string {
		return 'Wikipedia';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://wikipedia.org/wiki/{wikipedia}/'
	 */
	public function url_template(): string {
		return 'https://wikipedia.org/wiki/{wikipedia}/';
	} 
}
