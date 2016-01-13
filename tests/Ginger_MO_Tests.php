<?php
class Ginger_MO_Tests extends PHPUnit_Framework_TestCase {
	function testWorks() {
		$this->assertTrue( true );
	}

	function test_no_files_loaded_returns_false() {
		$this->assertFalse( Ginger_MO::instance()->translate( "singular" ) );
		$this->assertFalse( ginger_MO::instance()->translate_plural( array( "plural0", "plural1" ), 1 ) );
	}

	function _test_load_simple_json_file() {
		// File file doesn't specufy a text domain, it's just English plural rules apply
		$instance = new Ginger_MO;
		$this->assertTrue( $instance->load( GINGER_MO_TEST_DATA . 'example-simple.json', 'unittest' ) );

		$this->_test_simple_translation_files( $instance );
	}

	function test_load_simple_jed_json_file() {
		$instance = new Ginger_MO;
		// File file doesn't specufy a text domain, it's just English plural rules apply
		$this->assertTrue( $instance->load( GINGER_MO_TEST_DATA . 'example-simple-jed.json', 'unittest' ) );

		$this->_test_simple_translation_files( $instance );
	}

	function test_load_simple_po2json_file() {
		$instance = new Ginger_MO;
		// File file doesn't specufy a text domain, it's just English plural rules apply
		$this->assertTrue( $instance->load( GINGER_MO_TEST_DATA . 'example-simple-po2json.json', 'unittest' ) );

		$this->_test_simple_translation_files( $instance );
	}

	function test_load_simple_php_file() {
		$instance = new Ginger_MO;
		// File file doesn't specufy a text domain, it's just English plural rules apply
		$this->assertTrue( $instance->load( GINGER_MO_TEST_DATA . 'example-simple.php', 'unittest' ) );

		$this->_test_simple_translation_files( $instance );
	}

	function _test_simple_translation_files( $ginger_mo ) {
		$this->assertFalse( $ginger_mo->translate( "string that doesn't exist", null, 'unittest' ) );
		$this->assertFalse( $ginger_mo->translate( 'original', null, 'textdomain not loaded' ) );

		$this->assertSame( 'translation', $ginger_mo->translate( 'original', null, 'unittest' ) );
		$this->assertSame( 'translation with context', $ginger_mo->translate( 'original with context', 'context', 'unittest' ) );

		$this->assertSame( 'translation1', $ginger_mo->translate_plural( array( 'plural0', 'plural1' ), 0, null, 'unittest' ) );
		$this->assertSame( 'translation0', $ginger_mo->translate_plural( array( 'plural0', 'plural1' ), 1, null, 'unittest' ) );
		$this->assertSame( 'translation1', $ginger_mo->translate_plural( array( 'plural0', 'plural1' ), 2, null, 'unittest' ) );

		$this->assertSame( 'translation1 with context', $ginger_mo->translate_plural( array( 'plural0 with context', 'plural1 with context' ), 0, 'context', 'unittest' ) );
		$this->assertSame( 'translation0 with context', $ginger_mo->translate_plural( array( 'plural0 with context', 'plural1 with context' ), 1, 'context', 'unittest' ) );
		$this->assertSame( 'translation1 with context', $ginger_mo->translate_plural( array( 'plural0 with context', 'plural1 with context' ), 2, 'context', 'unittest' ) );
	}

}
