<?php
/**
 * Plugin Name: Maybe clear caches
 * Description: Allows clearing caches (OPCache, object cache, APCU) visiting example.com?action=clear-XYZ
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
	'plugins_loaded',
	static function () {
		if ( isset( $_GET['clear-cache'] ) ) {
			switch ( $_GET['clear-cache'] ) {
				case 'opcache':
					if ( function_exists( 'opcache_reset' ) && opcache_reset() ) {
						status_header( 202 );
					} else {
						status_header( 400 );
					}
					die;

				case 'object-cache':
					if ( function_exists( 'opcache_reset' ) && opcache_reset() ) {
						status_header( 202 );
					} else {
						status_header( 400 );
					}
					die;

				case 'apcu-cache':
					if ( function_exists( 'apcu_clear_cache' ) && apcu_clear_cache() ) {
						status_header( 202 );
					} else {
						status_header( 400 );
					}
					die;
			}
		}
	},
	1
);
