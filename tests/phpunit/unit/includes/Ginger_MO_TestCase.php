<?php

class Ginger_MO_TestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * @var string[]
	 */
	protected $temp_files = array();

	/**
	 * Create temporary files
	 *
	 * @param string $contents
	 * @return false|string
	 */
	protected function temp_file( string $contents = null ) {
		$file = tempnam( sys_get_temp_dir(), 'gingermo' );

		if ( false === $file ) {
			return false;
		}

		file_put_contents( $file, $contents );
		$this->temp_files[] = $file;
		return $file;
	}

	/**
	 * @return void
	 */
	public function __destruct() {
		foreach ( $this->temp_files as $file ) {
			unlink( $file );
		}
		$this->temp_files = array();
	}
}
