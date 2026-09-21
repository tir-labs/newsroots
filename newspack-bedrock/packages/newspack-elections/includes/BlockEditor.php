<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack;

use Govpack\BlockEditor\BlockCategories;
use Govpack\BlockEditor\BlockTemplates;
use Govpack\BlockEditor\PatternCategories;
use Govpack\BlockEditor\Patterns;

class BlockEditor {


	private BlockCategories $block_categories;
	private PatternCategories $pattern_categories;
	private BlockTemplates $block_templates;
	private Patterns $patterns;
	private Govpack $plugin;

	public function __construct( Govpack $plugin ) {
		$this->plugin = $plugin;
	}
	
	public function hooks() {
	}

	public function block_categories(): BlockCategories {
		if ( ! isset( $this->block_categories ) ) {
			$this->block_categories = new BlockCategories();
			$this->block_categories->add_hooks();
		}
		return $this->block_categories;
	}

	public function pattern_categories() {
		if ( ! isset( $this->pattern_categories ) ) {
			$this->pattern_categories = new PatternCategories();
		}
		return $this->pattern_categories;
	}

	public function patterns() {
		if ( ! isset( $this->patterns ) ) {
			$this->patterns = new Patterns( $this->plugin );
		}
		return $this->patterns;
	}

	public function block_templates(): BlockTemplates {
		if ( ! isset( $this->block_templates ) ) {
			$this->block_templates = new BlockTemplates($this->plugin );
		}
		return $this->block_templates;
	}
}
