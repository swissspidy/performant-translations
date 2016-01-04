<?php

class Ginger_MO_PHP_File {
	private $headers = array();
	private $entries = array();
	private $plural_form_function = '';

	private $file = null;

	protected $flag_parsed  = false;
	protected $flag_error   = false;

	public function error() {
		return $this->flag_error;
	}

	public function get_plural_form( $number ) {
		if ( $this->plural_form_function && is_callable( $this->plural_form_function ) ) {
			return call_user_func( $this->plural_form_function, $number );
		}

		// Default plural form matches English, only "One" is considered singular.
		return ( $number == 1 ? 0 : 1 );
	}

	public function headers() {
		return $this->headers;
	}

	public function translate( $string ) {
		if ( ! $this->flag_parsed ) {
			$this->load();
		}
		return isset( $this->entries[ $string ] ) ? $this->entries[ $string ] : false;
	}

	protected function __construct( $file ) {
		$this->file = $file;
		$this->flag_error = ! is_readable( $file );
	}

	static function create( $file ) {
		$php_moe = new Ginger_MO_PHP_File( $file );
		if ( $php_moe->error() ) {
			return false;
		}
		return $php_moe;
	}

	private function load() {
		$result = include( $this->file );
		if ( ! $result || ! is_array( $result ) ) {
			$this->flag_error = true;
			return;
		}
		var_dump( $result );
		foreach ( array( 'headers', 'entries', 'plural_form_function' ) as $field ) {
			if ( isset( $result[ $field ] ) ) {
				$this->$field = $result[ $field ];
			}
		}
		$this->flag_parsed = true;
	}
}