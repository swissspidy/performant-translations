<?php
/**
 * Plugin Name: Server Timing Memory Usage
 * Description: Add memory usage to the Performance Lab plugin's Server Timing API.
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_action(
	'plugins_loaded',
	static function() {
		if ( ! function_exists( 'perflab_server_timing_register_metric' ) ) {
			return;
		}

		perflab_server_timing_register_metric(
			'memory-usage',
			array(
				'measure_callback' => function( Perflab_Server_Timing_Metric $metric ) {
					add_action(
						'perflab_server_timing_send_header',
						static function() use ( $metric ) {
							$metric->set_value( memory_get_usage() );
						}
					);
				},
				'access_cap'       => 'exist',
			)
		);
	}
);
