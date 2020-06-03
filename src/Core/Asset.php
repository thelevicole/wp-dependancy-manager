<?php

namespace WPDEPM\Core;

class Asset {

	protected $args = [];

	protected $extras = [];

	protected $handle;

	protected $source;

	protected $type;

	protected $version;

	public function __construct( string $type, string $handle, string $source, ?string $version = null, $args = [], $extras = [] ) {

		$this->type( $type );
		$this->handle( $handle );
		$this->source( $source );
		$this->version( $version );
		$this->args( $args );
		$this->extras( $extras );

	}

	/**
	 * Conditional property setter/getter
	 * 
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return mixed
	 */
	protected function set_get( string $key, $value ) {
		if ( $value ) {
			$this->$key = $value;
		}

		return $this->$key;
	}

	/**
	 * Get or set `type` property
	 * 
	 * @return string
	 */
	public function type( ?string $value = null ) {
		return $this->set_get( 'type', $value );
	}

	/**
	 * Get or set `handle` property
	 * 
	 * @return string
	 */
	public function handle( ?string $value = null ) {
		return $this->set_get( 'handle', $value );
	}

	/**
	 * Get or set `source` property
	 * 
	 * @return string
	 */
	public function source( ?string $value = null ) {
		return $this->set_get( 'source', $value );
	}

	/**
	 * Get or set `version` property
	 * 
	 * @return string
	 */
	public function version( ?string $value = null ) {
		return $this->set_get( 'version', $value );
	}

	/**
	 * Get or set `args` property
	 * 
	 * @return string
	 */
	public function args( $value = null ) {
		return $this->set_get( 'args', $value );
	}

	/**
	 * Get or set `extras` property
	 * 
	 * @return string
	 */
	public function extras( $value = null ) {
		return $this->set_get( 'extras', $value );
	}

	/**
	 * Check if asset source is external
	 * 
	 * @return boolean
	 */
	public function is_external() {

		$source = $this->source();
		$compare = get_site_url();

		$src_parts		= parse_url( $source );
		$src_fragment	= !empty( $src_parts[ 'fragment' ] ) ? $src_parts[ 'fragment' ] : null;
		$src_path		= !empty( $src_parts[ 'path' ] ) ? $src_parts[ 'path' ] : null;
		$src_host		= !empty( $src_parts[ 'host' ] ) ? $src_parts[ 'host' ] : null;

		$compare_parts		= parse_url( $compare );
		$compare_fragment	= !empty( $compare_parts[ 'fragment' ] ) ? $compare_parts[ 'fragment' ] : null;
		$compare_path		= !empty( $compare_parts[ 'path' ] ) ? $compare_parts[ 'path' ] : null;
		$compare_host		= !empty( $compare_parts[ 'host' ] ) ? $compare_parts[ 'host' ] : null;


		// Link is either #link or /link
		if ( $src_fragment || $src_path ) {
			return $src_host ? strpos( $src_host, $compare_host ) === false : false;
		}

		return strpos( $tis->srce, $compare_host ) === false;
	}

	/**
	 * Create a hash string from properties
	 * 
	 * @return string
	 */
	public function hash() {

		$parts = array_filter( [
			$this->type(),
			$this->handle(),
			$this->source()
		] );

		return md5( implode( '-', $parts ) );
	}

	/**
	 * Get the file contents from source URL
	 * 
	 * @return ?string
	 */
	public function source_contents() {
		$contents = @file_get_contents( $this->source() );

		if ( !empty( $contents ) ) {
			return $contents;
		}

		return null;
	}

	public function cache_contents() {
		return null;
	}

	public function contents() {
		return $this->cache_contents() ?: $this->source_contents();
	}


}






