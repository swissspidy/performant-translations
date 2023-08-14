<?php

/**
 * @BeforeMethods("setUp")
 * @AfterMethods("tearDown")
 * @Revs(100)
 * @Iterations(50)
 */
class LoadTextdomainBench {

	public function setUp() {
		$GLOBALS['l10n']                   = array();
		$GLOBALS['l10n_unloaded']          = array();
		$GLOBALS['wp_textdomain_registry'] = new WP_Textdomain_Registry();
	}

	public function tearDown() {
		add_filter( 'override_load_textdomain', array( 'Ginger_MO_Translation_Compat', 'load_textdomain' ), 100, 4 );
		add_filter( 'override_unload_textdomain', array( 'Ginger_MO_Translation_Compat', 'unload_textdomain' ), 100, 2 );
	}

	public function bench_default_pomo() {
		remove_filter( 'override_load_textdomain', array( 'Ginger_MO_Translation_Compat', 'load_textdomain' ), 100 );
		remove_filter( 'override_unload_textdomain', array( 'Ginger_MO_Translation_Compat', 'unload_textdomain' ), 100 );
		load_default_textdomain( 'de_DE' );
	}

	public function bench_mo_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'mo';
			}
		);
		load_default_textdomain( 'de_DE' );
	}

	public function bench_json_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'json';
			}
		);
		load_default_textdomain( 'de_DE' );
	}

	public function bench_php_files() {
		add_filter(
			'ginger_mo_preferred_format',
			static function() {
				return 'php';
			}
		);
		load_default_textdomain( 'de_DE' );
	}
}
