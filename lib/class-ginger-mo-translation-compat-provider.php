<?php
class Ginger_MO_Translation_Compat_Provider {
	/**
	 * Text domain.
	 *
	 * @var string
	 */
	protected $textdomain = 'default';

	/**
	 * Constructor.
	 *
	 * @param string $textdomain Text domain.
	 */
	public function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
	}

	/**
	 * Magic getter for backward compatibility.
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( 'entries' === $name ) {
			return Ginger_MO::instance()->get_entries( $this->textdomain );
		}

		if ( 'headers' === $name ) {
			return Ginger_MO::instance()->get_headers( $this->textdomain );
		}

		return null;
	}

	/**
	 * Translates a plural string.
	 *
	 * @param string $singular Singular translation.
	 * @param string $plural Plural translation.
	 * @param int    $count Count.
	 * @param string $context Context.
	 * @return string Translation.
	 */
	public function translate_plural( $singular, $plural, $count = 1, $context = '' ) {
		$translation = Ginger_MO::instance()->translate_plural( array( $singular, $plural ), $count, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original with English grammar rules.
		return ( 1 === $count ? $singular : $plural );
	}

	/**
	 * Translates a singular string.
	 *
	 * @param string $singular Singular translation.
	 * @param string $context Context.
	 * @return string Translation.
	 */
	public function translate( $singular, $context = '' ) {
		$translation = Ginger_MO::instance()->translate( $singular, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original.
		return $singular;
	}
}
