<?php
/**
 * Uninstall handler.
 *
 * @package Performant_Translations
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

$locations = array(
	WP_LANG_DIR,
	WP_LANG_DIR . '/plugins',
	WP_LANG_DIR . '/themes',
);

foreach ( $locations as $location ) {
	$lang_files = glob( $location . '/*.mo.php' );
	if ( $lang_files ) {
		foreach ( $lang_files as $lang_file ) {
			wp_delete_file( $lang_file );
		}
	}

	$lang_files = glob( $location . '/*.mo.json' );
	if ( $lang_files ) {
		foreach ( $lang_files as $lang_file ) {
			wp_delete_file( $lang_file );
		}
	}
}
