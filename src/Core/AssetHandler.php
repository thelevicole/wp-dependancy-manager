<?php

namespace WPDEPM\Core;

use WPDEPM\Core\WordPressAssetWrapper as Assets;
use WPDEPM\Core\WordPressOptionsWrapper as Options;

class AssetHandler {

	public $assets = [];

	function __construct() {

		// Attempt to build local array of assets on each of these hooks
		add_action( 'wp_print_styles', [ $this, 'get_assets' ], PHP_INT_MAX );
		add_action( 'wp_print_scripts', [ $this, 'get_assets' ], PHP_INT_MAX );
		add_action( 'wp_print_footer_scripts', [ $this, 'get_assets' ], PHP_INT_MAX );

		// Replace tags
		add_filter( 'script_loader_tag', [ $this, 'replace_scripts' ], 5, 3 );
		add_filter( 'style_loader_tag', [ $this, 'replace_styles' ], 5, 3 );
	}

	/**
	 * Build and return an array of enqued assets
	 *
	 * @return array
	 */
	public function get_assets() {
		if ( empty( $this->assets ) ) {
			$this->assets = Assets::get_assets();
		}

		return $this->assets;
	}

	/**
	 * Return an array of external enqued assets
	 *
	 * @return array
	 */
	public function get_external_assets() {
		return array_filter( $this->get_assets(), function( $asset ) {
			return $asset->is_external();
		} );
	}

	/**
	 * Get asset from cache, and replace html
	 *
	 * @return string
	 */
	public function replace_handler( string $type, string $tag, string $handle, string $src ) {
		$found = Assets::first_asset( $type, $handle, $src );

		if ( $found ) {

			// Get new cache on load if expired
			$found->cache_check();

			// Print contents inline
			if ( Options::get( 'inline', false ) ) {
				if ( $contents = $found->contents() ) {
					$tag = '<' . $type . '>' . $contents . '</' . $type . '>';
				}
			} else {
				$tag = str_replace( $src, $found->get_cache_file_url( true ), $tag );
			}
		}

		return $tag;
	}

	/**
	 *
	 */
	public function replace_scripts( string $tag, string $handle, string $src ) {
		return $this->replace_handler( 'script', $tag, $handle, $src );
	}

	/**
	 *
	 */
	public function replace_styles( string $tag, string $handle, string $src ) {
		return $this->replace_handler( 'style', $tag, $handle, $src );
	}

}
