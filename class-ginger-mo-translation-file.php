<?php

class Ginger_MO_Translation_File {
	protected $headers  = array();
	protected $parsed   = false;
	protected $error    = false;
	protected $file     = '';
	protected $entries  = array(); // [ "Original" => "Translation" ]

	protected $plural_form_function = '';

	protected function __construct( $file, $context = 'read' ) {
		$this->file = $file;

		if ( 'write' == $context ) {
			if ( file_exists( $file ) ) {
				$this->error = ! is_writable( $file );
			} elseif ( ! is_writable( dirname( $file ) ) ) {
				$this->error = true;
			}
		} elseif ( ! is_readable( $file ) ) {
			$this->error = true;
		}
	}

	public static function create( $file, $context = 'read' ) {
		$extension = substr( $file, strrpos( $file, '.' )+1 );
		switch ( $extension ) {
			case 'mo':
				if ( ! class_exists( 'Ginger_MO_Translation_File_MO' ) ) {
					include dirname(__FILE__) . '/class-ginger-mo-translation-file-mo.php';
				}
				$moe = new Ginger_MO_Translation_File_MO( $file, $context );
				break;
			case 'php':
				if ( ! class_exists( 'Ginger_MO_Translation_File_PHP' ) ) {
					include dirname(__FILE__) . '/class-ginger-mo-translation-file-php.php';
				}
				$moe = new Ginger_MO_Translation_File_PHP( $file, $context );
				break;
			case 'json':
				if ( ! class_exists( 'Ginger_MO_Translation_File_JSON' ) ) {
					include dirname(__FILE__) . '/class-ginger-mo-translation-file-json.php';
				}
				$moe = new Ginger_MO_Translation_File_JSON( $file, $context );
				break;
			default:
				$moe = false;
		}

		if ( ! $moe || $moe->error() ) {
			return false;
		}

		return $moe;
	}

	public function headers() {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}
		return $this->headers;
	}

	public function error() {
		return $this->error;
	}

	public function translate( $string ) {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		return isset( $this->entries[ $string ] ) ? $this->entries[ $string ] : false;
	}

	public function get_plural_form( $number ) {
		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		// Incase a plural form is specified as a header, but no function included, build one.
		if ( ! $this->plural_form_function && isset( $this->headers['plural-forms'] ) ) {
			$this->plural_form_function = $this->generate_plural_forms_function( $this->headers['plural-forms'] );
		}

		if ( $this->plural_form_function && is_callable( $this->plural_form_function ) ) {
			return call_user_func( $this->plural_form_function, $number );
		}

		// Default plural form matches English, only "One" is considered singular.
		return ( $number == 1 ? 0 : 1 );
	}

	public function export( Ginger_MO_Translation_File $destination ) {
		if ( $destination->error() ) {
			return false;
		}

		if ( ! $this->parsed ) {
			$this->parse_file();
		}

		$destination->create_file( $this->headers, $this->entries, $this->file );
		$this->error = $destination->error();

		return ! $this->error;
	}

	protected function generate_plural_forms_function( $plural_form ) {
		$plural_func_contents = $this->generate_plural_forms_function_content( $plural_form );
		if ( ! $plural_func_contents ) {
			return false;
		}

		return create_function( '$n', $plural_func_contents );
	}

	protected function generate_plural_forms_function_content( $plural_form ) {
		$plural_func_contents = false;
		// Validate that the plural form function is legit
		// This should/could use a more strict plural matching (such as validating it's a valid expression)
		if ( $plural_form && preg_match( '#^nplurals=(\d+);\s*plural=([n><!=\s()?%&|:0-9-]+);?$#i', $plural_form, $match ) ) {
			$nexpression =  str_replace( 'n', '$n', $match[2] );
			$plural_func_contents = "return (int)($nexpression);";
		}
		return $plural_func_contents;
	}

	protected function parse_file() {}
	protected function create_file( $headers, $entries, $source ) {
		$this->error = "Format not supported.";
		return false;
	}
}
