<?php
namespace Govpack\ProfileLinks;

class Linkedin extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'linkedin';

	/**
	 * @return string
	 *
	 * @psalm-return 'linkedin'
	 */
	public function meta_key(): string {
		return 'linkedin';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'LinkedIn'
	 */
	public function label(): string {
		return 'LinkedIn';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'https://www.linkedin.com/in/{linkedin}/'
	 */
	public function url_template(): string {
		return 'https://www.linkedin.com/in/{linkedin}/';
	}
}
