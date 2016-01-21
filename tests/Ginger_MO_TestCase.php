<?php

class Ginger_MO_TestCase extends PHPUnit_Framework_TestCase {
	protected $temp_files = array();

	// PHPUnit + PHP 5.2 doesn't appear to support this natively.
	static function assertNotFalse( $value ) {
		parent::assertTrue( false !== $value );
	}

	// Create temporary files
	function tmpnam() {
		$file = tempnam( sys_get_temp_dir(), 'gingermo' );
		$this->temp_files[] = $file;
		return $file;
	}

	function __destruct() {
		foreach ( $this->temp_files as $f ) {
			unlink( $f );
		}
		$this->temp_files = array();
	}
}
