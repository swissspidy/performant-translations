<?php
/**
 * This is an example PHP translation file for Ginger MO.
 * It specifies a custom plural form callback which is identical to english, although it doesn't need to.
 * An error within this file will most definately cause a WSOD for the WordPress site :(
 */

function plural_forms_24w3487639867k95836( $number ) {
	return (int) ( 1 === $number );
}

return array(
	'plural_form_function' => 'plural_forms_24w3487639867k95836',
	'headers' => array(
		'PO-Revision-Date' => '2015-08-01 14:38:19+0000',
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain; charset=UTF-8',
		'Content-Transfer-Encoding' => '8bit',
		'X-Generator' => 'GlotPress/1.0-alpha-1100',
		'Project-Id-Version' => 'Administration',
	),
	/*
	 * Strings are stored as they are in a standard gettext .mo file.
	 * Originals are the Array key, Translation the value.
	 * - Singular strings are as-is
	 * - Plural forms are separated by \0
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
