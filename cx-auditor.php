<?php
/**
 * Plugin Name: CX Auditor
 * Description: Generate a preliminary site audit report for any WordPress site using just its URL. No login required.
 * Version:     1.0.0
 * Author:      Codexpert
 * License:     GPLv2 or later
 * Text Domain: cx-auditor
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Codexpert\CX_Auditor
 */

defined( 'ABSPATH' ) || exit;

define( 'CX_AUDITOR_VERSION', '1.0.0' );
define( 'CX_AUDITOR_FILE', __FILE__ );
define( 'CX_AUDITOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CX_AUDITOR_URL', plugin_dir_url( __FILE__ ) );
define( 'CX_AUDITOR_BASENAME', plugin_basename( __FILE__ ) );

$cx_auditor_autoload = CX_AUDITOR_PATH . 'vendor/autoload.php';
if ( file_exists( $cx_auditor_autoload ) ) {
	require_once $cx_auditor_autoload;
}

spl_autoload_register( static function ( $class ) {
	$prefix = 'Codexpert\\CX_Auditor\\';
	$len    = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative = substr( $class, $len );
	$file     = CX_AUDITOR_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

require_once CX_AUDITOR_PATH . 'src/Plugin.php';

add_action( 'plugins_loaded', static function () {
	if ( class_exists( \Codexpert\CX_Auditor\Plugin::class ) ) {
		\Codexpert\CX_Auditor\Plugin::instance()->init();
	}
} );