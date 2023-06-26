<?php

class Ginger_MO_Tests extends Ginger_MO_TestCase {
	/**
	 * @covers Ginger_MO::instance
	 *
	 * @return void
	 */
	public function test_get_instance() {
		$instance  = Ginger_MO::instance();
		$instance2 = Ginger_MO::instance();

		$this->assertInstanceOf( Ginger_MO::class, $instance );
		$this->assertInstanceOf( Ginger_MO::class, $instance2 );
		$this->assertSame( $instance, $instance2 );
	}

	/**
	 * @return void
	 */
	public function test_no_files_loaded_returns_false() {
		$instance = new Ginger_MO();
		$this->assertFalse( $instance->translate( 'singular' ) );
		$this->assertFalse( $instance->translate_plural( array( 'plural0', 'plural1' ), 1 ) );
	}

	/**
	 * @covers Ginger_MO::unload()
	 *
	 * @return void
	 */
	public function test_unload_not_loaded() {
		$instance = new Ginger_MO();
		$this->assertFalse( $instance->is_loaded( 'unittest' ) );
		$this->assertFalse( $instance->unload( 'unittest' ) );
	}

	/**
	 * @covers Ginger_MO::unload()
	 *
	 * @return void
	 */
	public function test_unload_entire_textdomain() {
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
	 * @dataProvider data_invalid_files
	 *
	 * @param string $type
	 * @param string $file_contents
	 * @param string|bool $expected_error
	 * @return void
	 */
	public function test_invalid_files( $type, $file_contents, $expected_error = null ) {
		$file = $this->temp_file( $file_contents );

		$this->assertNotFalse( $file );

		$instance = Ginger_MO_Translation_File::create( $file, 'read', $type );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $instance );

		// Not an error condition until it attempts to parse the file.
		$this->assertFalse( $instance->error() );

		// Trigger parsing.
		$instance->headers();

		$this->assertNotFalse( $instance->error() );

		if ( $expected_error ) {
			$this->assertSame( $expected_error, $instance->error() );
		}
	}

	/**
	 * @return array{0: array{0: string, 1: string|false, 2?: string}}
	 */
	public function data_invalid_files() {
		return array(
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

	/**
	 * @return void
	 */
	public function test_non_existent_file() {
		$instance = new Ginger_MO();

		$this->assertFalse( $instance->load( GINGER_MO_TEST_DATA . 'file-that-doesnt-exist.mo', 'unittest' ) );
		$this->assertFalse( $instance->is_loaded( 'unittest' ) );
	}

	/**
	 * @dataProvider data_simple_example_files
	 *
	 * @param string $file
	 * @return void
	 */
	public function test_simple_translation_files( $file ) {
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

	/**
	 * @return array<array{0: string}>
	 */
	public function data_simple_example_files() {
		return array(
			array( 'example-simple.json' ),
			array( 'example-simple.mo' ),
			array( 'example-simple.php' ),
		);
	}
}
