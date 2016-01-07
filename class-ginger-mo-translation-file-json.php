<?php

class Ginger_MO_Translation_File_JSON extends Ginger_MO_Translation_File {
	protected function parse_file() {
		$data = json_decode( file_get_contents( $this->file ), true );

		if ( ! $data || ! is_array( $data ) ) {
			$this->error = true;
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
				// po2json format
				if ( null !== $item[0] ) {
					// Plurals
					$key .= "\0" . $item[0];
					$this->entries[ $key ] = array_slice( $item, 1 );
				} else {
					// Singular
					$this->entries[ $key ] = $item[1];
				}
			}
		}

		$this->parsed = true;
	}
}

