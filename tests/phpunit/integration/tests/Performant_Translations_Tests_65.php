<?php

/**
 * @coversDefaultClass Performant_Translations
 */
class Performant_Translations_Tests_65 extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();

		if ( ! class_exists( 'WP_Translations' ) ) {
			$this->markTestSkipped( 'This test is only relevant on WP >= 6.5' );
		}
	}

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
		$this->assertSame( 100, has_filter( 'load_translation_file', array( Performant_Translations::class, 'load_translation_file' ) ) );

		$this->assertSame( 10, has_action( 'wp_head', array( Performant_Translations::class, 'add_generator_tag' ) ) );
		$this->assertSame( 10, has_action( 'performant_translations_file_written', array( Performant_Translations::class, 'opcache_invalidate' ) ) );
		$this->assertSame( 10, has_action( 'upgrader_process_complete', array( Performant_Translations::class, 'upgrader_process_complete' ) ) );
		$this->assertSame( 10, has_action( 'loco_file_written', array( Performant_Translations::class, 'regenerate_translation_file' ) ) );
		$this->assertSame( 10, has_action( 'wpml_st_translation_file_updated', array( Performant_Translations::class, 'regenerate_translation_file' ) ) );
	}

	/**
	 * @covers ::load_translation_file
	 *
	 * @return void
	 */
	public function test_load_translation_file_creates_and_reads_php_files_if_filtered_format_is_unsupported() {
		add_filter(
			'translation_file_format',
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
	 * @covers ::load_translation_file
	 *
	 * @return void
	 */
	public function test_load_translation_file_creates_and_reads_php_files_no_wp_filesystem() {
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
	 * @covers ::load_translation_file
	 *
	 * @return void
	 */
	public function test_load_translation_file_does_not_create_php_files_if_disabled() {
		add_filter( 'performant_translations_convert_files', '__return_false' );

		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php' );
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
	 * @covers ::load_translation_file
	 *
	 * @return void
	 */
	public function test_load_translation_file_inception_does_not_create_duplicate_files() {
		add_action( 'load_textdomain', array( $this, '_on_load_textdomain' ), 10, 2 );

		// Just to ensure the PHP files exist.
		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );
		unload_textdomain( 'wp-tests-domain' );

		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php.php' );
	}
}
