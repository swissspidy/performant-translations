<?php
class Ginger_MO_Translation_Compat_Provider {
	protected $textdomain = 'default';
	public function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
	}

	public function __get( $name ) {
		if ( 'entries' === $name ) {
			return Ginger_MO::instance()->get_entries( $this->textdomain );
		}

		if ( 'headers' === $name ) {
			return Ginger_MO::instance()->get_headers( $this->textdomain );
		}

		return null;
	}

	public function translate_plural( $singular, $plural, $count = 1, $context = '' ) {
		$translation = Ginger_MO::instance()->translate_plural( array( $singular, $plural ), $count, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original with English grammar rules.
		return ( 1 === $count ? $singular : $plural );
	}

	public function translate( $singular, $context = '' ) {
		$translation = Ginger_MO::instance()->translate( $singular, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original.
		return $singular;
	}
}
