<?php

namespace tests;

use Ginger_MO;
use Ginger_MO_Translation_File;
use includes\Ginger_MO_TestCase;

class Ginger_MO_Tests extends Ginger_MO_TestCase {


	function test_no_files_loaded_returns_false() {
		$instance = new Ginger_MO();
		$this->assertFalse( $instance->translate( 'singular' ) );
		$this->assertFalse( $instance->translate_plural( array( 'plural0', 'plural1' ), 1 ) );
	}

	function test_unload_entire_textdomain() {
		$instance = new Ginger_MO();
		$this->assertFalse( $instance->is_loaded( 'unittest' ) );
		$this->assertTrue( $instance->load( GINGER_MO_TEST_DATA . 'example-simple.php', 'unittest' ) );
		$this->assertTrue( $instance->is_loaded( 'unittest' ) );

		$this->assertSame( 'translation', $instance->translate( 'original', null, 'unittest' ) );

		$this->assertTrue( $instance->unload( 'unittest' ) );
		$this->assertFalse( $instance->is_loaded( 'unittest' ) );
		$this->assertFalse( $instance->translate( 'original', null, 'unittest' ) );
	}

	/**
	 * @dataProvider dataprovider_invalid_files
	 */
	function test_invalid_files( $type, $file_contents, $expected_error = null ) {
		$file = $this->temp_file( $file_contents );

		$instance = Ginger_MO_Translation_File::create( $file, 'read', $type );

		// Not an error condition until it attempts to parse the file.
		$this->assertFalse( $instance->error() );

		// Trigger parsing.
		$instance->headers();

		$this->assertNotFalse( $instance->error() );

		if ( $expected_error ) {
			$this->assertSame( $expected_error, $instance->error() );
		}
	}

	function dataprovider_invalid_files() {
		return array(
			// filetype, file ( contents ) [, expected error string ]
			array( 'php', '' ),
			array( 'php', '<?php // This is a php file without a payload' ),
			array( 'json', '' ),
			array( 'json', 'Random data in a file' ),
			array( 'mo', '', 'Invalid Data.' ),
			array( 'mo', 'Random data in a file long enough to be a real header', "Magic Marker doesn't exist" ),
			array( 'mo', pack( 'V*', 0x950412de ), 'Invalid Data.' ),
			array( 'mo', pack( 'V*', 0x950412de ) . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'Unsupported Revision.' ),
			array( 'mo', pack( 'V*', 0x950412de, 0x0 ) . 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', 'Invalid Data.' ),
		);
	}

	function test_non_existent_file() {
		$instance = new Ginger_MO();

		$this->assertFalse( $instance->load( GINGER_MO_TEST_DATA . 'file-that-doesnt-exist.mo', 'unittest' ) );
		$this->assertFalse( $instance->is_loaded( 'unittest' ) );
	}

	/**
	 * @dataProvider dataprovider_simple_example_files
	 */
	function test_simple_translation_files( $file ) {
		$ginger_mo = new Ginger_MO();
		$this->assertTrue( $ginger_mo->load( GINGER_MO_TEST_DATA . $file, 'unittest' ) );

		$this->assertTrue( $ginger_mo->is_loaded( 'unittest' ) );
		$this->assertFalse( $ginger_mo->is_loaded( 'textdomain not loaded' ) );

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

	function dataprovider_simple_example_files() {
		return array(
			array( 'example-simple.json' ),
			array( 'example-simple-jed.json' ),
			array( 'example-simple-po2json.json' ),
			array( 'example-simple.mo' ),
			array( 'example-simple.php' ),
		);
	}
}
