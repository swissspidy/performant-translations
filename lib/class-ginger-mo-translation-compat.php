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
	 * @param bool   $override Whether to override. Unused.
	 * @param string $domain Text domain.
	 * @param string $mofile File name.
	 * @return bool True on success, false otherwise.
	 */
	public static function load_textdomain( $override, $domain, $mofile ) {
		global $l10n;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'load_textdomain', $domain, $mofile );

		/** This filter is documented in wp-includes/l10n.php */
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		/**
		 * Filters the preferred file format for translation files.
		 *
		 * Useful for testing/debugging.
		 *
		 * @param string $convert Preferred file format. Possible values: 'php', 'mo', 'json'. Default: 'php'.
		 */
		$preferred_format = apply_filters( 'ginger_mo_preferred_format', 'php' );
		if ( ! in_array( $preferred_format, array( 'php', 'mo', 'json' ), true ) ) {
			$preferred_format = 'php';
		}

		$mofile_preferred = str_replace( '.mo', ".$preferred_format", $mofile );

		if ( 'mo' !== $preferred_format ) {
			$success = Ginger_MO::instance()->load( $mofile_preferred, $domain );

			if ( $success ) {
				// Unset Noop_Translations reference in get_translations_for_domain.
				unset( $l10n[ $domain ] );
				$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );

				return $success;
			}
		}

		$success = Ginger_MO::instance()->load( $mofile, $domain );

		if ( $success ) {
			// Unset Noop_Translations reference in get_translations_for_domain.
			unset( $l10n[ $domain ] );
			$l10n[ $domain ] = new Ginger_MO_Translation_Compat_Provider( $domain );

			/**
			 * Filters whether existing MO files should be automatically converted to the preferred format.
			 *
			 * Only runs when no corresponding PHP or JSON translation file exists yet.
			 *
			 * The preferred format is determined by the {@see 'ginger_mo_prefer_php_files'} filter
			 *
			 * Useful for testing/debugging.
			 *
			 * @param bool $convert Whether to convert MO files to PHP files. Default true.
			 */
			$convert = apply_filters( 'ginger_mo_convert_files', true );

			if ( 'mo' !== $preferred_format && $convert ) {
				$source      = Ginger_MO_Translation_File::create( $mofile );
				$destination = Ginger_MO_Translation_File::create( $mofile_preferred, 'write' );
				if ( false !== $source && false !== $destination ) {
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
	 * @return bool True on success, false otherwise.
	 */
	public static function unload_textdomain( $override, $domain ) {
		global $l10n;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'unload_textdomain', $domain );

		unset( $l10n[ $domain ] );
		return Ginger_MO::instance()->unload( $domain );
	}

	/**
	 * Hook into WordPress.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function overwrite_wordpress() {
		add_filter( 'override_unload_textdomain', array( __CLASS__, 'unload_textdomain' ), 100, 2 );
		add_filter( 'override_load_textdomain', array( __CLASS__, 'load_textdomain' ), 100, 3 );
	}
}
