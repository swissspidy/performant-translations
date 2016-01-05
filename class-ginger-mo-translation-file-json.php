<?php

class Ginger_MO_Translation_File_JSON extends Ginger_MO_Translation_File {
	protected function parse_file() {
		$data = json_decode( file_get_contents( $this->file ), true );

		if ( ! $data || ! is_array( $data ) ) {
			$this->error = true;
			return;
		}

		if ( isset( $data[''] ) ) {
			$this->headers = array_change_key_case( $data[''], CASE_LOWER );
			unset( $data[''] );
		}

		// Support JED JSON files
		if ( isset( $data['locale_data']['messages'] ) ) {
			$data = $data['locale_data']['messages'];
		}

		foreach ( $data as $key => $item ) {
			if ( ! is_array( $item ) ) {
				// Straight Key => Value translations
				$this->entries[ $key ] = $item;
			} else {
				// Po2json format
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

