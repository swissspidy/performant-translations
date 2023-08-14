<?php

/**
 * @BeforeMethods("setUp")
 * @AfterMethods("tearDown")
 * @Revs(100)
 * @Iterations(50)
 */
class SwitchToLocaleBench {
	public function setUp() {
		$GLOBALS['l10n']                   = array();
		$GLOBALS['l10n_unloaded']          = array();
		$GLOBALS['wp_textdomain_registry'] = new WP_Textdomain_Registry();

		require_once DIR_TESTDATA . '/plugins/custom-internationalized-plugin/custom-internationalized-plugin.php';
	}

	public function tearDown() {
		add_filter( 'override_load_textdomain', array( 'Ginger_MO_Translation_Compat', 'load_textdomain' ), 100, 4 );
		add_filter( 'override_unload_textdomain', array( 'Ginger_MO_Translation_Compat', 'unload_textdomain' ), 100, 2 );
	}

	public function bench_default_pomo() {
		remove_filter( 'override_load_textdomain', array( 'Ginger_MO_Translation_Compat', 'load_textdomain' ), 100 );
		remove_filter( 'override_unload_textdomain', array( 'Ginger_MO_Translation_Compat', 'unload_textdomain' ), 100 );

		custom_i18n_plugin_test();
		switch_to_locale( 'es_ES' );
		custom_i18n_plugin_test();
		switch_to_locale( 'de_DE' );
		custom_i18n_plugin_test();
	}

	public function bench_mo_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'mo';
			}
		);

		custom_i18n_plugin_test();

		custom_i18n_plugin_test();
		switch_to_locale( 'es_ES' );
		custom_i18n_plugin_test();
		switch_to_locale( 'de_DE' );
		custom_i18n_plugin_test();
	}

	public function bench_json_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'json';
			}
		);

		custom_i18n_plugin_test();

		custom_i18n_plugin_test();
		switch_to_locale( 'es_ES' );
		custom_i18n_plugin_test();
		switch_to_locale( 'de_DE' );
		custom_i18n_plugin_test();
	}

	public function bench_php_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'php';
			}
		);

		custom_i18n_plugin_test();
		switch_to_locale( 'es_ES' );
		custom_i18n_plugin_test();
		switch_to_locale( 'de_DE' );
		custom_i18n_plugin_test();
	}
}
