<?php

/**
 * Plugin Name: WP dependancy manager
 * Plugin URI: https://github.com/thelevicole/wp-dependancy-manager/
 * Author: Levi Cole
 * Author URI: https://thelevicole.com
 * Description: Manage how frontend dependancies are loaded
 * Version: 1.0.0
 * Text Domain: wpdepm
 * Network: true
 * Requires at least: 5.2
 * Requires PHP: 7.2
 */


if ( !defined( 'WPDEPM_VERSION' ) ) {
	define( 'WPDEPM_VERSION', '1.0.0' );
}

if ( !defined( 'WPDEPM_PATH' ) ) {
	define( 'WPDEPM_PATH', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'WPDEPM_URL' ) ) {
	define( 'WPDEPM_URL', plugin_dir_url( __FILE__ ) );
}

if ( !defined( 'WPDEPM_BASENAME' ) ) {
	define( 'WPDEPM_BASENAME', plugin_basename( __FILE__ ) );
}


require_once WPDEPM_PATH . 'src/Autoloader.php';

$autoloader = new \WPDEPM\Autoloader( WPDEPM_PATH );

/**
 * Load shared classes
 */
$autoloader->loadArray( [ 'WPDEPM\\Core' => 'src/Core' ], 'psr-4' );

$hanlder = new WPDEPM\Core\AssetHandler;


/**
 * Must be frontend
 */
if ( !is_admin() && !wp_doing_ajax() ) {
	// Do frontend only
}

/**
 * Must be in CMS and login page
 */
else if ( is_admin() && !wp_doing_ajax() ) {
	// Do admin only
}






