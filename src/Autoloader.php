<?php

namespace WPDEPM;

class Autoloader {
	
	public $dir;

	public $currentDir;

	public $seperator = '/';

	public $supports = [ 'psr-4', 'psr-0', 'files' ];

	function __construct( string $dir ) {
		$this->dir = $dir;
		$this->currentDir = $dir;
		$this->seperator = DIRECTORY_SEPARATOR;
	}

	public function parseDir( ?string $dir ) {
		if ( !empty( $this->dir ) ) {
			$dir = preg_replace( '/^' . preg_quote( $this->dir, '/' ) . '/', '', $dir );
			return rtrim( $this->dir, $this->seperator ) . $this->seperator . ltrim( $dir, $this->seperator );
		}

		return $dir;
	}

	public function loadArray( array $parts, string $type, ?string $dir = null ) {
		$this->currentDir = $this->parseDir( $dir );
		return $this->include( $parts, $type );
	}

	/**
	 * Load from composer file
	 */
	public function loadComposer( ?string $dir = null ) {
		$composer = json_decode( file_get_contents( $this->parseDir( $dir ) . '/composer.json' ), true );
		foreach ( $this->supports as $type ) {
			if ( isset( $composer[ 'autoload' ][ $type ] ) ) {
				$this->include( (array)$composer[ 'autoload' ][ $type ], $type );
			}
		}
	}

	/**
	 * Handle loading by array and type
	 * 
	 * @param  array  $items
	 * @param  string $type
	 * @return boolean
	 */
	private function include( array $items, string $type ) {

		$status = false;

		if ( in_array( $type, $this->supports ) ) {
			switch ( $type ) {
				case 'psr-4':
					$this->includePSR( $items, true );
					$status = true;
					break;

				case 'psr-0':
					$this->includePSR( $items, false );
					$status = true;
					break;

				case 'psr-0':
					$this->includeFiles( $items );
					$status = true;
					break;
			}
		}

		return $status;
	}

	public function includeFiles( array $files ) {
		foreach( $files as $file ) {
			$fullpath = rtrim( $this->dir, $this->seperator ) . $this->seperator . ltrim( $file, $this->seperator );
			
			if ( file_exists( $fullpath ) ) {
				include_once $fullpath;
			}
		}
	}

	public function includePSR( $namespaces, bool $psr4 ) {
		$dir = $this->currentDir;
		
		// Foreach namespace specified in the composer, load the given classes
		foreach( $namespaces as $namespace => $classpaths ) {
			$classpaths = (array)$classpaths;

			spl_autoload_register( function( $classname ) use ( $namespace, $classpaths, $dir, $psr4 ) {
				// Check if the namespace matches the class we are looking for
				if ( preg_match( '/^' . preg_quote( $namespace ) . '/', $classname ) ) {
					
					// Remove the namespace from the file path since it's psr4
					if ( $psr4 ) {
						$classname = str_replace( $namespace, '', $classname );
					}

					$filename = preg_replace( '/\\\\/', '/', $classname ) . '.php';
					
					foreach ( $classpaths as $classpath ) {
						$fullpath = implode( $this->seperator, [
							rtrim( $dir, $this->seperator ),
							trim( $classpath, $this->seperator ),
							ltrim( $filename, $this->seperator )
						] );
						
						if ( file_exists( $fullpath ) ) {
							include_once $fullpath;
						}
					}
				}
			} );
		}
	}
}