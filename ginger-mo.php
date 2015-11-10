<?php
/*
 * Ginger-Mo is not-quite-gold, but almost.
 * It's a "lightweight" .mo reader for WordPress, it's designed to use the minimal memory and processing.
 * This is a POC plugin and includes the ability to hook into WordPress.
 */
 
	/*
	// PJW Hashing used by MO file hashtables
	function hashPJW ($key) {
		$hval = 0;
		for ($i = 0, $key_len = strlen( $key ); $i < $key_len; $i++ ) {
			$hval = ( $hval << 4 ) + ord( $key{$i} );
			$g = $hval & 0xF0000000;
			if( $g !== 0 ){
				if ( $g < 0 )
					$hval ^= ( ( ($g & 0x7FFFFFFF) >> 24 ) | 0x80 ); // shift wordaround
				else
					$hval ^= ( $g >> 24 );
				$hval ^= $g;
			}
		}
		
		if ( $hval >= 0 )
			return $hval;
		else
			return (float) sprintf('%u', $hval); // unsigned workaround
	}*/

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
	private $mo_jit;
	function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
		$this->mo_jit = Ginger_MO::instance();
	}

	function translate_plural( $single, $plural, $number = 1, $context = '' ) {
		return $this->mo_jit->translate_plural( array( $single, $plural ), $number, $context, $this->textdomain );
	}
	function translate( $text, $context = '' ) {
		return $this->mo_jit->translate( $text, $context, $this->textdomain );
	}
}

class Ginger_MO_Translation_Compat implements ArrayAccess {
	private $mo_jit;
	function __construct() {
		$this->mo_jit = Ginger_MO::instance();
	}

	function offsetExists( $domain ) {
		return $this->mo_jit->is_loaded( $domain );
	}

	function offsetGet( $domain ) {
		return new Ginger_MO_Translation_Provider( $domain );
	}

	function offsetSet( $domain, $value ) {
		// Not supported
		return false;
	}

	function offsetUnset( $domain ) {
		return $this->mo_jit->unload( $domain );
	}

	function load_textdomain( $return, $domain, $mofile ) {
		do_action( 'load_textdomain', $domain, $mofile );
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		$this->mo_jit->load( $mofile, $domain );

		return true;
	}

	function unload_textdomain( $return, $domain ) {
		do_action( 'unload_textdomain', $domain );

		$this->mo_jit->unload( $domain );

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
	private $loaded_mo_files = array();

	private $fallback_to_default_textdomain = false;

	static function instance() {
		static $instance = false;
		return $instance ? $instance : $instance = new Ginger_MO();
	}

	public function load( $mo, $textdomain = null ) {
		$moe = new Ginger_MO_MO_FILE( $mo );
		if ( $moe->flag( 'exists' ) && ! $moe->flag( 'error' ) ) {
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
			if ( $moe->flag( 'error' ) ) {
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

class Ginger_MO_MO_FILE {
	public $meta = array(
		'plural-forms' => 'nplurals=2;plural=(n!=1);',
		'load-all-after' => 0
	);
	public $total_translations = 0;

	private $hashmap             = array();

	private $flag_parsed         = false;
	private $flag_exists         = false;
	private $flag_read_full_file = false;
	private $flag_parse_all      = false;
	private $flag_error          = false;

	// used for unpack(), little endian = V, big endian = N
	private $uint32 = false;

	private $entries             = array(); // [ "String" => (int)$translation_id, "String2" => "Translation2", ...]
	private $originals           = array(); // [ 0 => [ 123, 10 ], ... ] = [ translation_id => [ start, length ], ... ]
	private $translations        = array(); // [ 0 => [ 123, 10 ], ... ] = [ translation_id => [ start, length ], ... ]
	private $originals_by_length = array(); // [ 1 => [ &$originals, &$originals], 12 => [&$originals, ... ], ... ]

	private $fp            = null;
	private $file          = null;
	private $file_contents = null;

	function __construct( $file, $read_full_file_from_disk = false ) {
		$this->file = $file;
		$this->flag_exists = is_readable( $file );
		$this->flag_error |= !$this->flag_exists;
		$this->flag_read_full_file |= $read_full_file_from_disk;
		// If remote file, load it all
		$this->flag_read_full_file |= ( false !== strpos( $file, '://' ) );

		// Allow MO files to say "If someone asks for x or more of my translations, mass-load them all"
		// This applies mostly to the Contents/Cities MO which isn't normally used, but all of is needed on some pages
		if ( false !== stripos( $this->file, 'continents-cities' ) ) {
			$this->meta['load-all-after'] = 2;
		}
	}

	public function flag( $flag ) {
		$flag = "flag_$flag";
		return isset( $this->$flag ) ? $this->$flag : false;
	}

	public function translate( $string ) {
		if ( isset( $this->entries[ $string ] ) ) {
			if ( is_int( $this->entries[ $string ] ) ) {
				$this->entries[ $string ] = $this->load_translation_by_id( $this->entries[ $string ] );
			}
			return $this->entries[ $string ];
		}

		$this->entries[ $string ] = $this->find_translation( $string );

		if ( $this->meta['load-all-after'] && $this->meta['load-all-after'] >= count( $this->entries ) ) {
			$this->mass_load_all_translations();
		}

		return $this->entries[ $string ];
	}

	public function mass_load_all_translations() {
		// Mass-load translation entries
		foreach ( $this->originals as $id => $data ) {
			$this->entries[ $this->load_original_by_id( $id ) ] = (int) $id;
		}

		foreach ( $this->entries as $original => $id ) {
			if ( is_int( $id ) ) { // string = already loaded
				$this->entries[ $original ] = $this->load_translation_by_id( $id );
			}
		}
	}

	private function parse_file() {
		$this->flag_parsed = true;

		// Measure file opening latency
		$start_time = microtime(1);

		$this->uint32 = $this->detect_endian_and_validate_file();
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

		// If disk latency exists, read the full file into memory
		$end_time = microtime(1);
		if ( $end_time - $start_time > 0.0005 ) {
			$this->flag_read_full_file = true;
			var_dump( "Slow disk detected; Loading full mo file into memory" );
		}

		// Load the Originals offsets
		$originals = $this->read( $offsets['originals_addr'], $offsets['originals_length'] );
		foreach ( str_split( $originals, 8 ) as $i => $data ) {
			$e = unpack( "{$this->uint32}length/{$this->uint32}pos", $data );
			$this->originals[ $i ] = array( $e['pos'], $e['length'] );
			$this->originals_by_length[ $e['length'] ][ $i ] = &$this->originals[ $i ];
		}

		// Load the Translations offsets
		$translations = $this->read( $offsets['translations_addr'], $offsets['translations_length'] );
		foreach ( str_split( $translations, 8 ) as $i => $data ) {
			$e = unpack( "{$this->uint32}length/{$this->uint32}pos", $data );
			$this->translations[ $i ] = array( $e['pos'], $e['length'] );
		}

		// Load the hashmap
		if ( $offsets['hash_length'] ) {
			$hashmap = $this->read( $offsets['hash_addr'], $offsets['hash_length'] * 4 );
			foreach ( str_split( $hashmap, 4 ) as $id => $hash ) {
				$uint = unpack( $this->uint32, $hash );
				$this->hashmap[ $id ] = reset( $uint );
			}
		} else {
			var_dump( "(hashmap) $this->file has no hashes." );
		}

		// Load the Language meta from the first entry
		$metas = trim( $this->read( $this->translations[0][0], $this->translations[0][1] ) );
		foreach ( explode( "\n", $metas ) as $metas_line ) {
			list( $name, $value ) = array_map( 'trim', explode( ':', $metas_line ) );
			$name = strtolower( $name );
			$this->meta[ $name ] = $value;
		}

		if ( $this->flag_parse_all ) {
			$this->mass_load_all_translations();
		}
	}

	// Is this 32bit compatible? 
	private function hash_string( $string ) {
		$val = $g = 0;

		/* Compute the hash value for the given string.  */
		for ( $i = 0, $len = strlen( $string ); $i < $len; $i++ ) {
     		$val <<= 4;
     		$val += ord( $string[ $i ] );
     		$g = $val & 0xf0000000; // (0xf << 28);
     		if ( $g != 0 ) {
				$val ^= $g >> 24;
				$val ^= $g;
			}
		}

		return $val;
	}

	// Now.. what do those variables mean..
	function find_translation_by_hashmap( $string ) {
		$V = $this->hash_string( $string );
		$S = count( $this->hashmap );// * 4;

		$hash_cursor = $V % $S;
		$orig_hash_cursor = $hash_cursor;
		$increment = 1 + ($V % ( $S - 2 ) );
	
		while ( true ) {
			if ( ! isset( $this->hashmap[ $hash_cursor  ] ) ) {
				var_dump( "Hashmap: $hash_cursor is not set.. what the what?" );
				var_dump( $this );
				die();
				break;
			}
			$index = $this->hashmap[ $hash_cursor  ] - 1;
			if ( $index < 0 ) {
				break;
			}
//	var_dump( "(hashmap) trying $index for $string" );

			// Verify we've hit the correct string,  otherwise it's a colision and should try the 2nd hsah.
			if ( $string === $this->load_original_by_id( $index ) ) {
				//var_dump( "(hashmap) Found $string in $index" );
				return $this->load_translation_by_id( $index );
			}
	
			$hash_cursor += $increment;
			$hash_cursor %= $S;
	
			if ( $hash_cursor === $orig_hash_cursor ) {
				break;
			}
		}
//	var_dump( "(hashmap) Couldn't find $string in hashmap - $this->file" );
		return false;
	}

	function find_translation( $text ) {
		if ( ! $this->flag_parsed ) {
			$this->parse_file();
		}

		if ( $this->hashmap ) {
			return $this->find_translation_by_hashmap( $text );
		}

		return $this->find_translation_by_length( $text );

	}

	/**
	 * Searches for a translation by locating the original.
	 * As not all originals have been loaded, it narrows the search
	 * by using the length of the strings, followed by divide-and-conquer 
	 *
	 * It uses a modified binary search - the search is skewed towards how
	 * far into the character range the original is, instead of pure a
	 * divide-in-half processing.
	 * This obviously requires that the MO file originals are ordered according
	 * to their ASCII first-character positions, which might not hold-true.
	 *
	 */
	private function find_translation_by_length( $text ) {

		// Search by length
		$len = strlen( $text );

		$keys = array_keys( $this->originals_by_length[ $len ] );

		$base = 32;
		$aim  = ord( $text[0] );
		$top  = 126;

		while ( $keys ) {

			$percent = ( ($aim - $base)  / ($top - $base ) );
			$index = ceil( $percent * count( $keys ) );
			if ( $index >= count( $keys ) || $index < 0 ) {
				$index = max( min( count( $keys )-1, $index ), 0 );
			}
//var_dump( "(length) trying $keys[$index] for $text" );
			$original = $this->load_original_by_id( $keys[ $index ] );

			if ( $original === $text ) {
				return $this->load_translation_by_id( $keys[ $index ] );
			}

			$cmp = strcmp( $text, $original );
			if ( $cmp > 0 ) {
				$keys = array_slice( $keys, $index + 1 );
				$base = ord( $original[0] );
			} else {
				$keys = array_slice( $keys, 0, $index );
				$top = ord( $original[0] );
			}

		}
		return false;

	}

	private function load_original_by_id( $id ) {
		if ( ! $id || ! isset( $this->originals[ $id ] ) ) {
			return;
		}

		$original = '';
		if ( $this->originals[ $id ][0] ) {
			$original = $this->read( $this->originals[ $id ][0], $this->originals[ $id ][1] );
		}

		unset(
			$this->originals[ $id ],
			$this->originals_by_length[ strlen( $original ) ][ $id ]
		);

		$this->entries[ $original ] = (int) $id;

		return $original;
	}

	private function load_translation_by_id( $id ) {
		if ( ! $id || ! isset( $this->translations[ $id ] ) ) {
			return;
		}

		$translation = '';
		if ( $this->translations[ $id ][0] ) {
			$translation = $this->read( $this->translations[ $id ][0], $this->translations[ $id ][1] );
		}

		unset( $this->translations[ $id ] );

		return $translation;
	}

	private function read( $from, $bytes ) {
		if ( $this->flag_read_full_file ) {
			if ( empty( $this->file_contents ) ) {
				if ( $this->fp ) {
					fclose( $this->fp );
				}
				$this->file_contents = file_get_contents( $this->file );
			}
			if ( $from + $bytes > strlen( $this->file_contents ) ) {
				$this->flag_error = true;
				return false;
			}

			return substr( $this->file_contents, $from, $bytes );
		}

		if ( ! $this->fp ) {
			$this->fp = fopen( $this->file, 'rb' );
		}

		fseek( $this->fp, $from );
		$data = fread( $this->fp, $bytes );

		if ( $bytes !== strlen( $data ) ) {
			$this->flag_error = true;
			return false;
		}

		return $data;
	}

	private function detect_endian_and_validate_file() {
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
			$this->flag_error = true;
			return false;
		}
	}

}
