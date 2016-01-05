<?php
/**
 * This is an example PHP translation file for Ginger MO.
 * It specifies a custom plural form callback which is identical to english, although it doesn't need to.
 * An error within this file will most definately cause a WSOD for the calling functions.
 *
 * The main benefits of using this format over MO files is that it's much faster to parse, and also cacheable by PHP opcode caches.
 */

/*
 * A custom plural forms callback for the translations provided within this file.
 * The function name should be prefixed with information about what it's applying to to be unique.
 * The function name should be suffixed with enough random data to ensure that multiple translations for the same textdomain/locale can be used.
 */
function example_translation_plural_forms_24w3487639867k95836( $number ) {
	return (int) ( 1 === $number );
}

return array(
	/*
	 * A callback which specifies how to determine the plural form for the translation.
	 * If not specified, any `Plural-Form` header will be used to generate the appropriate plural forms instead.
	 * If neither are available, then English plural forms `( $number === 1 )` will be applied.
	 */
	'plural_form_function' => 'example_translation_plural_forms_24w3487639867k95836',

	/*
	 * Standard PO headers can be added, although not needed or used.
	 */
	'headers' => array(
		'PO-Revision-Date' => '2016-01-05 18:45:32+1000',
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain; charset=UTF-8',
		'Content-Transfer-Encoding' => '8bit',
		'X-Generator' => 'GlotPress/1.0-alpha-1100',
		'Project-Id-Version' => 'Example Project',
	),

	/*
	 * Strings are stored as they are in a standard gettext .mo file.
	 * Originals are the Array key, Translation the value.
	 * - Singular strings are as-is
	 * - Plural forms are separated by \0, or alternatively as a PHP array
	 * - Context is before the original, with \4 following it
	 */
	'entries' => array(
		"singular"                  => 'singular translation',
		"context\4singular"         => 'singular translation with context',
		"plural0\0plural1"          => "plural0 translation\0plural1 translation\0plural2 translation",
		"array0\0array1"            => array( "array0 translation", "array1 translation", "array2 translation" ),
		"context\4plural0\0plural1" => "plural0 translation with context\0plural1 translation with context\0plural2 translation with context",
	),
);
