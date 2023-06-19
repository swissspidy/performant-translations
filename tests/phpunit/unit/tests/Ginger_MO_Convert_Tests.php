<?php

class Ginger_MO_Convert_Tests extends Ginger_MO_TestCase {


	/**
	 * @dataProvider dataprovider_export_matrix
	 */
	public function test_convert_format( $source_file, $destination_format ) {
		$destination_file = $this->temp_file();
		$source           = Ginger_MO_Translation_File::create( $source_file, 'read' );
		$destination      = Ginger_MO_Translation_File::create( $destination_file, 'write', $destination_format );

		$this->assertFalse( $source->error() );
		$this->assertFalse( $destination->error() );

		$this->assertTrue( $source->export( $destination ) );

		$this->assertFalse( $source->error() );
		$this->assertFalse( $destination->error() );

		$this->assertTrue( filesize( $destination_file ) > 0 );

		$destination_read = Ginger_MO_Translation_File::create( $destination_file, 'read', $destination_format );

		$this->assertFalse( $destination_read->error() );

		$source_headers      = $source->headers();
		$destination_headers = $destination_read->headers();
		unset( $destination_headers['x-converter'] );
		// We add this.

		$this->assertEquals( $source_headers, $destination_headers );

		foreach ( $source->entries() as $original => $translation ) {
			// Verify the translation is in the destination file
			if ( false !== strpos( $original, "\0" ) ) {
				// Plurals:
				$translation     = is_array( $translation ) ? implode( "\0", $translation ) : $translation;
				$new_translation = $destination_read->translate( $original );
				$new_translation = is_array( $new_translation ) ? implode( "\0", $new_translation ) : $new_translation;

				$this->assertSame( $translation, $new_translation );

			} else {
				// Single
				$new_translation = $destination_read->translate( $original );

				$this->assertSame( $translation, $new_translation );
			}
		}
	}

	public function dataprovider_export_matrix() {
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
