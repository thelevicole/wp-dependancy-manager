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

	public static function get_assets( ?string $type = null, bool $refresh = true ): array {

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

	public static function find_assets( string $type, string $handle, string $source, bool $refresh = true  ) {
		return array_filter( self::get_assets( $type, $refresh ), function( $asset ) use ( $type, $handle, $source ) {
			$difference = array_diff( [
				'handle' => $handle,
				'source' => $source
			], [
				'handle' => $asset->handle(),
				'source' => $asset->source()
			] );

			return empty( $difference );
		} );
	}

	public static function first_asset( string $type, string $handle, string $source ) {
		$found = self::find_assets( $type, $handle, $source );

		return $found ? array_shift( array_values( $found ) ) : null;
	}


}
