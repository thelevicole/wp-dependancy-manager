<?php

namespace WPDEPM\Core;

use WPDEPM\Core\WordPressOptionsWrapper as Options;

class Asset {

	protected $args = [];

	protected $extras = [];

	protected $handle;

	protected $source;

	protected $type;

	protected $version;

	public static $type_map = [
		'WP_Scripts' => [ 'js', 'scripts', 'script', 'WP_Scripts' ],
		'WP_Styles' => [ 'css', 'styles', 'style', 'WP_Styles' ]
	];

	public function __construct( string $type, string $handle, string $source, ?string $version = null, $args = [], $extras = [] ) {

		$this->type( $type );
		$this->handle( $handle );
		$this->source( $source );
		$this->version( $version );
		$this->args( $args );
		$this->extras( $extras );

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
	 * Check if the asset is a specific type
	 *
	 * @param  string  $type
	 * @return boolean|string
	 */
	public function is_type( string $value ) {
		foreach ( self::$type_map as $type => $map ) {
			if ( in_array( $value, $map ) && $type === $this->type() ) {
				return $type;
			}
		}

		return false;
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
	 * Build a relative cache dir path (rel to WP_CONTENT)
	 *
	 * @return string
	 */
	public function get_cache_dir() {

		$separator = DIRECTORY_SEPARATOR;

		$parts = [
			Options::get( 'cache_dir', 'cache' ),
			str_replace( '_', '-', sanitize_title( $this->type() ) ) // Make URL friendly
		];

		$parts = array_map( function( $part ) {
			return trim( $part, DIRECTORY_SEPARATOR );
		}, $parts );

		$parts = array_filter( $parts );

		return $separator . implode( $separator, $parts ) . $separator;
	}

	/**
	 * Build a absolute cache directory path (rel to host root)
	 *
	 * @return string
	 */
	public function get_cache_dir_path() {
		return rtrim( WP_CONTENT_DIR, DIRECTORY_SEPARATOR ) . $this->get_cache_dir();
	}

	/**
	 * Get the public cache directory url
	 *
	 * @return string
	 */
	public function get_cache_dir_url() {
		return content_url( $this->get_cache_dir() );
	}

	/**
	 * Get the cached file name including extension
	 *
	 * @return string
	 */
	public function get_cache_file_name() {
		$ext = pathinfo( parse_url( $this->source(), PHP_URL_PATH ), PATHINFO_EXTENSION );
		return $this->hash() . '.' . $ext;
	}

	/**
	 * Build a absolute cache file path (rel to host root)
	 *
	 * @return string
	 */
	public function get_cache_file_path() {
		return $this->get_cache_dir_path() . $this->get_cache_file_name();
	}

	/**
	 * Get the public cache file url
	 *
	 * @return string
	 */
	public function get_cache_file_url() {
		return content_url( $this->get_cache_dir() . $this->get_cache_file_name() );
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
	 * Get the file contents from cache path
	 *
	 * @return ?string
	 */
	public function cache_contents() {
		$contents = @file_get_contents( $this->get_cache_file_path() );
		return $contents !== false ? $contents : null;
	}

	/**
	 * Get the file contents from source URL
	 *
	 * @return ?string
	 */
	public function source_contents() {
		$contents = @file_get_contents( $this->source() );
		return $contents !== false ? $contents : null;
	}

	/**
	 * Get the contents from either cache or source
	 *
	 * @return ?string
	 */
	public function contents() {
		return $this->cache_contents() ?: $this->source_contents();
	}


}






