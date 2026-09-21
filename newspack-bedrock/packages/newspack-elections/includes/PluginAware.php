<?php

namespace Govpack;
use \Govpack\Abstracts\Plugin as Plugin;
trait PluginAware {

	
	/**
	 * Stores static instance of class.
	 *
	 * @access protected
	 * @var Govpack The single instance of the class
	 */
	protected Govpack|Plugin $plugin;

	/**
	 * Returns static instance of class.
	 *
	 * @return Govpack
	 */
	public function plugin( null|Govpack|Plugin $plugin = null ): Govpack|Plugin {

		if ( $plugin ) {
			$this->plugin = $plugin;
		}
		
		return $this->plugin;
	}
}
