<?php

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