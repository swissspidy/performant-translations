<?php

/**
 * @coversDefaultClass Ginger_MO_Translation_Compat
 */
class Ginger_MO_Translation_Compat_Tests extends WP_UnitTestCase {
	public function tear_down() {
		if ( file_exists( DIR_TESTDATA . '/pomo/simple.php' ) ) {
			$this->unlink( DIR_TESTDATA . '/pomo/simple.php' );
		}
	}

	/**
	 * @covers ::overwrite_wordpress
	 */
	public function test_overwrite_wordpress() {
		$this->assertSame( 100, has_filter( 'override_load_textdomain', array( Ginger_MO_Translation_Compat::class, 'load_textdomain' ) ) );
		$this->assertSame( 100, has_filter( 'override_unload_textdomain', array( Ginger_MO_Translation_Compat::class, 'unload_textdomain' ) ) );
	}

	/**
	 * @covers ::load_textdomain
	 */
	public function test_load_textdomain() {
		global $l10n;

		$loaded_before_load = is_textdomain_loaded( 'wp-tests-domain' );

		$load_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$loaded_after_load = is_textdomain_loaded( 'wp-tests-domain' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

		$is_loaded = Ginger_MO::instance()->is_loaded( 'wp-tests-domain' );
		$headers   = Ginger_MO::instance()->get_headers( 'wp-tests-domain' );
		$entries   = Ginger_MO::instance()->get_entries( 'wp-tests-domain' );

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$loaded_after_unload = is_textdomain_loaded( 'wp-tests-domain' );

		$this->assertFalse( $loaded_before_load, 'Text domain was already loaded at beginning of the test' );
		$this->assertTrue( $load_successful, 'Text domain not successfully loaded' );
		$this->assertTrue( $loaded_after_load, 'Text domain is not considered loaded' );
		$this->assertInstanceOf( Ginger_MO_Translation_Compat_Provider::class, $compat_instance, 'No compat provider instance used' );
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
	 */
	public function test_load_textdomain_creates_and_reads_php_files() {
		$load_mo_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_mo_successful = unload_textdomain( 'wp-tests-domain' );

		$file_exists = file_exists( DIR_TESTDATA . '/pomo/simple.php' );

		$load_php_successful = load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.php' );

		$unload_php_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertTrue( $load_mo_successful, 'MO file not successfully loaded' );
		$this->assertTrue( $unload_mo_successful );
		$this->assertTrue( $file_exists );
		$this->assertTrue( $load_php_successful, 'PHP file not successfully loaded' );
		$this->assertTrue( $unload_php_successful );
	}


	/**
	 * @covers ::unload_textdomain
	 */
	public function test_unload_textdomain() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$loaded_after_unload = is_textdomain_loaded( 'wp-tests-domain' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

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
}
