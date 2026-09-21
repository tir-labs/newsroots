<?php

namespace Govpack\BlockEditor;

use Govpack\Abstracts\Plugin;
use Govpack\PluginAware;


use WP_Block_Patterns_Registry;

class Patterns {

	/**
	 * Make The Class Aware of the plugin 
	 */
	use PluginAware;

	/**
	 * Path to the directory to look for patterns in
	 * 
	 * @since 1.2.0
	 */
	private string $pattern_directory;


	/**
	 * Default Pattern Category to assign patterns to when registered
	 * 
	 * @since 1.2.0
	 */
	private string $default_category;

	/**
	 * Name of the transient to store the pattern cache in
	 * 
	 * @since 1.2.0
	 */
	private string $cache_transient_name = 'npe_plugin_files_patterns';

	/**
	 * Expiration time for the plugin patttern cache bucket.
	 *
	 * By default the bucket is not cached, so this value is useless.
	 *
	 * @since 1.2.0
	 * @var bool
	 */
	private static $cache_expiration = 1800;

	/**
	 * Constructor
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin( $plugin );
	}

	/**
	 * Set pattern_directory
	 * 
	 * Allows the pattern directory to be configured from above
	 */
	public function set_pattern_directory( string $path ): self {
		$this->pattern_directory = $path;
		return $this;
	}

	/**
	 * Set default_category
	 * 
	 * Allows the default category to be configured from above
	 */
	public function set_default_category( string $category ): self {
		$this->default_category = $category;
		return $this;
	}

	/**
	 * Register Hooks 
	 * 
	 * Integrate with WordPress Load Order
	 */
	public function hooks() {
		//  add_action( 'admin_init', [ __CLASS__, 'register_block_patterns' ] );
	}

	/**
	 * Get the WP_Block_Patterns_Registry
	 * 
	 * Utility method for chaining the WP_Block_Patterns_Registry
	 */
	private function registry(): WP_Block_Patterns_Registry {
		return WP_Block_Patterns_Registry::get_instance();
	}

	/**
	 * Register Patterns in the directory to the WP_Block_Patterns_Registry
	 * 
	 * Get the pattern data, either from cache or disk, translates any fields 
	 * as needed, Set the Default Category, Mark the pattern source as plugin 
	 * finally register the actual pattern
	 */
	public function register_patterns(): self {
		
		// Either from cache or the files on disk
		$patterns = $this->get_patterns();
		
		foreach ( $patterns as $file => $pattern_data ) {
			if ( $this->registry()->is_registered( $pattern_data['slug'] ) ) {
				continue;
			}

			$path = trailingslashit( $this->pattern_directory ) . $file;

			if ( ! file_exists( $path ) ) {
				_doing_it_wrong( 
					__FUNCTION__,
					sprintf(
						/* translators: %s: file name. */
						__( 'Could not register file "%s" as a block pattern as the file does not exist.', 'newspack-elections' ), //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$file //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					),
					'6.4.0'
				);
				
				continue;
			}

			$pattern_data['filePath'] = $path;

			// Translate the pattern metadata.
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.LowLevelTranslationFunction
			$pattern_data['title'] = translate_with_gettext_context( $pattern_data['title'], 'Pattern title', $this->plugin->text_domain );
			if ( ! empty( $pattern_data['description'] ) ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain,WordPress.WP.I18n.LowLevelTranslationFunction
				$pattern_data['description'] = translate_with_gettext_context( $pattern_data['description'], 'Pattern description', $this->plugin->text_domain );
			}

			
			// if categories are set then force the default category
			if ( $this->use_default_category() && (
				( ! isset( $pattern_data['categories'] ) ) || ( empty( $pattern_data['categories'] ) )
			) ) {
				$pattern_data['categories'] = [ $this->default_category ];
			}

			if ( ! isset( $pattern_data['source'] ) || empty( $pattern_data['source'] ) ) {
				$pattern_data['source'] = 'plugin';
			}
			
		
			register_block_pattern( $pattern_data['slug'], $pattern_data );

		}

		return $this;
	}

	/**
	 * Conditional check to see if there is a valid default category
	 */
	private function use_default_category(): bool {
		return isset( $this->default_category ) && $this->default_category;
	}

	/**
	 * Conditional check to see if we can use the pattern cache.
	 * 
	 * If WordPress is in "plugin" or "any" development mode, then do not use the cache
	 */
	private function can_use_cached(): bool {
		return ! wp_is_development_mode( 'plugin' );
	}

	/**
	 * Get Pattern Data used to register patterns
	 * 
	 * Abstraction over using the cached pattern data or loading from files
	 */
	public function get_patterns(): array|bool {

		$pattern_data = $this->get_pattern_cache();

		if ( is_array( $pattern_data ) ) {
			if ( $this->can_use_cached() ) {
				return $pattern_data;
			}
			// If in development mode, clear pattern cache.
			$this->delete_pattern_cache();
		}

		return $this->load_patterns_files();
	}

	/**
	 * Get Pattern Data from the files in the pattern directory
	 */
	public function load_patterns_files(): array|bool {

		$pattern_data = [];

		if ( ! is_dir( $this->pattern_directory ) ) {
			if ( ! $this->can_use_cached() ) {
				$this->delete_pattern_cache();
			}
			return $pattern_data;
		}

		

		$files = (array) self::scandir( $this->pattern_directory, 'php', -1 );

		if ( ! $files ) {
			if ( $this->can_use_cached() ) {
				$this->set_pattern_cache( $pattern_data );
			}
			return $pattern_data;
		}
		
		$default_headers = [
			'title'         => 'Title',
			'slug'          => 'Slug',
			'description'   => 'Description',
			'viewportWidth' => 'Viewport Width',
			'inserter'      => 'Inserter',
			'categories'    => 'Categories',
			'keywords'      => 'Keywords',
			'blockTypes'    => 'Block Types',
			'postTypes'     => 'Post Types',
			'templateTypes' => 'Template Types',
		];

		$properties_to_parse = [
			'categories',
			'keywords',
			'blockTypes',
			'postTypes',
			'templateTypes',
		];

		foreach ( $files as $file ) {
			$pattern = get_file_data( $file, $default_headers );

			if ( empty( $pattern['slug'] ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: 1: file name. */
						__( 'Could not register file "%s" as a block pattern ("Slug" field missing)', 'newspack-elections' ), //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_attr( $file )
					),
					'6.0.0'
				);
				continue;
			}

			if ( ! preg_match( '/^[A-z0-9\/_-]+$/', $pattern['slug'] ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: 1: file name; 2: slug value found. */
						__( 'Could not register file "%1$s" as a block pattern (invalid slug "%2$s")', 'newspack-elections' ), //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_attr( $file ),
						esc_attr( $pattern['slug'] )
					),
					'6.0.0'
				);
			}

			// Title is a required property.
			if ( ! $pattern['title'] ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: 1: file name. */
						__( 'Could not register file "%s" as a block pattern ("Title" field missing)', 'newspack-elections' ), //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						esc_attr( $file )
					),
					'6.0.0'
				);
				continue;
			}

			// For properties of type array, parse data as comma-separated.
			foreach ( $properties_to_parse as $property ) {
				if ( ! empty( $pattern[ $property ] ) ) {
					$pattern[ $property ] = array_filter( wp_parse_list( (string) $pattern[ $property ] ) );
				} else {
					unset( $pattern[ $property ] );
				}
			}

			// Parse properties of type int.
			$property = 'viewportWidth';
			if ( ! empty( $pattern[ $property ] ) ) {
				$pattern[ $property ] = (int) $pattern[ $property ];
			} else {
				unset( $pattern[ $property ] );
			}

			// Parse properties of type bool.
			$property = 'inserter';
			if ( ! empty( $pattern[ $property ] ) ) {
				$pattern[ $property ] = in_array(
					strtolower( $pattern[ $property ] ),
					[ 'yes', 'true' ],
					true
				);
			} else {
				unset( $pattern[ $property ] );
			}

			$key = str_replace( trailingslashit( $this->pattern_directory ), '', $file );

			$pattern_data[ $key ] = $pattern;
		}

		if ( $this->can_use_cached() ) {
			$this->set_pattern_cache( $pattern_data );
		}

		return $pattern_data;
	}
	
	/**
	 * Scan Directory at $path for files with $extensions.
	 * 
	 * See scandir in class-wp-theme.php for original
	 */
	private static function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {
		if ( ! is_dir( $path ) ) {
			return false;
		}

		if ( $extensions ) {
			$extensions  = (array) $extensions;
			$_extensions = implode( '|', $extensions );
		}

		$relative_path = trailingslashit( $relative_path );
		if ( '/' === $relative_path ) {
			$relative_path = '';
		}

		$results = scandir( $path );
		$files   = [];

		/**
		 * Filters the array of excluded directories and files while scanning theme folder.
		 *
		 * @since 4.7.4
		 *
		 * @param string[] $exclusions Array of excluded directories and files.
		 */
		$exclusions = (array) apply_filters( 'npe_scandir_exclusions', [ 'CVS', 'node_modules', 'vendor', 'bower_components' ] );

		foreach ( $results as $result ) {
			if ( '.' === $result[0] || in_array( $result, $exclusions, true ) ) {
				continue;
			}
			if ( is_dir( $path . '/' . $result ) ) {
				if ( ! $depth ) {
					continue;
				}
				$found = self::scandir( $path . '/' . $result, $extensions, $depth - 1, $relative_path . $result );
				$files = array_merge_recursive( $files, $found );
			} elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {
				$files[ $relative_path . $result ] = $path . '/' . $result;
			}
		}

		return $files;
	}


	/**
	 * Clears block pattern cache.
	 *
	 * @since 1.2.0
	 */
	public function delete_pattern_cache() {
		delete_site_transient( $this->cache_transient_name );
	}
	
	/**
	 * Get block pattern cache.
	 *
	 * @since 1.2.0
	 */
	private function get_pattern_cache() {
	
		$pattern_data = get_site_transient( $this->cache_transient_name );

		if ( is_array( $pattern_data ) && $pattern_data['version'] === $this->plugin->version->version() ) {
			return $pattern_data['patterns'];
		}
		return false;
	}

	/**
	 * Set block pattern cache.
	 *
	 * @since 1.2.0
	 */
	private function set_pattern_cache( array $patterns ) {
		$pattern_data = [
			'version'  => $this->plugin->version->version(),
			'patterns' => $patterns,
		];

		/**
		 * Filters the cache expiration time for theme files.
		 *
		 * @since 6.6.0
		 *
		 * @param int    $cache_expiration Cache expiration time in seconds.
		 * @param string $cache_type       Type of cache being set.
		 */
		$cache_expiration = (int) apply_filters( 'npe_plugin_files_patterns_ttl', self::$cache_expiration );

		// We don't want to cache patterns infinitely.
		if ( $cache_expiration <= 0 ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: %1$s: The filter name.*/
					__( 'The %1$s filter must return an integer value greater than 0.', 'newspack-elections' ), //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<code>npe_plugin_files_patterns_ttl</code>'
				),
				'6.6.0'
			);

			$cache_expiration = self::$cache_expiration;
		}

		set_site_transient( $this->cache_transient_name, $pattern_data, $cache_expiration );
	}
}
