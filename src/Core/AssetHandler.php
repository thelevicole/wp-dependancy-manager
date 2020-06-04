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
	 *
	 */
	public function replace_scripts( string $tag, string $handle, string $src ) {
		$found = Assets::first_asset( 'script', $handle, $src );

		if ( $found ) {
			if ( Options::get( 'inline', true ) ) {
				if ( $contents = $found->contents() ) {
					$tag = '<script>' . $contents . '</script>';
				}
			} else {
				$tag = str_replace( $src, $found->cache_file(), $tag );
			}
		}

		return $tag;
	}

	/**
	 *
	 */
	public function replace_styles( string $tag, string $handle, string $src ) {
		$found = Assets::first_asset( 'style', $handle, $src );

		if ( $found ) {
			if ( Options::get( 'inline', true ) ) {
				$tag = '<style>' . $found->contents() . '</style>';
			} else {
				// Todo add link href tag
			}
		}

		return $tag;
	}

}
