<?php
/**
 * Plugin Name: Disable translation updates
 * Description: Prevent installing translations when changing the locale under Settings -> General.
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_filter(
	'file_mod_allowed',
	static function ( $allowed, $context ) {
		if ( 'can_install_language_pack' === $context && ! defined( 'WP_CLI' ) ) {
			return false;
		}

		return $allowed;
	},
	10,
	2
);
