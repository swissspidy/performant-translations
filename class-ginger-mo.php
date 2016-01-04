<?php

class Ginger_MO {
	private $default_textdomain = 'default';
	private $loaded_mo_files = array(); // [ Textdomain => [ .., .. ] ]

	private $fallback_to_default_textdomain = false;

	static function instance() {
		static $instance = false;
		return $instance ? $instance : $instance = new Ginger_MO();
	}

	public function load( $translation_file, $textdomain = null ) {
		$moe = Ginger_MO_Translation_File::create( $translation_file );
		if ( ! $moe ) {
			return false;
		}

		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}
		$this->loaded_mo_files[ $textdomain ][] = $moe;
		return true;
	}

	public function fallback_to_default_textdomain( $set = null ) {
		if ( null !== $set ) {
			$this->fallback_to_default_textdomain = $set;
		}
		return $this->fallback_to_default_textdomain;
	}

	public function unload( $textdomain, $mo = null ) {	
		if ( $mo ) {
			foreach ( $this->loaded_mo_files[ $textdomain ] as $i => $moe ) {
				if ( $mo === $moe ) {
					unset( $this->loaded_mo_files[ $textdomain ][ $i ] );
					return true;
				}
			}
			return true;
		}

		unset( $this->loaded_mo_files[ $textdomain ] );
		return true;
	}

	public function is_loaded( $textdomain ) {
		return !empty( $this->loaded_mo_files[ $textdomain ] );
	}

	public function translate( $text, $context, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}

		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );
		return $translation ? $translation['entries'] : $text;
	}

	public function translate_plural( $plurals, $number, $context, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}
		$text = implode( "\0", $plurals );
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( $translation ) {
			$t = is_array( $translation['entries'] ) ? $translation['entries'] : explode( "\0", $translation['entries'] );
			$num = $translation['source']->get_plural_form( $number );
		} else {
			$t = $plurals;
			// Fallback to english grammer
			$num = ( $number == 1 ? 0 : 1 );
		}

		if ( isset( $t[ $num ] ) ) {
			return $t[ $num ];
		} else {
			return $t[ count( $t ) -1 ]; // Just return the highest plural form.
		}
	}

	static public function generate_plural_forms_function( $plural_form ) {
		$num_plurals = 1;
		$plural_func = false;
		// Validate that the plural form function is legit
		// This should/could use a more strict plural matching (such as validating it's a valid expression)
		if ( $plural_form && preg_match( '#^nplurals=(\d+);\s*plural=([n><!=\s()?%&|:0-9-]+);?$#i', $plural_form, $match ) ) {
			$num_plurals = (int) $match[1] - 1; // indexed from 1
			$nexpression =  str_replace( 'n', '$n', preg_replace( '#\s+#', '', $match[2] ) );
			$plural_func = create_function( '$n', "return (int)($nexpression);" );
		}
		return compact( 'num_plurals', 'plural_func' );
	}

	private function locate_translation( $string, $textdomain = null ) {
		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		// Find the translation in all loaded files for this text domain
		foreach ( $this->get_mo_files( $textdomain ) as $moe ) {
			if ( false !== ( $translation = $moe->translate( $string ) ) ) {
				return array(
					'entries' => $translation,
					'source' => $moe
				);
			}
			if ( $moe->error() ) {
				// Unload this file, something is wrong.
				$this->unload( $textdomain, $moe );
			}
		}

		// Nothing could be found
		return false;
	}

	protected function get_mo_files( $textdomain = null ) {
		$moes = array();
		if ( isset( $this->loaded_mo_files[ $textdomain ] ) ) {
			$moes = $this->loaded_mo_files[ $textdomain ];
		}

		if ( $this->fallback_to_default_textdomain && $textdomain != $this->default_textdomain ) {
			$moes = array_merge( $moes, $this->get_mo_files( $this->default_textdomain ) );
		}

		return $moes;
	}

}