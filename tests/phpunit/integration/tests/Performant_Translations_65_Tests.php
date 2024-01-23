<?php

/**
 * @coversDefaultClass Performant_Translations_65
 */
class Performant_Translations_65_Tests extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();

		if ( ! class_exists( 'WP_Translation_Controller' ) ) {
			$this->markTestSkipped( 'This test is only relevant on trunk' );
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
		remove_all_filters( 'translation_file_format' );

		remove_all_filters( 'filesystem_method' );

		unload_textdomain( 'wp-tests-domain' );

		switch_theme( 'default' );
	}

	/**
	 * @covers ::init
	 *
	 * @return void
	 */
	public function test_init() {
		$this->assertSame( 10, has_filter( 'load_translation_file', array( Performant_Translations_65::class, 'load_translation_file' ) ) );
	}

	/**
	 * @covers ::load_translation_file
	 *
	 * @return void
	 */
	public function test_load_textdomain_creates_and_reads_php_files_if_filtered_format_is_unsupported() {
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
	 * @covers ::load_translation_file
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
	public function test_load_textdomain_inception_does_not_create_duplicate_files() {
		add_action( 'load_textdomain', array( $this, '_on_load_textdomain' ), 10, 2 );

		// Just to ensure the PHP files exist.
		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );
		unload_textdomain( 'wp-tests-domain' );

		$this->assertFileExists( DIR_TESTDATA . '/pomo/simple.l10n.php' );
		$this->assertFileDoesNotExist( DIR_TESTDATA . '/pomo/simple.l10n.php.php' );
	}


	/**
	 * @covers ::upgrader_process_complete
	 *
	 * @return void
	 */
	public function test_create_translation_files_after_translations_update() {
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
					'language' => 'es_ES',
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
			'translation_file_format',
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
