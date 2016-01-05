<?php
/*
 * Ginger-Mo is not-quite-gold, but almost.
 * It's a "lightweight" .mo reader for WordPress, it's designed to use the minimal memory and processing.
 * This is a POC plugin and includes the ability to hook into WordPress.
 */

include dirname(__FILE__) . '/class-ginger-mo.php';
include dirname(__FILE__) . '/class-ginger-mo-translation-file.php';
include dirname(__FILE__) . '/class-ginger-mo-translation-file-mo.php';
include dirname(__FILE__) . '/class-ginger-mo-translation-file-php.php';
include dirname(__FILE__) . '/class-ginger-mo-translation-compat.php';

// All the WordPress magic:
Ginger_MO_Translation_Compat::overwrite_wordpress();

/*
//PHP testing

Ginger_MO::instance()->load( __DIR__ . '/example-php-translation.php', 'testtextdomain' );

var_dump( Ginger_MO::instance()->translate( "singular", "context", 'testtextdomain') );
var_dump( Ginger_MO::instance()->translate_plural( array( "plural0", "plural1" ), 1, false, 'testtextdomain' ) );

die();
//*/

/*
Ginger_MO::instance()->fallback_to_default_textdomain( false );


add_action( 'init', function() {

	var_dump( Ginger_MO::instance() );

	var_dump( __( '%1$s - Comments on %2$s', 'whoop' ) );

	var_dump( Ginger_MO::instance() );

	die();

} );
*/

/*
$ginger = new Ginger_MO(); $ginger->load( WP_LANG_DIR . '/continents-cities-fr_FR.mo' );

var_dump( $ginger->translate( $ginger->example_string ), $ginger );


// Want some crazy Plural forms? Try Russian.
// "Plural-Forms: nplurals=4; plural=(n==1) ? 0 : (n%10==1 && n%100!=11) ? 3 : ((n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20)) ? 1 : 2);\n"


$time = microtime(1);
	$ginger = new Ginger_MO( WP_LANG_DIR . '/admin-fr_FR.mo' );
var_dump( "Loading took " . (microtime(1)-$time) );


// Plurals!
$plural = array();
foreach ( range( 0, 5 ) as $i  ) {
	$plural[$i] = $ginger->translate_n( '%s aPost', '%s aPosts', $i );
}
var_dump( $plural );


$time = microtime(1);
$translations = array();
foreach ( [ "%s Post\0%s Posts" ] as $string ) {
	$translations[ $string ] = $ginger->translate( $string );
}
var_dump( "Loading took " . (microtime(1)-$time) . ' loading ' . count ($ginger->entries) . ' translations' );

var_dump( $translations );


*/
