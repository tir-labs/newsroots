<?php
namespace Govpack\ProfileLinks;

class OpenSecrets extends \Govpack\ProfileLinks\ProfileLink {

	protected string $slug = 'open-secrets';

	/**
	 * @return string
	 *
	 * @psalm-return 'opensecrets_id'
	 */
	public function meta_key(): string {
		return 'opensecrets_id';
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'OpenSecrets'
	 */
	public function label(): string {
		return 'Open Secrets';
	}

	public function enabled(): bool {
		return true;
	}

	public function url_template(): string {
		return '{opensecrets_id}';
	}
}
