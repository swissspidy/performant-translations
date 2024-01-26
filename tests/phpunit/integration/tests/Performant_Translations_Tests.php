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
		add_filter( 'wp_opcache_invalidate_file', array( $filter, 'filter' ) );

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
