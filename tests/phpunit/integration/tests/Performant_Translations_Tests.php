<?php

/**
 * @coversDefaultClass Performant_Translations
 */
class Performant_Translations_Tests extends WP_UnitTestCase {
	/**
	 * @return void
	 */
	public function tear_down() {
		$generated_translation_files = array(
			DIR_TESTDATA . '/pomo/simple.l10n.php',
			DIR_TESTDATA . '/pomo/context.l10n.php',
			WP_LANG_DIR . '/plugins/internationalized-plugin-de_DE.l10n.php',
			WP_LANG_DIR . '/themes/internationalized-theme-de_DE.l10n.php',
			WP_LANG_DIR . '/de_DE.l10n.php',
			WP_LANG_DIR . '/admin-de_DE.l10n.php',
			WP_LANG_DIR . '/admin-network-de_DE.l10n.php',
			WP_LANG_DIR . '/continents-cities-de_DE.l10n.php',
			WP_PLUGIN_DIR . '/custom-internationalized-plugin/languages/custom-internationalized-plugin-de_DE.l10n.php',
			WP_LANG_DIR . '/plugins/custom-internationalized-plugin-de_DE.l10n.php',
			DIR_TESTDATA . '/themedir1/custom-internationalized-theme/languages/de_DE.l10n.php',
			WP_LANG_DIR . '/themes/custom-internationalized-theme-de_DE.l10n.php',
		);

		foreach ( $generated_translation_files as $file ) {
			if ( file_exists( $file ) ) {
				chmod( $file, 0644 );
				$this->unlink( $file );
			}
		}

		remove_all_filters( 'performant_translations_convert_files' );
		remove_all_filters( 'performant_translations_preferred_format' );

		remove_all_filters( 'filesystem_method' );

		unload_textdomain( 'wp-tests-domain' );

		switch_theme( 'default' );

		parent::tear_down();
	}

	/**
	 * @covers ::init
	 *
	 * @return void
	 */
	public function test_init() {
		$this->assertSame( 100, has_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ) ) );
		$this->assertSame( 100, has_filter( 'override_unload_textdomain', array( Performant_Translations::class, 'unload_textdomain' ) ) );
	}

	/**
	 * @covers ::load_textdomain
	 * @covers Ginger_MO::get_entries
	 * @covers Ginger_MO::get_headers
	 * @covers Ginger_MO::normalize_header
	 *
	 * @return void
	 */
	public function test_load_textdomain() {
		global $l10n;

		$loaded_before_load = is_textdomain_loaded( 'wp-tests-domain' );

		$load_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$loaded_after_load = is_textdomain_loaded( 'wp-tests-domain' );

		$compat_instance   = $l10n['wp-tests-domain'] ?? null;
		$expected_instance = class_exists( 'WP_Translations' ) ? WP_Translations::class : Performant_Translations_Compat_Provider::class;

		$is_loaded = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );
		$headers   = Ginger_MO::instance()->get_headers( 'wp-tests-domain' );
		$entries   = Ginger_MO::instance()->get_entries( 'wp-tests-domain' );

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$loaded_after_unload = is_textdomain_loaded( 'wp-tests-domain' );

		$this->assertFalse( $loaded_before_load, 'Text domain was already loaded at beginning of the test' );
		$this->assertTrue( $load_successful, 'Text domain not successfully loaded' );
		$this->assertTrue( $loaded_after_load, 'Text domain is not considered loaded' );
		$this->assertInstanceOf( $expected_instance, $compat_instance, 'No compat provider instance used' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
		$this->assertFalse( $loaded_after_unload, 'Text domain still considered loaded after unload' );
		$this->assertTrue( $is_loaded, 'Text domain not considered loaded in Ginger-MO' );
		$this->assertEqualSetsWithIndex(
			array(
				'Project-Id-Version'   => 'WordPress 2.6-bleeding',
				'Report-Msgid-Bugs-To' => 'wp-polyglots@lists.automattic.com',
			),
			$headers,
			'Actual translation headers do not match expected ones'
		);
		$this->assertEqualSetsWithIndex(
			array(
				'baba'       => 'dyado',
				"kuku\nruku" => 'yes',
			),
			$entries,
			'Actual translation entries do not match expected ones'
		);
	}

	/**
	 * @covers ::load_textdomain
	 * @covers Ginger_MO::get_entries
	 * @covers Ginger_MO::get_headers
	 * @covers Ginger_MO::normalize_header
	 *
	 * @return void
	 */
	public function test_load_textdomain_existing_override() {
		add_filter( 'override_load_textdomain', '__return_true' );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$is_loaded_wp = is_textdomain_loaded( 'wp-tests-domain' );

		$is_loaded = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );

		remove_filter( 'override_load_textdomain', '__return_true' );

		$this->assertFalse( $is_loaded_wp );
		$this->assertFalse( $is_loaded );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_mo_files() {
		add_filter(
			'performant_translations_preferred_format',
			static function () {
				return 'mo';
			}
		);

		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php' );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_and_reads_php_files() {
		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$load_php_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.l10n.php' );

		$unload_php_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_plugin_language_files_despite_permission_issues() {
		global $wp_textdomain_registry;

		// Create a non-writable PHP version to simulate issues with writing to the same directory.
		file_put_contents( WP_PLUGIN_DIR . '/custom-internationalized-plugin/languages/custom-internationalized-plugin-de_DE.l10n.php', '' );
		chmod( WP_PLUGIN_DIR . '/custom-internationalized-plugin/languages/custom-internationalized-plugin-de_DE.l10n.php', 0000 );

		$load_mo_successful = load_textdomain(
			'custom-internationalized-plugin',
			WP_PLUGIN_DIR . '/custom-internationalized-plugin/languages/custom-internationalized-plugin-de_DE.mo'
		);

		$unload_mo_successful = unload_textdomain( 'custom-internationalized-plugin' );

		$load_php_successful = load_textdomain(
			'custom-internationalized-plugin',
			WP_LANG_DIR . '/plugins/custom-internationalized-plugin-de_DE.l10n.php'
		);

		$unload_php_successful = unload_textdomain( 'custom-internationalized-plugin' );

		$load_converted_mo_successful = load_textdomain(
			'custom-internationalized-plugin',
			WP_LANG_DIR . '/plugins/custom-internationalized-plugin-de_DE.mo'
		);

		$unload_converted_mo_successful = unload_textdomain( 'custom-internationalized-plugin' );

		$mo_file_path = $wp_textdomain_registry->get( 'custom-internationalized-plugin', get_locale() );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileExists( WP_LANG_DIR . '/plugins/custom-internationalized-plugin-de_DE.l10n.php' );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
		$this->assertTrue( $load_converted_mo_successful );
		$this->assertTrue( $unload_converted_mo_successful );
		$this->assertSame( WP_LANG_DIR . '/plugins/', $mo_file_path );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_theme_language_files_despite_permission_issues() {
		global $wp_textdomain_registry;

		switch_theme( 'custom-internationalized-theme' );

		// Create a non-writable PHP version to simulate issues with writing to the same directory.
		file_put_contents( DIR_TESTDATA . '/themedir1/custom-internationalized-theme/languages/de_DE.l10n.php', '' );
		chmod( DIR_TESTDATA . '/themedir1/custom-internationalized-theme/languages/de_DE.l10n.php', 0000 );

		$load_mo_successful = load_textdomain(
			'custom-internationalized-theme',
			DIR_TESTDATA . '/themedir1/custom-internationalized-theme/languages/de_DE.mo'
		);

		$unload_mo_successful = unload_textdomain( 'custom-internationalized-theme' );

		$load_php_successful = load_textdomain(
			'custom-internationalized-theme',
			WP_LANG_DIR . '/themes/custom-internationalized-theme-de_DE.l10n.php'
		);

		$unload_php_successful = unload_textdomain( 'custom-internationalized-theme' );

		$load_converted_mo_successful = load_textdomain(
			'custom-internationalized-theme',
			DIR_TESTDATA . '/themedir1/custom-internationalized-theme/languages/de_DE.mo'
		);

		$unload_converted_mo_successful = unload_textdomain( 'custom-internationalized-theme' );

		$mo_file_path = $wp_textdomain_registry->get( 'custom-internationalized-theme', get_locale() );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileExists( WP_LANG_DIR . '/themes/custom-internationalized-theme-de_DE.l10n.php' );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
		$this->assertTrue( $load_converted_mo_successful );
		$this->assertTrue( $unload_converted_mo_successful );
		$this->assertSame( WP_LANG_DIR . '/themes/', $mo_file_path );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_and_reads_php_files_if_filtered_format_is_unsupported() {
		add_filter(
			'performant_translations_preferred_format',
			static function () {
				return 'unknown-format';
			}
		);

		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$load_php_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.l10n.php' );

		$unload_php_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_and_reads_php_files_no_wp_filesystem() {
		add_filter( 'filesystem_method', '__return_empty_string' );

		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$load_php_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.l10n.php' );

		$unload_php_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_does_not_create_php_files_if_disabled() {
		add_filter( 'performant_translations_convert_files', '__return_false' );

		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php' );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_existing_translation_is_kept() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		remove_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/context.mo' );

		add_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100, 4 );

		$simple  = __( 'baba', 'wp-tests-domain' );
		$context = _x( 'one dragon', 'not so dragon', 'wp-tests-domain' );

		$this->assertSame( 'dyado', $simple );
		$this->assertSame( 'oney dragoney', $context );
		$this->assertInstanceOf( Translations::class, $l10n['wp-tests-domain'] );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_loads_existing_translation() {
		global $l10n;

		remove_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		add_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100, 4 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/context.mo' );

		$simple  = __( 'baba', 'wp-tests-domain' );
		$context = _x( 'one dragon', 'not so dragon', 'wp-tests-domain' );

		$this->assertSame( 'dyado', $simple );
		$this->assertSame( 'oney dragoney', $context );
		$this->assertInstanceOf( Performant_Translations_Compat_Provider::class, $l10n['wp-tests-domain'] );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_loads_existing_translation_mo_files() {
		global $l10n;

		add_filter(
			'performant_translations_preferred_format',
			static function () {
				return 'mo';
			}
		);

		remove_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		add_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100, 4 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/context.mo' );

		$simple  = __( 'baba', 'wp-tests-domain' );
		$context = _x( 'one dragon', 'not so dragon', 'wp-tests-domain' );

		$this->assertSame( 'dyado', $simple );
		$this->assertSame( 'oney dragoney', $context );
		$this->assertInstanceOf( Performant_Translations_Compat_Provider::class, $l10n['wp-tests-domain'] );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_loads_existing_translation_php_files() {
		global $l10n;

		// Just to ensure the PHP files exist.
		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );
		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/context.mo' );
		unload_textdomain( 'wp-tests-domain' );

		remove_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		add_filter( 'override_load_textdomain', array( Performant_Translations::class, 'load_textdomain' ), 100, 4 );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/context.mo' );

		$simple  = __( 'baba', 'wp-tests-domain' );
		$context = _x( 'one dragon', 'not so dragon', 'wp-tests-domain' );

		$this->assertSame( 'dyado', $simple );
		$this->assertSame( 'oney dragoney', $context );
		$this->assertInstanceOf( Performant_Translations_Compat_Provider::class, $l10n['wp-tests-domain'] );
	}

	/**
	 * @param string $domain
	 * @param string $file
	 * @return void
	 */
	public function _on_load_textdomain( $domain, $file ) {
		remove_action( 'load_textdomain', array( $this, '_on_load_textdomain' ) );
		load_textdomain( $domain, $file );
	}

	/**
	 * @covers ::load_textdomain
	 *
	 * @return void
	 */
	public function test_load_textdomain_inception_does_not_create_duplicate_files() {
		add_action( 'load_textdomain', array( $this, '_on_load_textdomain' ), 10, 2 );

		// Just to ensure the PHP files exist.
		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );
		unload_textdomain( 'wp-tests-domain' );

		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php.php' );
	}

	/**
	 * @covers ::unload_textdomain
	 * @covers Ginger_MO::get_entries
	 * @covers Ginger_MO::get_headers
	 * @covers Ginger_MO::normalize_header
	 *
	 * @return void
	 */
	public function test_unload_textdomain() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$loaded_after_unload = is_textdomain_loaded( 'wp-tests-domain' );

		$compat_instance = $l10n['wp-tests-domain'] ?? null;

		$is_loaded = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );
		$headers   = Ginger_MO::instance()->get_headers( 'wp-tests-domain' );
		$entries   = Ginger_MO::instance()->get_entries( 'wp-tests-domain' );

		$this->assertNull( $compat_instance, 'Compat instance was not removed' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
		$this->assertFalse( $loaded_after_unload, 'Text domain still considered loaded after unload' );
		$this->assertFalse( $is_loaded, 'Text domain still considered loaded in Ginger-MO' );
		$this->assertEmpty( $headers, 'Actual translation headers are not empty' );
		$this->assertEmpty( $entries, 'Actual translation entries are not empty' );
	}

	/**
	 * @covers ::unload_textdomain
	 *
	 * @return void
	 */
	public function test_unload_textdomain_existing_override() {
		add_filter( 'override_unload_textdomain', '__return_true' );

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$is_loaded = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );

		remove_filter( 'override_unload_textdomain', '__return_true' );

		$unload_successful_after = unload_textdomain( 'wp-tests-domain' );

		$is_loaded_after = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );

		$this->assertTrue( $unload_successful );
		$this->assertTrue( $is_loaded );
		$this->assertTrue( $unload_successful_after );
		$this->assertFalse( $is_loaded_after );
	}

	/**
	 * @covers ::load_textdomain
	 * @covers ::unload_textdomain
	 *
	 * @return void
	 */
	public function test_switch_to_locale_translations_stay_loaded_default_textdomain() {
		switch_to_locale( 'es_ES' );

		$actual = __( 'Invalid parameter.' );

		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'default' ) );
		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'default', 'es_ES' ) );

		restore_previous_locale();

		$actual_2 = __( 'Invalid parameter.' );

		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'default', 'es_ES' ) );

		$this->assertSame( 'Parámetro no válido. ', $actual );
		$this->assertSame( 'Invalid parameter.', $actual_2 );
	}

	/**
	 * @covers ::load_textdomain
	 * @covers ::unload_textdomain
	 * @covers ::change_locale
	 *
	 * @return void
	 */
	public function test_switch_to_locale_translations_stay_loaded_custom_textdomain() {
		$this->assertSame( 'en_US', Ginger_MO::instance()->get_locale() );

		require_once DIR_TESTDATA . '/plugins/internationalized-plugin.php';

		$before = i18n_plugin_test();

		switch_to_locale( 'es_ES' );

		$actual = i18n_plugin_test();

		$this->assertSame( 'es_ES', Ginger_MO::instance()->get_locale() );
		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'internationalized-plugin', 'es_ES' ) );
		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'default', 'es_ES' ) );
		$this->assertFalse( Ginger_MO::instance()->is_loaded( 'foo-bar', 'es_ES' ) );

		restore_previous_locale();

		$after = i18n_plugin_test();

		$this->assertTrue( Ginger_MO::instance()->is_loaded( 'internationalized-plugin', 'es_ES' ) );

		$this->assertSame( 'This is a dummy plugin', $before );
		$this->assertSame( 'Este es un plugin dummy', $actual );
		$this->assertSame( 'This is a dummy plugin', $after );
	}

	/**
	 * @covers ::upgrader_process_complete
	 * @covers ::opcache_invalidate
	 *
	 * @return void
	 */
	public function test_create_translation_files_after_translations_update() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-language-pack-upgrader.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-upgrader-skin.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-language-pack-upgrader.php';

		$filter = new MockAction();
		add_filter( 'wp_opcache_invalidate_file', [ $filter, 'filter' ] );

		$upgrader = new Dummy_Language_Pack_Upgrader( new Dummy_Upgrader_Skin() );

		// These translations exist in the core test suite.
		// See https://github.com/WordPress/wordpress-develop/tree/e3d345800d3403f3902dc7b18c1ddb07158b0bd3/tests/phpunit/data/languages.
		$result = $upgrader->bulk_upgrade(
			array(
				(object) array(
					'type'     => 'plugin',
					'slug'     => 'internationalized-plugin',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'theme',
					'slug'     => 'internationalized-theme',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'core',
					'slug'     => 'default',
					'language' => 'es_ES',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
			)
		);

		$this->assertGreaterThan( 0, $filter->get_call_count() );

		$this->assertIsNotBool( $result );
		$this->assertNotWPError( $result );
		$this->assertNotEmpty( $result );

		$this->assertFileExists( WP_LANG_DIR . '/plugins/internationalized-plugin-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/themes/internationalized-theme-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/es_ES.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/admin-es_ES.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/admin-network-es_ES.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/continents-cities-es_ES.l10n.php' );
	}

	/**
	 * @covers ::upgrader_process_complete
	 *
	 * @return void
	 */
	public function test_create_translation_files_after_translations_update_if_filtered_format_is_unsupported() {
		add_filter(
			'performant_translations_preferred_format',
			static function () {
				return 'unknown-format';
			}
		);

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-language-pack-upgrader.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-upgrader-skin.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-language-pack-upgrader.php';

		$upgrader = new Dummy_Language_Pack_Upgrader( new Dummy_Upgrader_Skin() );

		// These translations exist in the core test suite.
		// See https://github.com/WordPress/wordpress-develop/tree/e3d345800d3403f3902dc7b18c1ddb07158b0bd3/tests/phpunit/data/languages.
		$result = $upgrader->bulk_upgrade(
			array(
				(object) array(
					'type'     => 'plugin',
					'slug'     => 'internationalized-plugin',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'theme',
					'slug'     => 'internationalized-theme',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'core',
					'slug'     => 'default',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
			)
		);

		$this->assertIsNotBool( $result );
		$this->assertNotWPError( $result );
		$this->assertNotEmpty( $result );

		$this->assertFileExists( WP_LANG_DIR . '/plugins/internationalized-plugin-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/themes/internationalized-theme-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/de_DE.l10n.php' );
	}

	/**
	 * @covers ::upgrader_process_complete
	 *
	 * @return void
	 */
	public function test_create_translation_files_after_translations_update_no_wp_filesystem() {
		$callback = static function () {
			add_filter( 'filesystem_method', '__return_empty_string' );
		};

		add_action( 'upgrader_process_complete', $callback, 1 );

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-language-pack-upgrader.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-upgrader-skin.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-language-pack-upgrader.php';

		$upgrader = new Dummy_Language_Pack_Upgrader( new Dummy_Upgrader_Skin() );

		// These translations exist in the core test suite.
		// See https://github.com/WordPress/wordpress-develop/tree/e3d345800d3403f3902dc7b18c1ddb07158b0bd3/tests/phpunit/data/languages.
		$result = $upgrader->bulk_upgrade(
			array(
				(object) array(
					'type'     => 'plugin',
					'slug'     => 'internationalized-plugin',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'theme',
					'slug'     => 'internationalized-theme',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
				(object) array(
					'type'     => 'core',
					'slug'     => 'default',
					'language' => 'de_DE',
					'version'  => '99.9.9',
					'package'  => '/tmp/notused.zip',
				),
			)
		);

		remove_action( 'upgrader_process_complete', $callback, 1 );

		$this->assertIsNotBool( $result );
		$this->assertNotWPError( $result );
		$this->assertNotEmpty( $result );

		$this->assertFileExists( WP_LANG_DIR . '/plugins/internationalized-plugin-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/themes/internationalized-theme-de_DE.l10n.php' );
		$this->assertFileExists( WP_LANG_DIR . '/de_DE.l10n.php' );
	}

	/**
	 * @covers ::upgrader_process_complete
	 *
	 * @return void
	 */
	public function test_do_not_create_translations_after_plugin_update() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-upgrader-skin.php';
		require_once DIR_PLUGIN_TESTDATA . '/class-dummy-plugin-upgrader.php';

		$upgrader = new Dummy_Plugin_Upgrader( new Dummy_Upgrader_Skin() );

		set_site_transient(
			'update_plugins',
			(object) array(
				'response' => array(
					'custom-internationalized-plugin/custom-internationalized-plugin.php' => (object) array(
						'package' => 'https://urltozipfile.local',
					),
				),
			)
		);

		$result = $upgrader->bulk_upgrade(
			array(
				'custom-internationalized-plugin/custom-internationalized-plugin.php',
			)
		);

		$this->assertNotFalse( $result );
		$this->assertFileDoesNotExist( WP_LANG_DIR . '/plugins/custom-internationalized-plugin-de_DE.php' );
		$this->assertFileDoesNotExist( WP_PLUGIN_DIR . '/plugins/custom-internationalized-plugin/custom-internationalized-plugin-de_DE.php' );
	}
}
