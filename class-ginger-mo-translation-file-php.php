<?php

class Ginger_MO_Translation_File_PHP extends Ginger_MO_Translation_File {
	protected function parse_file() {
		$result = include( $this->file );
		if ( ! $result || ! is_array( $result ) ) {
			$this->error = true;
			return;
		}

		foreach ( array( 'headers', 'entries', 'plural_form_function' ) as $field ) {
			if ( isset( $result[ $field ] ) ) {
				$this->$field = $result[ $field ];
			}
		}

		$this->headers = array_change_key_case( $this->headers, CASE_LOWER );
		$this->parsed = true;
	}
}
