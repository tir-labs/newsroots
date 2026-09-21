<?php
namespace Govpack\ProfileLinks;

class Rumble extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'rumble';

	/**
	 * @return string
	 *
	 * @psalm-return 'rumble'
	 */
	public function meta_key(): string {
		return 'rumble';
	}
	/**
	 * @return string
	 *
	 * @psalm-return 'Rumble'
	 */
	public function label(): string {
		return 'Rumble';
	}
	/**
	 * @return string
	 *
	 * @psalm-return 'https://rumble.com/user/{rumble}/'
	 */
	public function url_template(): string {
		return 'https://rumble.com/user/{rumble}/';
	}
}
