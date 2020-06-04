<?php

namespace WPDEPM\Core;

use WPDEPM\Core\WordPressAssetWrapper as Assets;
use WPDEPM\Core\WordPressOptionsWrapper as Options;
use WPDEPM\Core\WordPressHooksWrapper as Hooks;

class AssetHandler {

	public $assets = [];

	function __construct() {

		// Replace tags
		add_filter( 'script_loader_tag', [ $this, 'replace_scripts' ], 15, 3 );
		add_filter( 'style_loader_tag', [ $this, 'replace_styles' ], 15, 3 );
	}

	/**
	 * Get asset from cache, and replace html
	 *
	 * @return string
	 */
	public function replace_handler( string $type, string $tag, string $handle, string $src ) {
		$found = Assets::first( $type, $handle );

		if ( $found ) {

			// Get new cache on load if expired
			$found->cache_check();

			$tag = Hooks::apply_filters( 'asset/replace', $tag, $found );

			// Print contents inline
			if ( Options::get( 'inline', false ) ) {
				if ( $contents = $found->contents() ) {
					$tag = '<' . $type . '>' . $contents . '</' . $type . '>';
					$tag = Hooks::apply_filters( 'asset/replace/inline', $tag, $found );
				}
			} else if ( $found->cache_file_exists() ) {
				$tag = str_replace( $src, $found->get_cache_file_url( true ), $tag );
				$tag = Hooks::apply_filters( 'asset/replace/source', $tag, $found );
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
