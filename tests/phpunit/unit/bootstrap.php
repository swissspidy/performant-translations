<?php

$root = dirname( __DIR__, 3 ) . '/';

require $root . 'lib/class-ginger-mo.php';
require $root . 'lib/class-ginger-mo-translation-file.php';
require $root . 'lib/class-ginger-mo-translation-file-mo.php';
require $root . 'lib/class-ginger-mo-translation-file-php.php';
require __DIR__ . '/includes/Plural_Forms.php';
require __DIR__ . '/includes/Ginger_MO_TestCase.php';

define( 'GINGER_MO_TEST_DATA', __DIR__ . '/data/', false );

if ( ! function_exists( 'str_starts_with' ) ) {
	/**
	 * Polyfill for `str_starts_with()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack begins with needle.
	 *
	 * @since 5.9.0
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` starts with `$needle`, otherwise false.
	 */
	function str_starts_with( $haystack, $needle ) {
		if ( '' === $needle ) {
			return true;
		}

		return 0 === strpos( $haystack, $needle );
	}
}
