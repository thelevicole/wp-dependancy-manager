<?php

namespace WPDEPM\Core;

use WPDEPM\Core\Asset;

class WordPressAssetWrapper {

	public static $assets = [];

	public static function get_dependants( array &$results, $controller ) {
		if ( !empty( $controller->queue ) ) {
			foreach ( $controller->queue as $handle ) {
				if ( !empty( $controller->registered[ $handle ] ) ) {
					$dependency = $controller->registered[ $handle ];
					$results[] = new Asset(
						get_class( $controller ),
						$dependency->handle,
						$dependency->src,
						$dependency->ver,
						$dependency->args,
						$dependency->extra
					);
				}
			}
		}
	}

	public static function refresh_assets() {
		global $wp_scripts, $wp_styles;

		// Reset assets array
		self::$assets = [];

		self::get_dependants( self::$assets, $wp_scripts );
		self::get_dependants( self::$assets, $wp_styles );
	}

	public static function get( ?string $type = null, bool $refresh = true ): array {

		if ( $refresh ) {
			self::refresh_assets();
		}

		if ( $type ) {
			return array_values( array_filter( self::$assets, function( $asset ) use ( $type ) {
				return !!$asset->is_type( $type );
			} ) );
		}

		return self::$assets;
	}

	public static function find( string $type, ?string $handle = null, ?string $source = null, bool $refresh = true  ) {
		return array_filter( self::get( $type, $refresh ), function( $asset ) use ( $type, $handle, $source ) {
			$difference = array_diff( [
				'handle' => $handle,
				'source' => $source
			], [
				'handle' => $asset->handle(),
				'source' => $asset->source()
			] );

			if ( $handle && $source ) {
				return $asset->handle() === $handle && $asset->source() === $source;
			} else if ( $handle && !$source ) {
				return $asset->handle() === $handle;
			} else if ( !$handle && $source ) {
				return $asset->source() === $source;
			}


			return false;
		} );
	}

	public static function first( string $type, ?string $handle = null, ?string $source = null ) {
		$found = array_values( self::find( $type, $handle, $source ) );

		return $found ? array_shift( $found ) : null;
	}


}
