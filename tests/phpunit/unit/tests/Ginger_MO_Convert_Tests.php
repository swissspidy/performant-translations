<?php

class Ginger_MO_Convert_Tests extends Ginger_MO_TestCase {
	/**
	 * @dataProvider data_export_matrix
	 *
	 * @param string $source_file
	 * @param string $destination_format
	 * @return void
	 *
	 * @phpstan-param 'mo'|'php' $destination_format
	 */
	public function test_convert_format( string $source_file, string $destination_format ) {
		$destination_file = $this->temp_file();

		$this->assertNotFalse( $destination_file );

		$source = Ginger_MO_Translation_File::create( $source_file );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $source );

		$contents = Ginger_MO_Translation_File::transform( $source_file, $destination_format );

		$this->assertNotFalse( $contents );

		file_put_contents( $destination_file, $contents );

		$destination = Ginger_MO_Translation_File::create( $destination_file, $destination_format );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $destination );
		$this->assertFalse( $destination->error() );

		$this->assertTrue( filesize( $destination_file ) > 0 );

		$destination_read = Ginger_MO_Translation_File::create( $destination_file, $destination_format );

		$this->assertInstanceOf( Ginger_MO_Translation_File::class, $destination_read );
		$this->assertFalse( $destination_read->error() );

		$source_headers      = $source->headers();
		$destination_headers = $destination_read->headers();

		$this->assertEquals( $source_headers, $destination_headers );

		foreach ( $source->entries() as $original => $translation ) {
			// Verify the translation is in the destination file
			$new_translation = $destination_read->translate( $original );
			$this->assertSame( $translation, $new_translation );
		}
	}

	/**
	 * @return array<array{0:string, 1: 'mo'|'php'}>
	 */
	public function data_export_matrix(): array {
		$formats = array( 'mo', 'php' );

		$matrix = array();

		foreach ( $formats as $input_format ) {
			foreach ( $formats as $output_format ) {
				$matrix[ "$input_format to $output_format" ] = array( GINGER_MO_TEST_DATA . 'example-simple.' . $input_format, $output_format );
			}
		}

		return $matrix;
	}

	/**
	 * @covers Ginger_MO_Translation_File::transform
	 *
	 * @return void
	 */
	public function test_convert_format_invalid_source() {
		$this->assertFalse( Ginger_MO_Translation_File::transform( 'this-file-does-not-exist', 'invalid' ) );
		$this->assertFalse( Ginger_MO_Translation_File::transform( GINGER_MO_TEST_DATA . 'example-simple.mo', 'invalid' ) );
		$this->assertNotFalse( Ginger_MO_Translation_File::transform( GINGER_MO_TEST_DATA . 'example-simple.mo', 'php' ) );
	}
}
