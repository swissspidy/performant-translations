<?php
/**
 * Class Ginger_MO_Translation_File_PHP.
 *
 * @package Ginger_MO
 */

/**
 * Class Ginger_MO_Translation_File_PHP.
 */
class Ginger_MO_Translation_File_PHP extends Ginger_MO_Translation_File {
	/**
	 * Parses the file.
	 *
	 * @return void
	 */
	protected function parse_file() {
		$result = include $this->file;
		if ( ! $result || ! is_array( $result ) ) {
			$this->error = true;
			return;
		}

		foreach ( array( 'headers', 'entries' ) as $field ) {
			if ( isset( $result[ $field ] ) ) {
				$this->$field = $result[ $field ];
			}
		}

		$this->headers = array_change_key_case( $this->headers, CASE_LOWER );
		$this->parsed  = true;
	}

	/**
	 * Writes translations to file.
	 *
	 * @param array<string, string> $headers Headers.
	 * @param string[]              $entries Entries.
	 * @return bool True on success, false otherwise.
	 */
	protected function create_file( $headers, $entries ) {
		$file_contents = '<' . "?php\n";
		if ( ! empty( $headers['x-converter'] ) ) {
			$file_contents .= "// {$headers['x-converter']}.\n";
		}

		$file_contents .= 'return ' . var_export( compact( 'headers', 'entries' ), true ) . ';';

		return (bool) file_put_contents( $this->file, $file_contents );
	}
}
