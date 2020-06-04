<?php

namespace WPDEPM\Core;

use WPDEPM\Core\WordPressOptionsWrapper as Options;
use WPDEPM\Core\WordPressHooksWrapper as Hooks;

class Asset {

	protected $args = [];

	protected $extras = [];

	protected $handle;

	protected $source;

	protected $type;

	protected $version;

	public static $supports = [
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
	 * @param  string  $value
	 * @return boolean|string
	 */
	public function is_type( string $value ) {
		foreach ( self::$supports as $type => $map ) {
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
			Options::get( 'cache_dir', 'cache/wpdepm' ),
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

		if ( $this->is_type( 'js' ) ) {
			$ext = 'js';
		} elseif ( $this->is_type( 'css' ) ) {
			$ext = 'css';
		}

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
	public function get_cache_file_url( bool $with_version = false ) {
		$url = content_url( $this->get_cache_dir() . $this->get_cache_file_name() );

		if ( $with_version && $this->version() ) {
			$version_key = Options::get( 'version_key', 'ver' );
			$version_key = sanitize_title( (string)$version_key ) ?: 'ver';
			$url .= '?' . $version_key . '=' . $this->version();
		}

		return $url;
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

		$value = $this->$key;
		$value = Hooks::apply_filters( 'asset/property', $value, $key, $this );
		$value = Hooks::apply_filters( 'asset/property/' . $key, $value, $this );

		return $value;
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
		$src = $this->set_get( 'source', $value );

		if ( preg_match( '/^\/\//', $src ) ) {
			$src = 'https:' . $src;
		}

		return $src;
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
	 * Quickly check if the cache file exists on the local disk
	 *
	 * @return boolean
	 */
	public function cache_file_exists() {
		return @file_exists( $this->get_cache_file_path() );
	}

	/**
	 * Get the file contents from cache path
	 *
	 * @return ?string
	 */
	public function get_cache_contents() {
		$contents = @file_get_contents( $this->get_cache_file_path() );
		return $contents !== false ? $contents : null;
	}

	/**
	 * Check if current cache file has expired
	 *
	 * @return boolean|null  True for expired file, or null if file does not exist
	 */
	public function cache_expired() {
		if ( $this->cache_file_exists() ) {
			$expiry_operator	= strtoupper( (string)Options::get( 'expiry_operator', 'hour' ) );
			$expiry_value		= (int)Options::get( 'expiry_value', 12 );
			$expiry_compare		= DAY_IN_SECONDS; // Default to a day lifespan

			if ( defined( $expiry_operator . '_IN_SECONDS' ) ) {
				$expiry_compare = $expiry_value * constant( $expiry_operator . '_IN_SECONDS' );
			}

			$filetime = filemtime( $this->get_cache_file_path() );

			return ( time() - $filetime ) > $expiry_compare;
		}

		return null;
	}

	/**
	 * Get and store the cache contents on the local disk
	 *
	 * @return boolean  True on successfull storage
	 */
	public function download_cache_contents() {
		if ( $contents = $this->get_source_contents() ) {

			/**
			 * Recursively create directory based on full path
			 *
			 * @link https://developer.wordpress.org/reference/functions/wp_mkdir_p/
			 */
			wp_mkdir_p( $this->get_cache_dir_path() );

			$success = @file_put_contents( $this->get_cache_file_path(), $contents );

			return $success !== false;
		}

		return false;
	}

	/**
	 * Check if cache is expired, download new version
	 *
	 * @return void
	 */
	public function cache_check() {
		if ( in_array( $this->cache_expired(), [ null, true ] ) ) {
			$this->download_cache_contents();
		}
	}

	/**
	 * Get the file contents from source URL
	 *
	 * @return ?string
	 */
	public function get_source_contents() {
		$contents = @file_get_contents( $this->source() );
		return $contents !== false ? $contents : null;
	}

	/**
	 * Get the contents from either cache or source
	 *
	 * @return ?string
	 */
	public function contents() {
		return $this->get_cache_contents() ?: $this->get_source_contents();
	}


}







