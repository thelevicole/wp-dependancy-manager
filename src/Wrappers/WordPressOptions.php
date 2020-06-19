<?php

namespace WPDEPM\Core;

class WordPressOptions {

	public static $prefix = 'wpdepm_';

	public static function name( string $value ) {
		$value = preg_replace( '/^' . preg_quote( self::$prefix, '/' ) . '/', '', $value );
		return self::$prefix . $value;
	}

	public static function get( string $name, $default = null ) {
		return get_option( self::name( $name ), $default );
	}

	public static function update( string $name, $value, bool $autoload = true ) {
		return update_option( self::name( $name ), $value, $autoload );
	}

}
