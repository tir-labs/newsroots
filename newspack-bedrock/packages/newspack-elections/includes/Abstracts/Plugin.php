<?php
namespace Govpack\Abstracts;

abstract class Plugin {

	private string $path;
	private string $url;
	private string $uri;

	public function require( string $path ): string {
		return require_once $this->path( $path ); //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	public function build_path( string $path ): string {
		return trailingslashit( $this->path( 'build' ) ) . $path ;
	}

	public function set_path( string $path ): self {
		$this->path = $path;
		return $this;
	}

	public function set_url( string $url ): self {
		$this->url = $url;
		return $this;
	}

	public function set_uri( string $uri ): self {
		$this->uri = $uri;
		return $this;
	}

	public function path( string|null $path = null): string {
		$base_path = \trailingslashit($this->path);

		if(!$path){
			return $base_path;
		}

		return $base_path . $path;
	}

	public function url( string $path ): string {
		return trailingslashit(trailingslashit( $this->url ) . $path);
	}

	public function uri( ): string {
		return $this->uri;
	}

	public function build_url( string $path ): string {
		return $this->url("build") . $path;
	}
}
