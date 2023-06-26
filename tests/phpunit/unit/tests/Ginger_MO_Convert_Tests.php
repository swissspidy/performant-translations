<?php

class Ginger_MO_Convert_Tests extends Ginger_MO_TestCase {


	/**
	 * @dataProvider data_export_matrix
	 *
	 * @param string $source_file
	 * @param string $destination_format
	 * @return void
	 */
	public function test_convert_format( $source_file, $destination_format ) {
		$destination_file = $this->temp_file();

		$this->assertNotFalse( $destination_file );

		$source      = Ginger_MO_Translation_File::create( $source_file, 'read' );
		$destination = Ginger_MO_Translation_File::create( $destination_file, 'write', $destination_format );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $source );
		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $destination );
		$this->assertFalse( $source->error() );
		$this->assertFalse( $destination->error() );

		$this->assertTrue( $source->export( $destination ) );

		$this->assertFalse( $source->error() );
		$this->assertFalse( $destination->error() );

		$this->assertTrue( filesize( $destination_file ) > 0 );

		$destination_read = Ginger_MO_Translation_File::create( $destination_file, 'read', $destination_format );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $destination_read );
		$this->assertFalse( $destination_read->error() );

		$source_headers      = $source->headers();
		$destination_headers = $destination_read->headers();

		$this->assertEquals( $source_headers, $destination_headers );

		foreach ( $source->entries() as $original => $translation ) {
			// Verify the translation is in the destination file
			if ( false !== strpos( $original, "\0" ) ) {
				// Plurals:
				$new_translation = $destination_read->translate( $original );

				$this->assertSame( $translation, $new_translation );

			} else {
				// Single
				$new_translation = $destination_read->translate( $original );

				$this->assertSame( $translation, $new_translation );
			}
		}
	}

	/**
	 * @return array<array{0:string, 1: string}>
	 */
	public function data_export_matrix() {
		$sources = array(
			'example-simple.json',
			'example-simple.mo',
			'example-simple.php',
		);
		$outputs = array( 'mo', 'json', 'php' );

		$matrix = array();
		foreach ( $sources as $s ) {
			foreach ( $outputs as $output_format ) {
				$matrix[] = array( GINGER_MO_TEST_DATA . $s, $output_format );
			}
		}

		return $matrix;
	}
}
