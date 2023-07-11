<?php
/**
 * Plugin Name: Maybe clear OPCache
 * Description: Allows resetting the OPCache by visiting example.com?opcache_action=clear-opcache
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
	'plugins_loaded',
	static function () {
		if ( isset( $_GET['opcache_action'] ) && 'clear-opcache' === $_GET['opcache_action'] ) {
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
