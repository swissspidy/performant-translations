<?php

class Ginger_MO {
	protected $default_textdomain = 'default';
	protected $loaded_translations = array(); // [ Textdomain => [ $object1, $object2 ] ]
	protected $loaded_files = array(); // [ /path/to/file.mo => $object ]

	public static function instance() {
		static $instance = false;
		return $instance ? $instance : $instance = new Ginger_MO();
	}

	public function load( $translation_file, $textdomain = null ) {
		if ( ! class_exists( 'Ginger_MO_Translation_File' ) ) {
			include dirname( __FILE__ ) . '/class-ginger-mo-translation-file.php';
		}

		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		$translation_file = realpath( $translation_file );
		if ( !empty( $this->loaded_files[ $translation_file ][ $textdomain ] ) ) {
			if ( $this->loaded_files[ $translation_file ][ $textdomain ] && ! $this->loaded_files[ $translation_file ][ $textdomain ]->error() ) {
				return true;
			}
			return false;
		}

		if ( !empty( $this->loaded_files[ $translation_file ] ) ) {
			$moe = reset( $this->loaded_files[ $translation_file ] );
		} else {
			$moe = Ginger_MO_Translation_File::create( $translation_file );
		}
		$this->loaded_files[ $translation_file ][ $textdomain ] = $moe;

		if ( ! $moe ) {
			return false;
		}

		if ( ! isset( $this->loaded_translations[ $textdomain ] ) ) {
			$this->loaded_translations[ $textdomain ] = array();
		}

		// Prefix translations to ensure that last-loaded takes preference
		array_unshift( $this->loaded_translations[ $textdomain ], $moe );

		return true;
	}

	public function unload( $textdomain, $mo = null ) {	
		if ( ! $this->is_loaded( $textdomain ) ) {
			return false;
		}

		if ( $mo ) {
			foreach ( $this->loaded_translations[ $textdomain ] as $i => $moe ) {
				if ( $mo === $moe ) {
					unset( $this->loaded_translations[ $textdomain ][ $i ] );
					unset( $this->loaded_files[ $moe->file ][ $textdomain ] );
					return true;
				}
			}
			return true;
		}

		foreach ( $this->loaded_translations[ $textdomain ] as $moe ) {
			unset( $this->loaded_files[ $moe->file ][ $textdomain ] );
		}

		unset( $this->loaded_translations[ $textdomain ] );

		return true;
	}

	public function is_loaded( $textdomain ) {
		return !empty( $this->loaded_translations[ $textdomain ] );
	}

	public function translate( $text, $context = null, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}

		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );
		if ( ! $translation ) {
			return false;
		}

		return $translation['entries'];
	}

	public function translate_plural( $plurals, $number, $context = null, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}
		$text = implode( "\0", $plurals );
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( ! $translation ) {
			return false;
		}

		$t = is_array( $translation['entries'] ) ? $translation['entries'] : explode( "\0", $translation['entries'] );
		$num = $translation['source']->get_plural_form( $number );
		return $t[ $num ];
	}

	protected function locate_translation( $string, $textdomain = null ) {
		if ( ! $this->loaded_translations ) {
			return false;
		}
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
		if ( isset( $this->loaded_translations[ $textdomain ] ) ) {
			$moes = $this->loaded_translations[ $textdomain ];
		}
		return $moes;
	}
}
