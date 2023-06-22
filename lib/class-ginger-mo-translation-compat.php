<?php
/**
 * Compatibility & Implementation for WordPress.
 *
 * @package Ginger_MO
 */

/**
 * Class Ginger_MO_Translation_Compat.
 */
class Ginger_MO_Translation_Compat {
	/**
	 * Loads a text domain.
	 *
	 * @param bool        $override Whether to override the .mo file loading.
	 * @param string      $domain   Text domain. Unique identifier for retrieving translated strings.
	 * @param string      $mofile   Path to the MO file.
	 * @param string|null $locale   Locale.
	 * @return bool True on success, false otherwise.
	 */
	public static function load_textdomain( $override, $domain, $mofile, $locale ) {
		global $l10n;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		if ( ! $locale ) {
			$locale = determine_locale();
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'load_textdomain', $domain, $mofile );

		/** This filter is documented in wp-includes/l10n.php */
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		$php_mo = str_replace( '.mo', '.php', $mofile );

		$success = Ginger_MO::instance()->load( $php_mo, $domain, $locale );

		if ( $success ) {
			// Unset Noop_Translations reference in get_translations_for_domain.
			unset( $l10n[ $domain ] );
			$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );

			return $success;
		}

		$success = Ginger_MO::instance()->load( $mofile, $domain, $locale );

		if ( $success ) {
			// Unset Noop_Translations reference in get_translations_for_domain.
			unset( $l10n[ $domain ] );
			$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );

			/**
			 * Filters whether existing MO files should be automatically converted to PHP files.
			 *
			 * Only runs when no corresponding PHP translation file exists yet.
			 *
			 * Useful for testing/debugging.
			 *
			 * @param bool $convert Whether to convert MO files to PHP files. Default true.
			 */
			$convert = apply_filters( 'ginger_mo_convert_to_php_files', true );

			if ( $convert ) {
				$source      = Ginger_MO_Translation_File::create( $mofile );
				$destination = Ginger_MO_Translation_File::create( $php_mo, 'write' );
				if ( $source && $destination ) {
					$source->export( $destination );
				}
			}
		}

		return $success;
	}

	/**
	 * Unloads text domain.
	 *
	 * @param bool   $override Whether to override. Unused.
	 * @param string $domain Text domain.
	 * @param bool   $reloadable Whether the text domain can be loaded just-in-time again.
	 * @return bool True on success, false otherwise.
	 */
	public static function unload_textdomain( $override, $domain, $reloadable ) {
		global $l10n;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		// Since we support multiple locales, we don't actually need to unload
		// reloadable text domains.
		if ( $reloadable ) {
			return true;
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'unload_textdomain', $domain );

		unset( $l10n[ $domain ] );
		return Ginger_MO::instance()->unload( $domain );
	}

	/**
	 * Updates the locale whenever it is changed in WordPress.
	 *
	 * @param string $locale The new locale.
	 * @return void
	 */
	public static function change_locale( $locale ) {
		Ginger_MO::instance()->set_locale( $locale );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public static function overwrite_wordpress() {
		add_filter( 'override_load_textdomain', array( __CLASS__, 'load_textdomain' ), 100, 4 );
		add_filter( 'override_unload_textdomain', array( __CLASS__, 'unload_textdomain' ), 100, 3 );

		add_action( 'change_locale', array( __CLASS__, 'change_locale' ) );
	}
}
