<?php
/**
 * Plugin Name: Server Timing Admin Output
 * Description: Run Server Timing API in the WordPress admin.
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
	'wp_loaded',
	static function() {
		if ( ! function_exists( 'perflab_server_timing' ) ) {
			return;
		}

		$server_timing = perflab_server_timing();

		add_filter( 'admin_init', array( $server_timing, 'on_template_include' ), PHP_INT_MIN );
	},
	100
);
