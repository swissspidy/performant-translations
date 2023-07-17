<?php
/*
Plugin Name: Custom Internationalized Plugin
Plugin URI: https://wordpress.org/
Description: For testing purposes only.
Version: 1.0.0
Text Domain: custom-internationalized-plugin
Domain Path: languages/
*/

function custom_i18n_plugin_test() {
	return __( 'This is a dummy plugin', 'custom-internationalized-plugin' );
}

function custom_i18n_plugin_switch_locales_often() {
	$start = microtime();

	for($i = 0; $i < 500; $i++) {
		$is_switched = switch_to_locale('de_DE');
		custom_i18n_plugin_test();
		if ($is_switched) {
			restore_previous_locale();
		}

		$is_switched = switch_to_locale('es_ES');
		custom_i18n_plugin_test();
		if ($is_switched) {
			restore_previous_locale();
		}

		restore_current_locale();
	}

	$end = microtime();

	return $end - $start;
}

add_action(
	'init',
	static function() {
		load_plugin_textdomain( 'custom-internationalized-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( ! function_exists( 'perflab_server_timing_register_metric' ) ) {
			return;
		}

		if ( isset( $_GET['switch-locales'] ) && '1' === $_GET['switch-locales'] ) {
			perflab_server_timing_register_metric(
				'locale-switching',
				array(
					'measure_callback' => function (Perflab_Server_Timing_Metric $metric) {
						$metric->set_value(custom_i18n_plugin_switch_locales_often());
					},
					'access_cap' => 'exist',
				)
			);
		}
	}
);
