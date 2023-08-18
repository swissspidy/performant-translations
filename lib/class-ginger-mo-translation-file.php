<?php
/**
 * Base Ginger_MO_Translation_File class.
 *
 * @package Ginger_MO
 */

/**
 * Class Ginger_MO_Translation_File.
 */
class Ginger_MO_Translation_File {
	/**
	 * List of headers.
	 *
	 * @var array<string, string>
	 */
	protected $headers = array();

	/**
	 * Whether file has been parsed.
	 *
	 * @var bool
	 */
	protected $parsed = false;

	/**
	 * Error information.
	 *
	 * @var bool|string
	 */
	protected $error = false;

	/**
	 * File name.
	 *
	 * @var string
	 */
	protected $file = '';

	/**
	 * Translation entries.
	 *
	 * @var array<string, string>
	 */
	protected $entries = array();

	/**
	 * Plural forms function.
	 *
	 * @var callable|null Plural forms.
	 */
	protected $plural_forms = null;

	/**
	 * Constructor.
	 *
	 * @param string $file    File to load.
	 * @param string $context Optional. Context. Either 'read' or 'write'. Default 'read'.
	 */
	protected function __construct( string $file, string $context = 'read' ) {
		$this->file = $file;

		if ( 'write' === $context ) {
			if ( file_exists( $file ) ) {
				$this->error = is_writable( $file ) ? false : 'File is not writable'; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
			} elseif ( ! is_writable( dirname( $file ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
				$this->error = 'Directory not writable';
			}
		} elseif ( ! is_readable( $file ) ) {
			$this->error = 'File not readable';
		}
	}

	/**
	 * Creates a new Ginger_MO_Translation_File instance for a given file.
	 *
	 * @param string      $file     File name.
	 * @param string      $context  Optional. Context. Either 'read' or 'write'. Default 'read'.
	 * @param string|null $filetype Optional. File type. Default inferred from file name.
	 * @return false|Ginger_MO_Translation_File
	 */
	public static function create( string $file, string $context = 'read', string $filetype = null ) {
		if ( null === $filetype ) {
			$pos = strrpos( $file, '.' );
			if ( false !== $pos ) {
				$filetype = substr( $file, $pos + 1 );
			}
		}

		switch ( $filetype ) {
			case 'mo':
				return new Ginger_MO_Translation_File_MO( $file, $context );
			case 'php':
				return new Ginger_MO_Translation_File_PHP( $file, $context );
			case 'json':
				if ( function_exists( 'json_decode' ) ) {
					return new Ginger_MO_Translation_File_JSON( $file, $context );
				}
				break;
			default:
				return false;
		}

		return false;
	}

	/**
	 * Returns all headers.
	 *
	 * @return array<string, string> Headers.
	 */
	public function headers() {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}
		return $this->headers;
	}

	/**
	 * Returns all entries.
	 *
	 * @return array<string, string> Entries.
	 * @phstan-return array<string, non-empty-array<string>> Entries.
	 */
	public function entries() {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		return $this->entries;
	}

	/**
	 * Returns the current error information.
	 *
	 * @phpstan-impure
	 *
	 * @return bool|string Error
	 */
	public function error() {
		return $this->error;
	}

	/**
	 * Returns the file name.
	 *
	 * @return string File name.
	 */
	public function get_file(): string {
		return $this->file;
	}

	/**
	 * Translates a given string.
	 *
	 * @param string $text String to translate.
	 * @return false|string Translation(s) on success, false otherwise.
	 */
	public function translate( $text ) {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		return isset( $this->entries[ $text ] ) ? $this->entries[ $text ] : false;
	}

	/**
	 * Returns the plural form for a count.
	 *
	 * @param int $number Count.
	 * @return int Plural form.
	 */
	public function get_plural_form( $number ): int {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		// In case a plural form is specified as a header, but no function included, build one.
		if ( null === $this->plural_forms && isset( $this->headers['plural-forms'] ) ) {
			$this->plural_forms = $this->make_plural_form_function( $this->headers['plural-forms'] );
		}

		if ( is_callable( $this->plural_forms ) ) {
			/**
			 * Plural form.
			 *
			 * @phpstan-var int $result Plural form.
			 */
			$result = call_user_func( $this->plural_forms, $number );
			return $result;
		}

		// Default plural form matches English, only "One" is considered singular.
		return ( 1 === $number ? 0 : 1 );
	}

	/**
	 * Exports translations to file.
	 *
	 * @param Ginger_MO_Translation_File $destination Destination file.
	 * @return bool True on success, false otherwise.
	 */
	public function export( Ginger_MO_Translation_File $destination ): bool {
		if ( false !== $destination->error() ) {
			return false;
		}

		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		$destination->create_file( $this->headers, $this->entries );
		$this->error = $destination->error();

		return false === $this->error;
	}

	/**
	 * Makes a function, which will return the right translation index, according to the
	 * plural forms header
	 *
	 * @param string $expression Plural form expression.
	 * @return callable(int $num): int Plural forms function.
	 */
	public function make_plural_form_function( string $expression ) {
		try {
			$handler = new Plural_Forms( rtrim( $expression, ';' ) );
			return array( $handler, 'get' );
		} catch ( Exception $e ) {
			// Fall back to default plural-form function.
			return $this->make_plural_form_function( 'n != 1' );
		}
	}

	/**
	 * Parses the file.
	 *
	 * @return void
	 */
	protected function parse_file() {} // TODO: Move to interface or make abstract.

	/**
	 * Writes translations to file.
	 *
	 * @param array<string, string> $headers Headers.
	 * @param array<string, string> $entries Entries.
	 * @return bool True on success, false otherwise.
	 */
	protected function create_file( $headers, $entries ): bool {
		// TODO: Move to interface or make abstract.
		$this->error = 'Format not supported.';
		return false;
	}
}
