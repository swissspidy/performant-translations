<?php
/**
 * Main functionality.
 *
 * @package Ginger_MO
 */

/**
 * Class Ginger_MO.
 */
class Ginger_MO {
	/**
	 * Default text domain.
	 *
	 * @var string
	 */
	protected $default_textdomain = 'default';

	/**
	 * Map of loaded translations per text domain.
	 *
	 * @var array<string, Ginger_MO_Translation_File[]>
	 */
	protected $loaded_translations = array();

	/**
	 * List of loaded translation files.
	 *
	 * @var array<string,array<string, Ginger_MO_Translation_File|false>>
	 */
	protected $loaded_files = array();

	/**
	 * Returns the Ginger_MO singleton.
	 *
	 * @return Ginger_MO
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new Ginger_MO();
		}

		return $instance;
	}

	/**
	 * Loads a translation file.
	 *
	 * @param string $translation_file Translation file.
	 * @param string $textdomain Text domain.
	 * @return bool True on success, false otherwise.
	 */
	public function load( $translation_file, $textdomain = null ) {
		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		$translation_file = realpath( $translation_file );

		if ( ! $translation_file ) {
			return false;
		}

		if ( ! empty( $this->loaded_files[ $translation_file ][ $textdomain ] ) ) {
			return false !== $this->loaded_files[ $translation_file ][ $textdomain ]->error();
		}

		if ( ! empty( $this->loaded_files[ $translation_file ] ) ) {
			$moe = reset( $this->loaded_files[ $translation_file ] );
		} else {
			$moe = Ginger_MO_Translation_File::create( $translation_file );
			if ( ! $moe || $moe->error() ) {
				$moe = false;
			}
		}
		$this->loaded_files[ $translation_file ][ $textdomain ] = $moe;

		if ( ! $moe ) {
			return false;
		}

		if ( ! isset( $this->loaded_translations[ $textdomain ] ) ) {
			$this->loaded_translations[ $textdomain ] = array();
		}

		// Prefix translations to ensure that last-loaded takes preference.
		array_unshift( $this->loaded_translations[ $textdomain ], $moe );

		return true;
	}

	/**
	 * Unload all translation files or a specific one for a given text domain.
	 *
	 * @param string                     $textdomain Text domain.
	 * @param Ginger_MO_Translation_File $mo Translation file.
	 * @return bool True on success, false otherwise.
	 */
	public function unload( $textdomain, $mo = null ) {
		if ( ! $this->is_loaded( $textdomain ) ) {
			return false;
		}

		if ( $mo ) {
			foreach ( $this->loaded_translations[ $textdomain ] as $i => $moe ) {
				if ( $mo === $moe ) {
					unset( $this->loaded_translations[ $textdomain ][ $i ] );
					unset( $this->loaded_files[ $moe->get_file() ][ $textdomain ] );
					return true;
				}
			}
			return true;
		}

		foreach ( $this->loaded_translations[ $textdomain ] as $moe ) {
			unset( $this->loaded_files[ $moe->get_file() ][ $textdomain ] );
		}

		unset( $this->loaded_translations[ $textdomain ] );

		return true;
	}

	/**
	 * Determines whether translations are loaded for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return bool True if there are any loaded translations, false otherwise.
	 */
	public function is_loaded( $textdomain ) {
		return ! empty( $this->loaded_translations[ $textdomain ] );
	}

	/**
	 * Translates a singular string.
	 *
	 * @param string      $text Text to translate.
	 * @param string|null $context Optional. Context for the string.
	 * @param string      $textdomain Text domain.
	 * @return string|false Translation on success, false otherwise.
	 */
	public function translate( $text, $context = null, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}

		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( ! $translation ) {
			return false;
		}

		if ( is_array( $translation['entries'] ) ) {
			return $translation['entries'][0];
		}

		return $translation['entries'];
	}

	/**
	 * Translates plurals.
	 *
	 * @param array{0: string, 1: string} $plurals Pair of singular and plural translation.
	 * @param int                         $number Number of items.
	 * @param string|null                 $context Optional. Context for the string.
	 * @param string                      $textdomain Text domain.
	 * @return string|false Translation on success, false otherwise.
	 */
	public function translate_plural( $plurals, $number, $context = null, $textdomain = null ) {
		if ( $context ) {
			$context .= "\4";
		}
		$text        = implode( "\0", $plurals );
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( ! $translation ) {
			return false;
		}

		$t   = is_array( $translation['entries'] ) ? $translation['entries'] : explode( "\0", $translation['entries'] );
		$num = $translation['source']->get_plural_form( $number );
		return $t[ $num ];
	}

	/**
	 * Returns all existing headers for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return array<string, string> Headers.
	 */
	public function get_headers( $textdomain = null ) {
		if ( ! $this->loaded_translations ) {
			return array();
		}

		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		$headers = array();

		foreach ( $this->get_mo_files( $textdomain ) as $moe ) {
			foreach ( $moe->headers() as $header => $value ) {
				$headers[ $this->normalize_header( $header ) ] = $value;
			}
		}

		return $headers;
	}

	/**
	 * Normalizes header names to be capitalized.
	 *
	 * @param string $header Header name.
	 * @return string Normalized header name.
	 */
	protected function normalize_header( $header ) {
		$parts = explode( '-', $header );
		$parts = array_map( 'ucfirst', $parts );
		return implode( '-', $parts );
	}

	/**
	 * Returns all entries for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return string[] Entries.
	 */
	public function get_entries( $textdomain = null ) {
		if ( ! $this->loaded_translations ) {
			return array();
		}

		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		$entries = array();

		foreach ( $this->get_mo_files( $textdomain ) as $moe ) {
			$entries = array_merge( $entries, $moe->entries() );
		}

		// TODO: Return Translation_Entry instances instead to maintain back compat.
		return $entries;
	}

	/**
	 * Locates translation for a given string and text domain.
	 *
	 * @param string $singular Singular translation.
	 * @param string $textdomain Text domain.
	 * @return array{source: Ginger_MO_Translation_File, entries: string|string[]}|false Translations on success, false otherwise.
	 */
	protected function locate_translation( $singular, $textdomain = null ) {
		if ( ! $this->loaded_translations ) {
			return false;
		}
		if ( ! $textdomain ) {
			$textdomain = $this->default_textdomain;
		}

		// Find the translation in all loaded files for this text domain.
		foreach ( $this->get_mo_files( $textdomain ) as $moe ) {
			$translation = $moe->translate( $singular );
			if ( false !== $translation ) {
				return array(
					'entries' => $translation,
					'source'  => $moe,
				);
			}
			if ( $moe->error() ) {
				// Unload this file, something is wrong.
				$this->unload( $textdomain, $moe );
			}
		}

		// Nothing could be found.
		return false;
	}

	/**
	 * Returns all translation files for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return Ginger_MO_Translation_File[] List of translation files.
	 */
	protected function get_mo_files( $textdomain = null ) {
		if ( isset( $this->loaded_translations[ $textdomain ] ) ) {
			return $this->loaded_translations[ $textdomain ];
		}

		return array();
	}
}
