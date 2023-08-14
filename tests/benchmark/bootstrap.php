<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 *
 * Hooked into "init" so that the load_default_textdomain() call
 * in wp-settings.php isn't filtered yet by the plugin.
 */
function _manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/ginger-mo.php';
}

tests_add_filter( 'init', '_manually_load_plugin' );

define( 'DIR_PLUGIN_TESTDATA', realpath( $_tests_dir . '/data' ) );
define( 'WP_PLUGIN_DIR', realpath( $_tests_dir . '/data/plugins' ) );

// Start up the WP testing environment.
// Based on the WP PHPUnit bootstrap file, without the PHPUnit specific parts.

/**
 * Installs WordPress for running the tests and loads WordPress and the test libraries
 */

$config_file_path = $_tests_dir;
if ( ! file_exists( $config_file_path . '/wp-tests-config.php' ) ) {
	// Support the config file from the root of the develop repository.
	if ( basename( $config_file_path ) === 'phpunit' && basename( dirname( $config_file_path ) ) === 'tests' ) {
		$config_file_path = dirname( $config_file_path, 2 );
	}
}
$config_file_path .= '/wp-tests-config.php';

global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer, $wp_theme_directories;

if ( ! is_readable( $config_file_path ) ) {
	echo 'Error: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.' . PHP_EOL;
	exit( 1 );
}

require_once $config_file_path;
require_once $_tests_dir . '/includes/functions.php';
define( 'DIR_TESTDATA', $_tests_dir . '/data' );

define( 'WP_LANG_DIR', realpath( DIR_TESTDATA . '/languages' ) );

$PHP_SELF            = '/index.php';
$GLOBALS['PHP_SELF'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

// Override the PHPMailer.
require_once $_tests_dir . '/includes/mock-mailer.php';
$phpmailer = new MockPHPMailer( true );

system( WP_PHP_BINARY . ' ' . escapeshellarg( __DIR__ . '/install.php' ) . ' ' . escapeshellarg( $config_file_path ) . ' ' . escapeshellarg( $_tests_dir . '/includes' ), $retval );

// Load WordPress.
require_once ABSPATH . 'wp-settings.php';
