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
	// TODO: Also delete JSON files coming from Ginger MO, but do not touch script translations from WordPress.
	$lang_files = glob( $location . '/*.php' );
	if ( $lang_files ) {
		foreach ( $lang_files as $lang_file ) {
			wp_delete_file( $lang_file );
		}
	}
}
