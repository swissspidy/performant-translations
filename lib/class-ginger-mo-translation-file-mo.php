<?php

class Ginger_MO_Translation_File_MO extends Ginger_MO_Translation_File {
	// used for unpack(), little endian = V, big endian = N
	protected $uint32 = false;
	protected $use_mb_substr = false;
	protected $file_contents = null;

	protected function __construct( $file ) {
		parent::__construct( $file );
		$this->use_mb_substr = function_exists('mb_substr') && ( (ini_get( 'mbstring.func_overload' ) & 2) != 0 );
	}

	protected function detect_endian_and_validate_file() {
		$magic_marker = 0x950412de;

		$header = $this->read( 0, 4 );

		$big = unpack( 'N', $header );
		$big = reset( $big );
		$little = unpack( 'V', $header );
		$little = reset( $little );

		if ( $big === $magic_marker ) {
			return 'N';
		} elseif ( $little === $magic_marker ) {
			return 'V';
		} else {
			$this->error = "Magic Marker doesn't exist";
			return false;
		}
	}

	protected function read( $from, $bytes ) {
		if ( $this->use_mb_substr ) {
			return mb_substr( $this->file_contents, $from, $bytes, '8bit' );
		} else {
			return substr( $this->file_contents, $from, $bytes );
		}
	}

	protected function parse_file() {
		$this->parsed = true;

		$this->file_contents = file_get_contents( $this->file );

		$this->uint32 = $this->detect_endian_and_validate_file( $this->read( 0, 4 ) );
		if ( ! $this->uint32 ) {
			return false;
		}

		$offsets = $this->read( 4, 24 );
		if ( ! $offsets ) {
			return false;
		}

		$offsets = unpack( "{$this->uint32}rev/{$this->uint32}total/{$this->uint32}originals_addr/{$this->uint32}translations_addr/{$this->uint32}hash_length/{$this->uint32}hash_addr", $offsets );

		$offsets['originals_length'] = $offsets['translations_addr'] - $offsets['originals_addr'];
		$offsets['translations_length'] = $offsets['hash_addr'] - $offsets['translations_addr'];

		// Load the Originals
		$original_data = str_split( $this->read( $offsets['originals_addr'], $offsets['originals_length'] ), 8 );
		$translations_data = str_split( $this->read( $offsets['translations_addr'], $offsets['translations_length'] ), 8 );

		foreach ( array_keys( $original_data ) as $i ) {
			$o = unpack( "{$this->uint32}length/{$this->uint32}pos", $original_data[ $i ] );
			$t = unpack( "{$this->uint32}length/{$this->uint32}pos", $translations_data[ $i ] );

			$original = $this->read( $o['pos'], $o['length'] );
			$translation = $this->read( $t['pos'], $t['length'] );
			$translation = rtrim( $translation, "\0" ); // GlotPress bug

			// Metadata about the MO file is stored in the first translation entry.
			if ( '' === $original ) {
				foreach ( explode( "\n", $translation ) as $meta_line ) {
					if ( ! $meta_line ) continue;
					list( $name, $value ) = array_map( 'trim', explode( ':', $meta_line, 2 ) );
					$this->headers[ strtolower( $name ) ] = $value;
				}
			} else {
				$this->entries[ $original ] = $translation;
			}
		}

		unset( $this->file_contents, $original_data, $translations_data );

		return true;
	}

}
