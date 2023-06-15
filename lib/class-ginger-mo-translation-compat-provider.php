<?php

class Ginger_MO_Translation_Compat_Provider {
	protected $textdomain = 'default';

	public function __construct( $textdomain = 'default' ) {
		$this->textdomain = $textdomain;
	}

	public function translate_plural( $single, $plural, $number = 1, $context = '' ) {
		$translation = Ginger_MO::instance()->translate_plural( array( $single, $plural ), $number, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original with English grammar rules.
		return ( 1 === $number ? $single : $plural );
	}

	public function translate( $text, $context = '' ) {
		$translation = Ginger_MO::instance()->translate( $text, $context, $this->textdomain );
		if ( $translation ) {
			return $translation;
		}

		// Fall back to the original.
		return $text;
	}
}
