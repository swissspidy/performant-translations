<?php
/**
 * Installs WordPress for the purpose of the benchmarks
 *
 * Copied from tests/phpunit/includes/install.php in core,
 * without the "echo" statements.
 */
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];
$tests_dir_path   = $argv[2];
$multisite        = in_array( 'run_ms_tests', $argv, true );

define( 'WP_INSTALLING', true );

/*
 * Cron tries to make an HTTP request to the site, which always fails,
 * because tests are run in CLI mode only.
 */
define( 'DISABLE_WP_CRON', true );

require_once $config_file_path;
require_once $tests_dir_path . '/functions.php';

// Set the theme to our special empty theme, to avoid interference from the current Twenty* theme.
if ( ! defined( 'WP_DEFAULT_THEME' ) ) {
	define( 'WP_DEFAULT_THEME', 'default' );
}

tests_reset__SERVER();

$PHP_SELF            = '/index.php';
$GLOBALS['PHP_SELF'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

tests_add_filter( 'wp_die_handler', '_wp_die_handler_filter_exit' );

require_once ABSPATH . 'wp-settings.php';

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-includes/class-wpdb.php';

register_theme_directory( $tests_dir_path . '/../data/themedir1' );

/*
 * default_storage_engine and storage_engine are the same option, but storage_engine
 * was deprecated in MySQL (and MariaDB) 5.5.3, and removed in 5.7.
 */
if ( version_compare( $wpdb->db_version(), '5.5.3', '>=' ) ) {
	$wpdb->query( 'SET default_storage_engine = InnoDB' );
} else {
	$wpdb->query( 'SET storage_engine = InnoDB' );
}
$wpdb->select( DB_NAME, $wpdb->dbh );

$wpdb->query( 'SET foreign_key_checks = 0' );
foreach ( $wpdb->tables() as $table => $prefixed_table ) {
	//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS $prefixed_table" );
}

foreach ( $wpdb->tables( 'ms_global' ) as $table => $prefixed_table ) {
	//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS $prefixed_table" );

	// We need to create references to ms global tables.
	if ( $multisite ) {
		$wpdb->$table = $prefixed_table;
	}
}
$wpdb->query( 'SET foreign_key_checks = 1' );

// Prefill a permalink structure so that WP doesn't try to determine one itself.
add_action( 'populate_options', '_set_default_permalink_structure_for_tests' );

wp_install( WP_TESTS_TITLE, 'admin', WP_TESTS_EMAIL, true, null, 'password' );

// Delete dummy permalink structure, as prefilled above.
delete_option( 'permalink_structure' );

remove_action( 'populate_options', '_set_default_permalink_structure_for_tests' );
