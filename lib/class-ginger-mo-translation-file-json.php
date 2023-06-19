<?php
/**
 * Class Ginger_MO_Translation_File_JSON.
 *
 * @package Ginger_MO
 */

/**
 * Class Ginger_MO_Translation_File_JSON.
 */
class Ginger_MO_Translation_File_JSON extends Ginger_MO_Translation_File {
	/**
	 * Parses the file.
	 *
	 * @return void
	 */
	protected function parse_file() {
		$data = file_get_contents( $this->file );

		if ( ! $data ) {
			$this->error = true;
			return;
		}

		$data = json_decode( $data, true );

		if ( ! $data || ! is_array( $data ) ) {
			$this->error = true;
			if ( function_exists( 'json_last_error_msg' ) ) {
				$this->error = 'JSON Error: ' . json_last_error_msg();
			} elseif ( function_exists( 'json_last_error' ) ) {
				$this->error = 'JSON Error code: ' . json_last_error();
			}
			return;
		}

		// Support JED JSON files which wrap po2json.
		if ( isset( $data['domain'] ) && isset( $data['locale_data'][ $data['domain'] ] ) ) {
			$data = $data['locale_data'][ $data['domain'] ];
		}

		if ( isset( $data[''] ) ) {
			$this->headers = array_change_key_case( $data[''] );
			unset( $data[''] );
		}

		foreach ( $data as $key => $item ) {
			if ( is_string( $item ) ) {
				$this->entries[ (string) $key ] = $item;
			} elseif ( is_array( $item ) ) {
				$this->entries[ (string) $key ] = implode( "\0", $item );
			}
		}

		$this->parsed = true;
	}

	/**
	 * Writes translations to file.
	 *
	 * @param array<string, string> $headers Headers.
	 * @param string[]              $entries Entries.
	 * @return bool True on success, false otherwise.
	 */
	protected function create_file( $headers, $entries ) {
		// json headers are lowercase.
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
