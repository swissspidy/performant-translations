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

		if ( isset( $result['messages'] ) && is_array( $result['messages'] ) ) {
			// $this->entries = $result['messages'];
			foreach ( $result['messages'] as $singular => $translations ) {
				$this->entries[ $singular ] = is_array( $translations ) ? implode( "\0", $translations ) : $translations;
			}
			unset( $result['messages'] );
		}

		$this->headers = array_change_key_case( $result );
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
		$data = array_merge( $headers, array( 'messages' => $entries ) );

		$file_contents = '<?php' . PHP_EOL . 'return ' . $this->var_export( $data, true ) . ';' . PHP_EOL;

		return (bool) file_put_contents( $this->file, $file_contents );
	}

	/**
	 * Determines if the given array is a list.
	 *
	 * An array is considered a list if its keys consist of consecutive numbers from 0 to count($array)-1.
	 *
	 * Polyfill for array_is_list() in PHP 8.1.
	 *
	 * @see https://github.com/symfony/polyfill-php81/tree/main
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array<mixed> $arr The array being evaluated.
	 * @return bool True if array is a list, false otherwise.
	 */
	private function array_is_list( $arr ) {
		if ( function_exists( 'array_is_list' ) ) {
			return array_is_list( $arr );
		}

		if ( ( array() === $arr ) || ( array_values( $arr ) === $arr ) ) {
			return true;
		}

		$next_key = -1;

		foreach ( $arr as $k => $v ) {
			if ( ++$next_key !== $k ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Outputs or returns a parsable string representation of a variable.
	 *
	 * Like {@see var_export()} but "minified", using short array syntax
	 * and no newlines.
	 *
	 * @since 0.0.1
	 *
	 * @param mixed $value       The variable you want to export.
	 * @param bool  $return_only Optional. Whether to return the variable representation instead of outputting it. Default false.
	 * @return string|void The variable representation or void.
	 * @phpstan-return ($return_only is true ? string : void|null)
	 */
	private function var_export( $value, $return_only = false ) {
		if ( ! is_array( $value ) ) {
			return var_export( $value, $return_only );
		}

		$entries = array();

		$is_list = $this->array_is_list( $value );

		foreach ( $value as $key => $val ) {
			$entries[] = $is_list ? $this->var_export( $val, true ) : var_export( $key, true ) . '=>' . $this->var_export( $val, true );
		}

		$code = '[' . implode( ',', $entries ) . ']';
		if ( $return_only ) {
			return $code;
		}

		echo $code;
	}
}
