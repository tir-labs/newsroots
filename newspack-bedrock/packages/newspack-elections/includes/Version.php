<?php

namespace Govpack;

use Govpack\Vendor\z4kn4fein\SemVer\Version as SemVar;

class Version {

	use \Govpack\Instance;

	/**
	 * Reference to Parent Plugin.
	 *
	 * @access private
	 * @var Govpack Plugin Container
	 */
	private Govpack $plugin;

	/**
	 * Reference to Plugin SemVar Object.
	 *
	 * @access private
	 * @var SemVar Semantic Version comparison class
	 */
	private SemVar $semvar;	

	/**
	 * Plugin Version
	 *
	 * @access private
	 * @var string Plugin semantic version as a string major.minor.patch. Pre Reloease and meta data is ignored
	 */
	private string $version;

	/**
	 * Version File Exists
	 * 
	 * @access private
	 * @var bool Cache checked if the version file exists
	 */
	private bool $version_file_exists;

	public function __construct(Govpack $plugin) {
		$this->plugin = $plugin;

		if(!$this->get_db_version()){
			$this->set_db_version("1.1.1");
		}
	}

	/**
	 * Get the version string from the database. Returns null if missing
	 */
	public function get_db_version() : mixed {
		return get_option("govpack_db_version", null);
	}

	public function set_db_version(string $version) : void {
		 update_option("govpack_db_version", $version, true);
	}

	public function has_version_file() : bool {
		
		if(isset($this->version_file_exists)){
			$this->version_file_exists;
		}

		$this->version_file_exists = file_exists( $this->version_file_path() );

		return $this->version_file_exists;
	}

	public function version_file_path() : string  {
		return $this->plugin->path( 'version.php' );
	}

	public function get_semvar(): SemVar {
		if ( isset( $this->semvar ) ) {
			return $this->semvar;
		}

		$version = $this->plugin->require( "version.php" );

		$this->semvar = SemVar::parse( $version );

		return $this->semvar;
	}

	public function version(): string {
		if ( isset( $this->version ) ) {
			return $this->version;
		}

		$this->version = $this->semvar->withoutSuffixes()->__toString();

		return $this->version;
	}


}