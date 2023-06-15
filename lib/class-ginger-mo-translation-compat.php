<?php
// Compatibility & Implementation for WordPress
class Ginger_MO_Translation_Compat {
	public static function load_textdomain( $return, $domain, $mofile ) {
		global $l10n;
		do_action( 'load_textdomain', $domain, $mofile );
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		$success = Ginger_MO::instance()->load( $mofile, $domain );

		if ( $success ) {
			$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );
		}

		return $success;
	}

	public static function unload_textdomain( $return, $domain ) {
		global $l10n;

		do_action( 'unload_textdomain', $domain );

		unset( $l10n[ $domain ] );
		return Ginger_MO::instance()->unload( $domain );
	}

	public static function overwrite_wordpress() {
		global $l10n;

		$l10n = new Ginger_MO_Translation_Compat();

		add_filter( 'override_unload_textdomain', array( __CLASS__, 'unload_textdomain' ), 10, 2 );
		add_filter( 'override_load_textdomain', array( __CLASS__, 'load_textdomain' ), 10, 3 );
	}
}
