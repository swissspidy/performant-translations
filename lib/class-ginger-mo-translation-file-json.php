<?php

class Ginger_MO_Translation_File_JSON extends Ginger_MO_Translation_File {
	protected function parse_file() {
		$data = file_get_contents( $this->file );
		if ( '/' == $data[0] ) {
			$data = substr( $data, strpos( $data, '{' ) );
		}
		$data = json_decode( $data, true );

		if ( ! $data || ! is_array( $data ) ) {
			$this->error = json_last_error_msg() ? ( 'JSON Error: ' . json_last_error_msg() ) : true;
			return;
		}

		// Support JED JSON files which wrap po2json
		if ( isset( $data['domain'] ) && isset( $data['locale_data'][ $data['domain'] ] ) ) {
			$data = $data['locale_data'][ $data['domain'] ];
		}

		if ( isset( $data[''] ) ) {
			$this->headers = array_change_key_case( $data[''], CASE_LOWER );
			unset( $data[''] );
		}

		foreach ( $data as $key => $item ) {
			if ( ! is_array( $item ) ) {
				// Straight Key => Value translations
				$this->entries[ $key ] = $item;
			} else {
				if ( null === $item[0] ) {
					// Singular - po2json format
					$this->entries[ $key ] = $item[1];
				} elseif ( false !== strpos( $key, "\0" ) ) {
					// Singular - Straight Key (plural\0plural) => [ plural, plural ] format
					$this->entries[ $key ] = $item;
				} else {
					// Plurals - po2json format ( plural0 => [ plural1, translation0, translation1 ] )
					$key .= "\0" . $item[0];
					$this->entries[ $key ] = array_slice( $item, 1 );
				}
			}
		}

		$this->parsed = true;
	}

	protected function create_file( $headers, $entries ) {
		// json headers are lowercase
		$headers = array_change_key_case( $headers );
		// Prefix as the first key.
		$entries = array_merge( array( '' => $headers ), $entries );

		if ( defined( 'JSON_PRETTY_PRINT' ) ) {
			$json = json_encode( (array) $entries, JSON_PRETTY_PRINT );
		} else {
			$json = json_encode( (array) $entries );
		}

		return (bool) file_put_contents( $this->file, $json );
	}
}

