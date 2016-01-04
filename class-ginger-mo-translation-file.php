<?php

class Ginger_MO_Translation_File {
	protected $headers     = array();
	protected $flag_parsed = false;
	protected $flag_error  = false;
	protected $file        = '';
	protected $entries     = array(); // [ "Original" => "Translation" ]

	private $plural_form_function = '';

	protected function __construct( $file ) {
		$this->file = $file;
		$this->flag_error = ! is_readable( $file );
	}

	static function create( $file ) {
		$moe = false;
		if ( '.mo' == substr( $file, -3 ) ) {
			$moe = new Ginger_MO_Translation_File_MO( $file );
		} elseif ( '.php' == substr( $file, -4 ) ) {
			$moe = new Ginger_MO_Translation_File_PHP( $file );
		}

		if ( ! $moe || $moe->error() ) {
			return false;
		}
		return $moe;
	}

	public function headers() {
		if ( ! $this->flag_parsed ) {
			$this->parse_file();
		}
		return $this->headers;
	}

	public function error() {
		return $this->flag_error;
	}

	public function translate( $string ) {
		if ( ! $this->flag_parsed ) {
			$this->parse_file();
		}

		return isset( $this->entries[ $string ] ) ? $this->entries[ $string ] : false;
	}

	public function get_plural_form( $number ) {
		if ( ! $this->flag_parsed ) {
			$this->parse_file();
		}

		// Incase a plural form is specified as a header, but no function included, build one.
		if ( ! $this->plural_form_function && isset( $this->headers['plural-forms'] ) ) {
			$this->plural_form_function = Ginger_MO::generate_plural_forms_function( $this->headers['plural-forms'] );
		}

		if ( $this->plural_form_function && is_callable( $this->plural_form_function ) ) {
			return call_user_func( $this->plural_form_function, $number );
		}

		// Default plural form matches English, only "One" is considered singular.
		return ( $number == 1 ? 0 : 1 );
	}
}
