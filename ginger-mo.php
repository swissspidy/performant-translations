<?php

/*
 * Ginger-Mo is not-quite-gold, but almost.
 * It's a "lightweight" .mo reader for WordPress, it's designed to use the minimal memory and processing.
 * This is a POC plugin and includes the ability to hook into WordPress.
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

class Ginger_MO_Translation_Provider {
	private $textdomain = 'default';
	private $ginger_mo;
	function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
		$this->ginger_mo = Ginger_MO::instance();
	}

	function translate_plural( $single, $plural, $number = 1, $context = '' ) {
		return $this->ginger_mo->translate_plural( array( $single, $plural ), $number, $context, $this->textdomain );
	}

	function translate( $text, $context = '' ) {
		return $this->ginger_mo->translate( $text, $context, $this->textdomain );
	}
}

class Ginger_MO_Translation_Compat implements ArrayAccess {
	private $ginger_mo;

	function __construct() {
		$this->ginger_mo = Ginger_MO::instance();
	}

	function offsetExists( $domain ) {
		return $this->ginger_mo->is_loaded( $domain );
	}

	function offsetGet( $domain ) {
		return new Ginger_MO_Translation_Provider( $domain );
	}

	function offsetSet( $domain, $value ) {
		// Not supported
		return false;
	}

	function offsetUnset( $domain ) {
		return $this->ginger_mo->unload( $domain );
	}

	function load_textdomain( $return, $domain, $mofile ) {
		do_action( 'load_textdomain', $domain, $mofile );
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		$this->ginger_mo->load( $mofile, $domain );

		return true;
	}

	function unload_textdomain( $return, $domain ) {
		do_action( 'unload_textdomain', $domain );

		$this->ginger_mo->unload( $domain );

		return true;
	}

	static function overwrite_wordpress() {
		global $MOJIT, $l10n;

		if ( ! isset( $MOJIT ) ) {
			$MOJIT = Ginger_MO::instance();
		}

		$l10n = new Ginger_MO_Translation_Compat();

		add_filter( 'override_unload_textdomain', array( $l10n, 'unload_textdomain' ), 10, 2 );
		add_filter( 'override_load_textdomain',   array( $l10n, 'load_textdomain'   ), 10, 3 );
	}
}
Ginger_MO_Translation_Compat::overwrite_wordpress();

class Ginger_MO {
	private $default_textdomain = 'default';
	private $loaded_mo_files = array(); //[ Textdomain => [ .., .. ] ]

	private $fallback_to_default_textdomain = false;

	static function instance() {
		static $instance = false;
		return $instance ? $instance : $instance = new Ginger_MO();
	}

	public function load( $mo, $textdomain = null ) {
		$moe = new Ginger_MO_File( $mo );
		if ( $moe->exists() && ! $moe->error() ) {
			if ( ! $textdomain ) {
				$textdomain = $this->default_textdomain;
			}
			$this->loaded_mo_files[ $textdomain ][] = $moe;
			return true;
		}
		return false;
	}

	public function unload( $textdomain, $mo = null ) {
		unset( $this->loaded_mo_files[ $textdomain ] );
	}

	public function is_loaded( $textdomain ) {
		return !empty( $this->loaded_mo_files[ $textdomain ] );
	}

	public function translate( $text, $context, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );
		return $translation ? $translation[0] : $text;
	}

	public function translate_plural( $plurals, $number, $context, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}
		$text = implode( "\0", $plurals );
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( $translation ) {
			$t = explode( "\0", $translation[0] );
			$num = $this->get_plural_forms_number( $number, $translation[1] /* Moe */ );
		} else {
			$t = $plurals;
			$num = $this->get_plural_forms_number( $number );
		}

		if ( isset( $t[ $num ] ) ) {
			return $t[ $num ];
		} else {
			return $t[ count( $t ) -1 ]; // Just return the highest plural form.
		}
	}

	private function locate_translation( $string, $textdomain = null ) {
		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		// Find the translation in all loaded files for this text domain
		$moes = isset( $this->loaded_mo_files[ $textdomain ] ) ? $this->loaded_mo_files[ $textdomain ] : array();
		foreach ( $moes as $i => $moe ) {
			if ( false !== ( $translation = $moe->translate( $string ) ) ) {
				return array(
					$translation,
					$moe
				);
			}
			if ( $moe->error() ) {
				// Unload this file, something is wrong.
				unset( $this->loaded_mo_files[ $textdomain ][ $i ] );
			}
		}

		if ( $this->fallback_to_default_textdomain && $textdomain != $this->default_textdomain ) {
			return $this->locate_translation( $string, $this->default_translation );
		} else {
			// Default textdomain, and no translation available.
			return false;
		}
	}

	private function get_plural_forms_number( $number, $moe = false ) {
		// When no mo is presented for context, fallback to the first default translation if it's loaded, else use English plural forms.
		if ( ! $moe && empty( $this->loaded_mo_files[ $this->default_textdomain ] ) ) {
			if ( ! $moe ) {
				return ( $number == 1 ? 0 : 1 );
			}
		} elseif ( ! $moe ) {
			// Fallback to the first default translation.
			$moe = reset( $this->loaded_mo_files[ $this->default_textdomain ] );
		}

		$plural_forms = $this->parse_plural_forms( $moe->meta['plural-forms'] );
		if ( ! $plural_forms ) {
			return ( $number == 1 ? 0 : 1 );
		}
		$plural_form = $plural_forms['plural-form'];
		$plurals = $plural_forms['num-plurals'];

		$func = $this->get_plural_form_function( $plural_form );
		$index = $func( $number );

		// Some plural form functions return indexes higher than allowed by the language
		return min( $index, $plurals );
	}

	private function get_plural_form_function( $plural_form ) {
		static $funcs = array();
		if ( ! isset( $funcs[ $plural_form ] ) ) {
			$funcs[ $plural_form ] = $this->generate_plural_form_function( $plural_form );
		}
		return $funcs[ $plural_form ];
	}

	private function generate_plural_form_function( $forms ) {
		$nexpression = str_replace( 'n', '$n', $forms );
		return create_function( '$n', "return (int)($nexpression);" );
	}

	private function parse_plural_forms( $form ) {
		// Validate that the plural form function is legit
		// This should/could use a more strict plural matching (such as validating it's a valid expression)
		if ( preg_match( '#^nplurals=(\d+);\s*plural=([n><!=\s()?%&|:0-9-]+);?$#i', $form, $match ) ) {
			return array(
				'num-plurals' => (int) $match[1] - 1, // indexed from 1
				'plural-form' => preg_replace( '#\s+#', '', $match[2] ),
			);
		}
		return false;
	}

}

class Ginger_MO_File {
	public $meta = array(
		'plural-forms' => 'nplurals=2;plural=(n!=1);',
	);
	public $total_translations   = 0;

	protected $flag_parsed       = false;
	protected $flag_exists       = false;
	protected $flag_error        = false;

	protected $entries           = array(); // [ "Original" => "Translation" ]

	// used for unpack(), little endian = V, big endian = N
	private $uint32 = false;

	private $file                = '';
	private $file_contents       = null;

	public function __construct( $file ) {
		$this->file = $file;
		$this->flag_exists = is_readable( $file );
	}

	public function exists() {
		return $this->flag_exists;
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

	protected function detect_endian_and_validate_file() {
		$magic_marker = 0x950412de;

		$header = $this->read( 0, 4 );

		$big = unpack( 'N', $header );
		$big = reset( $big );
		$little = unpack( 'V', $header );
		$little = reset( $little );

		if ( $big === $magic_marker ) {
			return 'N';
		} elseif ( $little === $magic_marker ) {
			return 'V';
		} else {
			$this->flag_error = "Magic Marker doesn't exist";
			return false;
		}
	}

	protected function read( $from, $bytes ) {
		if ( empty( $this->file_contents ) ) {
			$this->file_contents = file_get_contents( $this->file );
		}
		if ( $from + $bytes > strlen( $this->file_contents ) ) {
			$this->flag_error = "Attempting to read invalid bytelength";
			return false;
		}

		return substr( $this->file_contents, $from, $bytes );
	}

	protected function parse_file() {
		$this->flag_parsed = true;

		$this->uint32 = $this->detect_endian_and_validate_file( $this->read( 0, 4 ) );
		if ( ! $this->uint32 ) {
			return false;
		}

		$offsets = $this->read( 4, 24 );
		if ( ! $offsets ) {
			return false;
		}

		$offsets = unpack( "{$this->uint32}rev/{$this->uint32}total/{$this->uint32}originals_addr/{$this->uint32}translations_addr/{$this->uint32}hash_length/{$this->uint32}hash_addr", $offsets );

		$this->total_translations = $offsets['total'];
		$offsets['originals_length'] = $offsets['translations_addr'] - $offsets['originals_addr'];
		$offsets['translations_length'] = $offsets['hash_addr'] - $offsets['translations_addr'];

		// Load the Originals
		$original_data = str_split( $this->read( $offsets['originals_addr'], $offsets['originals_length'] ), 8 );
		$translations_data = str_split( $this->read( $offsets['translations_addr'], $offsets['translations_length'] ), 8 );

		foreach ( array_keys( $original_data ) as $i ) {
			$o = unpack( "{$this->uint32}length/{$this->uint32}pos", $original_data[ $i ] );
			$t = unpack( "{$this->uint32}length/{$this->uint32}pos", $translations_data[ $i ] );

			$original = $this->read( $o['pos'], $o['length'] );
			$translation = $this->read( $t['pos'], $t['length'] );
			$translation = rtrim( $translation, "\0" ); // GlotPress bug

			// Metadata about the MO file is stored in the first translation entry.
			if ( '' === $original ) {
				foreach ( explode( "\n", $translation ) as $meta_line ) {
					if ( ! $meta_line ) continue;
					list( $name, $value ) = array_map( 'trim', explode( ':', $meta_line, 2 ) );
					$this->meta[ strtolower( $name ) ] = $value;
				}
			} else {
				$this->entries[ $original ] = $translation;
			}
		}

		unset( $this->file_contents, $original_data, $translations_data );

		return true;
	}

}

