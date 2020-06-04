<?php

namespace WPDEPM\Core;

class WordPressHooksWrapper {

	public static $prefix = 'wpdepm/';

	public static function name( string $value ) {
		$value = preg_replace( '/^' . preg_quote( self::$prefix, '/' ) . '/', '', $value );
		return self::$prefix . $value;
	}

	/**
	 * Run action with YoastExtended/ prefixed
	 *
	 * @link https://developer.wordpress.org/reference/functions/do_action/
	 *
	 * @return void
	 */
	public static function do_action() {
		$args = func_get_args();
		$tag = array_shift( $args );

		call_user_func_array( 'do_action', array_merge( [ self::name( $tag ) ], $args ) );
	}

	/**
	 * Add action with YoastExtended/ prefixed
	 *
	 * @link	https://developer.wordpress.org/reference/functions/add_action/
	 *
	 * @param	string		$tag
	 * @param	callable	$function_to_add
	 * @param	integer		$priority
	 * @param	integer		$accepted_args
	 * @return	void
	 */
	public static function add_action( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ) {
		add_action( self::name( $tag ), $function_to_add, $priority, $accepted_args );
	}

	/**
	 * Add filter with YoastExtended/ prefixed
	 *
	 * @link https://developer.wordpress.org/reference/functions/apply_filters/
	 *
	 * @return mixed
	 */
	public static function apply_filters() {
		$args = func_get_args();
		$tag = array_shift( $args );
		$value = array_shift( $args );

		$value = call_user_func_array( 'apply_filters', array_merge( [ self::name( $tag ), $value ], $args ) );

		return $value;
	}

	/**
	 * Add filter with YoastExtended/ prefixed
	 *
	 * @link	https://developer.wordpress.org/reference/functions/add_filter/
	 *
	 * @param	string		$tag
	 * @param	callable	$function_to_add
	 * @param	integer		$priority
	 * @param	integer		$accepted_args
	 * @return	void
	 */
	public static function add_filter( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ) {
		add_filter( self::name( $tag ), $function_to_add, $priority, $accepted_args );
	}

}
