<?php
namespace Govpack\ProfileLinks;

class OpenStates extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'open-states';

	/**
	 * @return string
	 *
	 * @psalm-return 'openstates_id'
	 */
	public function meta_key(): string {
		return 'openstates_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'OpenStates'
	 */
	public function label(): string {
		return 'Open States';
	}

	/**
	 * @return false
	 */
	public function enabled(): bool {
		return true;
	}

	public function url_template(): string {
		return '{openstates_id}';
	}
}
