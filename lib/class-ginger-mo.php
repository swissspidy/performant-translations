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
	public static function instance(): Ginger_MO {
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
	public function load( string $translation_file, string $textdomain = 'default' ): bool {
		$translation_file = realpath( $translation_file );

		if ( false === $translation_file ) {
			return false;
		}

		if (
			isset( $this->loaded_files[ $translation_file ][ $textdomain ] ) &&
			false !== $this->loaded_files[ $translation_file ][ $textdomain ]
		) {
			return false === $this->loaded_files[ $translation_file ][ $textdomain ]->error();
		}

		if ( isset( $this->loaded_files[ $translation_file ] ) && array() !== $this->loaded_files[ $translation_file ] ) {
			$moe = reset( $this->loaded_files[ $translation_file ] );
		} else {
			$moe = Ginger_MO_Translation_File::create( $translation_file );
			if ( false === $moe || false !== $moe->error() ) {
				$moe = false;
			}
		}

		$this->loaded_files[ $translation_file ][ $textdomain ] = $moe;

		if ( ! $moe instanceof Ginger_MO_Translation_File ) {
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
	 * @param string                            $textdomain Text domain.
	 * @param Ginger_MO_Translation_File|string $mo         Translation file instance or file name.
	 * @return bool True on success, false otherwise.
	 */
	public function unload( string $textdomain = 'default', $mo = null ): bool {
		if ( ! $this->is_loaded( $textdomain ) ) {
			return false;
		}

		if ( null !== $mo ) {
			foreach ( $this->loaded_translations[ $textdomain ] as $i => $moe ) {
				if ( $mo === $moe || $mo === $moe->get_file() ) {
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
	public function is_loaded( string $textdomain = 'default' ): bool {
		return isset( $this->loaded_translations[ $textdomain ] ) && array() !== $this->loaded_translations[ $textdomain ];
	}

	/**
	 * Translates a singular string.
	 *
	 * @param string      $text Text to translate.
	 * @param string|null $context Optional. Context for the string.
	 * @param string      $textdomain Text domain.
	 * @return string|false Translation on success, false otherwise.
	 */
	public function translate( string $text, $context = null, string $textdomain = 'default' ) {
		if ( null !== $context && '' !== $context ) {
			$context .= "\4";
		}

		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( false === $translation ) {
			return false;
		}

		return $translation['entries'][0];
	}

	/**
	 * Translates plurals.
	 *
	 * Checks both singular+plural combinations as well as just singulars,
	 * in case the translation file does not store the plural.
	 *
	 * @todo Revisit this.
	 *
	 * @param array{0: string, 1: string} $plurals Pair of singular and plural translation.
	 * @param int                         $number Number of items.
	 * @param string|null                 $context Optional. Context for the string.
	 * @param string                      $textdomain Text domain.
	 * @return string|false Translation on success, false otherwise.
	 */
	public function translate_plural( $plurals, $number, $context = null, string $textdomain = 'default' ) {
		if ( null !== $context && '' !== $context ) {
			$context .= "\4";
		}

		$text        = implode( "\0", $plurals );
		$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

		if ( false === $translation ) {
			$text        = $plurals[0];
			$translation = $this->locate_translation( "{$context}{$text}", $textdomain );

			if ( false === $translation ) {
				return false;
			}
		}

		/* @var Ginger_MO_Translation_File $source */
		$source = $translation['source'];
		$num    = $source->get_plural_form( $number );

		return $translation['entries'][ $num ];
	}

	/**
	 * Returns all existing headers for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return array<string, string> Headers.
	 */
	public function get_headers( string $textdomain = 'default' ): array {
		if ( array() === $this->loaded_translations ) {
			return array();
		}

		$headers = array();

		foreach ( $this->get_files( $textdomain ) as $moe ) {
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
	protected function normalize_header( string $header ): string {
		$parts = explode( '-', $header );
		$parts = array_map( 'ucfirst', $parts );
		return implode( '-', $parts );
	}

	/**
	 * Returns all entries for a given text domain.
	 *
	 * @param string $textdomain Text domain.
	 * @return array<string, string> Entries.
	 */
	public function get_entries( string $textdomain = 'default' ): array {
		if ( array() === $this->loaded_translations ) {
			return array();
		}

		$entries = array();

		foreach ( $this->get_files( $textdomain ) as $moe ) {
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
	 * @return array{source: Ginger_MO_Translation_File, entries: string[]}|false Translations on success, false otherwise.
	 */
	protected function locate_translation( string $singular, string $textdomain = 'default' ) {
		if ( array() === $this->loaded_translations ) {
			return false;
		}

		// Find the translation in all loaded files for this text domain.
		foreach ( $this->get_files( $textdomain ) as $moe ) {
			$translation = $moe->translate( $singular );
			if ( false !== $translation ) {
				return array(
					'entries' => explode( "\0", $translation ),
					'source'  => $moe,
				);
			}
			if ( false !== $moe->error() ) {
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
	protected function get_files( string $textdomain = 'default' ): array {
		if ( isset( $this->loaded_translations[ $textdomain ] ) ) {
			return $this->loaded_translations[ $textdomain ];
		}

		return array();
	}
}
