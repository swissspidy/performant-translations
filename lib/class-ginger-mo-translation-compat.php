<?php
// Compatibility & Implementation for WordPress
class Ginger_MO_Translation_Compat {
	/**
	 * @param bool   $override Whether to override. Unused.
	 * @param string $domain Text domain.
	 * @param string $mofile File name.
	 * @return bool True on success, false otherwise.
	 */
	public static function load_textdomain( $override, $domain, $mofile ) {
		global $l10n;
		do_action( 'load_textdomain', $domain, $mofile );
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		$success = Ginger_MO::instance()->load( $mofile, $domain );

		if ( $success ) {
			$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );
		}

		return $success;
	}

	/**
	 * @param bool   $override Whether to override. Unused.
	 * @param string $domain Text domain.
	 * @return bool True on success, false otherwise.
	 */
	public static function unload_textdomain( $override, $domain ) {
		global $l10n;

		do_action( 'unload_textdomain', $domain );

		unset( $l10n[ $domain ] );
		return Ginger_MO::instance()->unload( $domain );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function overwrite_wordpress() {
		add_filter( 'override_unload_textdomain', array( __CLASS__, 'unload_textdomain' ), 10, 2 );
		add_filter( 'override_load_textdomain', array( __CLASS__, 'load_textdomain' ), 10, 3 );
	}
}
