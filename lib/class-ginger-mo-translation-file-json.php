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
			$this->error = json_last_error_msg();
			return;
		}

		if ( ! isset( $data['domain'] ) | ! isset( $data['locale_data'][ $data['domain'] ] ) ) {
			$this->error = true;
			return;
		}

		if ( isset( $data['translation-revision-date'] ) ) {
			$this->headers['po-revision-date'] = $data['translation-revision-date'];
		}

		$entries = $data['locale_data'][ $data['domain'] ];

		foreach ( $entries as $key => $item ) {
			if ( '' === $key ) {
				$headers = array_change_key_case( $item );
				if ( isset( $headers['lang'] ) ) {
					$this->headers['language'] = $headers['lang'];
					unset( $headers['lang'] );
				}

				$this->headers = array_merge(
					$this->headers,
					$headers
				);
				continue;
			}

			if ( is_string( $item ) ) {
				$this->entries[ (string) $key ] = $item;
			} elseif ( is_array( $item ) ) {
				$this->entries[ (string) $key ] = implode( "\0", $item );
			}
		}

		unset( $this->headers['domain'] );

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
		$headers = array_change_key_case( $headers );

		$domain = isset( $headers['domain'] ) ? $headers['domain'] : 'messages';

		$data = array(
			'domain'      => $domain,
			'locale_data' => array(
				$domain => $entries,
			),
		);

		if ( isset( $headers['po-revision-date'] ) ) {
			$data['translation-revision-date'] = $headers['po-revision-date'];
		}

		if ( isset( $headers['x-generator'] ) ) {
			$data['generator'] = $headers['x-generator'];
		}

		$data['locale_data'][ $domain ][''] = array(
			'domain' => $domain,
		);

		if ( isset( $headers['plural-forms'] ) ) {
			$data['locale_data'][ $domain ]['']['plural-forms'] = $headers['plural-forms'];
		}

		if ( isset( $headers['language'] ) ) {
			$data['locale_data'][ $domain ]['']['lang'] = $headers['language'];
		}

		$json = json_encode( $data, JSON_PRETTY_PRINT );

		if ( ! $json ) {
			return false;
		}

		return (bool) file_put_contents( $this->file, $json );
	}
}
