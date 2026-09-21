<?php
namespace Govpack\ProfileLinks;

class Gab extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'gab';

	/**
	 * @return string
	 *
	 * @psalm-return 'gab'
	 */
	public function meta_key(): string {
		return 'gab';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'Gab'
	 */
	public function label(): string {
		return 'Gab';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://gab.com/{gab}/'
	 */
	public function url_template(): string {
		return 'https://gab.com/{gab}/';
	}
}
