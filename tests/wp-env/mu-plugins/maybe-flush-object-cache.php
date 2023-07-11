<?php
/**
 * Plugin Name: Maybe flush object cache
 * Description: Allows flushing the object cache by visiting example.com?action=flush-object-cache
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
	'plugins_loaded',
	static function () {
		if ( isset( $_GET['action'] ) && 'flush-object-cache' === $_GET['action'] ) {
			if ( function_exists( 'opcache_reset' ) && opcache_reset() ) {
				status_header( 202 );
			} else {
				status_header( 400 );
			}

			die();
		}
	},
	1
);
