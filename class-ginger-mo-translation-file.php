<?php

class Ginger_MO_Translation_File {
	protected $headers  = array();
	protected $parsed   = false;
	protected $error    = false;
	protected $file     = '';
	protected $entries  = array(); // [ "Original" => "Translation" ]

	private $plural_form_function = '';

	protected function __construct( $file ) {
		$this->file = $file;
		$this->error = ! is_readable( $file );
	}

	static function create( $file ) {
		$moe = false;
		if ( '.mo' == substr( $file, -3 ) ) {
			$moe = new Ginger_MO_Translation_File_MO( $file );
		} elseif ( '.php' == substr( $file, -4 ) ) {
			$moe = new Ginger_MO_Translation_File_PHP( $file );
		} elseif ( '.json' == substr( $file, -5 ) ) {
			$moe = new Ginger_MO_Translation_File_JSON( $file );
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

	protected function generate_plural_forms_function( $plural_form ) {
		$plural_func = false;

		// Validate that the plural form function is legit
		// This should/could use a more strict plural matching (such as validating it's a valid expression)
		if ( $plural_form && preg_match( '#^nplurals=(\d+);\s*plural=([n><!=\s()?%&|:0-9-]+);?$#i', $plural_form, $match ) ) {
			$num_plurals = (int) $match[1] - 1; // indexed from 1
			$nexpression =  str_replace( 'n', '$n', preg_replace( '#\s+#', '', $match[2] ) );
			$plural_func = create_function( '$n', "return min( $num_plurals, (int)($nexpression) );" );
		}

		return $plural_func;
	}
}
