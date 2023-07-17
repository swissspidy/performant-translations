<?php
/**
 * Plugin Name: Server Timing DB Queries
 * Description: Add total number of db queries to the Performance Lab plugin's Server Timing API.
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
			'db-queries',
			array(
				'measure_callback' => function( Perflab_Server_Timing_Metric $metric ) {
					add_action(
						'perflab_server_timing_send_header',
						static function() use ( $metric ) {
							global $wpdb;
							$metric->set_value( $wpdb->num_queries );
						}
					);
				},
				'access_cap'       => 'exist',
			)
		);
	}
);
