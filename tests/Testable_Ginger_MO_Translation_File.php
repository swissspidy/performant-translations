<?php

// Ginger_MO_Translation_File has some protected methods we want to test.
class Testable_Ginger_MO_Translation_File extends Ginger_MO_Translation_File {
	public static function get_testable_instance() {
		return new Testable_Ginger_MO_Translation_File( 'dummy-data' );
	}

	public function __call( $method, $args ) {
		if ( is_callable( array( $this, $method ) ) ) {
			return call_user_func_array( array( $this, $method ), $args );
		}
		return null;
	}
}
