<?php

/**
 * @coversDefaultClass Ginger_MO_Translation_Compat_Provider
 */
class Ginger_MO_Translation_Compat_Provider_Tests extends WP_UnitTestCase {
	/**
	 * @covers ::__get
	 * @covers ::make_entry
	 */
	public function test_get_entries() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

		$entries = $compat_instance ? $compat_instance->entries : array();

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertInstanceOf( Ginger_MO_Translation_Compat_Provider::class, $compat_instance, 'No compat provider instance used' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
		$this->assertEqualSets(
			array(
				new Translation_Entry(
					array(
						'singular'     => 'baba',
						'translations' => array( 'dyado' ),
					)
				),
				new Translation_Entry(
					array(
						'singular'     => "kuku\nruku",
						'translations' => array( 'yes' ),
					)
				),
			),
			$entries,
			'Actual translation entries do not match expected ones'
		);
	}

	/**
	 * @covers ::__get
	 */
	public function test_get_headers() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

		$headers = $compat_instance ? $compat_instance->headers : array();

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertInstanceOf( Ginger_MO_Translation_Compat_Provider::class, $compat_instance, 'No compat provider instance used' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
		$this->assertEqualSetsWithIndex(
			array(
				'Project-Id-Version'   => 'WordPress 2.6-bleeding',
				'Report-Msgid-Bugs-To' => 'wp-polyglots@lists.automattic.com',
			),
			$headers,
			'Actual translation headers do not match expected ones'
		);
	}

	/**
	 * @covers ::translate
	 */
	public function test_translate() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/simple.mo' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

		$translation = $compat_instance ? $compat_instance->translate( 'baba' ) : false;

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertInstanceOf( Ginger_MO_Translation_Compat_Provider::class, $compat_instance, 'No compat provider instance used' );
		$this->assertSame( 'dyado', $translation, 'Actual translation does not match expected one' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
	}

	/**
	 * @covers ::translate_plural
	 */
	public function test_translate_plural() {
		global $l10n;

		load_textdomain( 'wp-tests-domain', DIR_TESTDATA . '/pomo/plural.mo' );

		$compat_instance = isset( $l10n['wp-tests-domain'] ) ? $l10n['wp-tests-domain'] : null;

		$translation_1       = $compat_instance ? $compat_instance->translate_plural( 'one dragon', '%d dragons', 1 ) : false;
		$translation_2       = $compat_instance ? $compat_instance->translate_plural( 'one dragon', '%d dragons', 2 ) : false;
		$translation_minus_8 = $compat_instance ? $compat_instance->translate_plural( 'one dragon', '%d dragons', -8 ) : false;

		$unload_successful = unload_textdomain( 'wp-tests-domain' );

		$this->assertInstanceOf( Ginger_MO_Translation_Compat_Provider::class, $compat_instance, 'No compat provider instance used' );
		$this->assertSame( 'oney dragoney', $translation_1, 'Actual translation does not match expected one' );
		$this->assertSame( 'twoey dragoney', $translation_2, 'Actual translation does not match expected one' );
		$this->assertSame( 'twoey dragoney', $translation_minus_8, 'Actual translation does not match expected one' );
		$this->assertTrue( $unload_successful, 'Text domain not successfully unloaded' );
	}
}
