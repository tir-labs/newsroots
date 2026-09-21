<?php

namespace Govpack\BlockEditor;

use Govpack\Abstracts\Plugin;
use Govpack\PluginAware;

class BlockTemplates {

	/**
	 * Make The Class Aware of the plugin 
	 */
	use PluginAware;

	/**
	 * Constructor
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin( $plugin );
	}

	/**
	 * Registers a block template
	 * 
	 * Chainable Pass-Through to WordPresses `register_block_theme`. 
	 * Returns this managment class so methods can be chained.
	 * Prepends the plugin's uri to the template name if not
	 * 
	 * @todo Handle failure to register the template.
	 * @todo Cache references to created template objects.
	 * 
	 * @param string       $template_name  Template name in the form of `plugin_uri//template_name`.
 	 * @param array|string $args           {
 	 *     @type string        $title                 Optional. Title of the template as it will be shown in the Site Editor
	 *                                                and other UI elements.
	 *     @type string        $description           Optional. Description of the template as it will be shown in the Site
	 *                                                Editor.
	 *     @type string        $content               Optional. Default content of the template that will be used when the
	 *                                                template is rendered or edited in the editor.
	 *     @type string[]      $post_types            Optional. Array of post types to which the template should be available.
	 *     @type string        $plugin                Optional. Slug of the plugin that registers the template.
	 * }
 	 * @return BlockTemplates The Block Template Loader Class.
 	 */
	public function register( string $template_name, array $args = [] ): self {
		
		$template_name = $this->prefix_template_name_with_plugin_uri($template_name);
		
		if(!isset($args["plugin"])){
			$args["plugin"] = $this->plugin()->uri();
		}
		register_block_template(
			$template_name,
			$args
		);

		return $this;
	}

	public function register_from_file(string $template_name, string $template_file, array $args ) : self {

		$args['content'] = $this->get_template_content($template_file);
		$this->register($template_name, $args);

		return $this;
	}

	private function get_block_template_directory() : string {
		return $this->plugin()->path("block-templates");
	}

	private function get_block_template_path($template_file) : string {
		return sprintf("%s/%s", 
			$this->get_block_template_directory(),
			$template_file
		);
	}

	private function get_template_content( $template_file ) : string {
		ob_start();
		include $this->get_block_template_path($template_file);
		return ob_get_clean();
	}

	private function prefix_template_name_with_plugin_uri(string $template_name) : string {
		$prefix = sprintf("%s//", $this->plugin->uri());

		if(\str_starts_with($template_name, $prefix)){
			return $template_name;
		}

		return $prefix . $template_name;
	}
}
