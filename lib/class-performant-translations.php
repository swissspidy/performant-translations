<?php
/**
 * Compatibility & Implementation for WordPress.
 *
 * @package Performant_Translations
 */

/**
 * Class Performant_Translations.
 */
class Performant_Translations {
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
		global $l10n, $wp_textdomain_registry;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		if ( null === $locale ) {
			$locale = determine_locale();
		}

		// Ensures the correct locale is set as the current one,
		// even if the "locale" filter is used in WordPress to change
		// the locale.
		Ginger_MO::instance()->set_locale( $locale );

		/**
		 * Filters the preferred file format for translation files.
		 *
		 * Useful for testing/debugging.
		 *
		 * @param string $convert Preferred file format. Possible values: 'php', 'mo', 'json'. Default: 'php'.
		 */
		$preferred_format = apply_filters( 'performant_translations_preferred_format', 'php' );
		if ( ! in_array( $preferred_format, array( 'php', 'mo', 'json' ), true ) ) {
			$preferred_format = 'php';
		}

		$mofile_preferred = str_replace( '.mo', ".$preferred_format", $mofile );

		if ( 'mo' !== $preferred_format ) {
			/** This action is documented in wp-includes/l10n.php */
			do_action( 'load_textdomain', $domain, $mofile_preferred );

			/** This filter is documented in wp-includes/l10n.php */
			$mofile_preferred = apply_filters( 'load_textdomain_mofile', $mofile_preferred, $domain );

			/**
			 * Filters the file path for loading translations for the given text domain.
			 *
			 * The file could be an MO, JSON, or PHP file.
			 *
			 * @since 1.0.3
			 *
			 * @param string $file   Path to the translation file to load.
			 * @param string $domain The text domain.
			 */
			$mofile_preferred = apply_filters( 'performant_translations_load_translation_file', $mofile_preferred, $domain );

			$success = Ginger_MO::instance()->load( $mofile_preferred, $domain, $locale );

			if ( $success ) {
				// Unset Noop_Translations reference in get_translations_for_domain.
				unset( $l10n[ $domain ] );
				$l10n[ $domain ] = new Performant_Translations_Compat_Provider( $domain );

				$wp_textdomain_registry->set( $domain, $locale, dirname( $mofile ) );

				return true;
			}
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'load_textdomain', $domain, $mofile );

		/** This filter is documented in wp-includes/l10n.php */
		$mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

		/** This filter is documented in lib/class-performant-translations.php */
		$mofile = apply_filters( 'performant_translations_load_translation_file', $mofile, $domain );

		$success = Ginger_MO::instance()->load( $mofile, $domain, $locale );

		if ( $success ) {
			// Unset Noop_Translations reference in get_translations_for_domain.
			unset( $l10n[ $domain ] );

			$l10n[ $domain ] = new Performant_Translations_Compat_Provider( $domain );

			$wp_textdomain_registry->set( $domain, $locale, dirname( $mofile ) );

			/**
			 * Filters whether existing MO files should be automatically converted to the preferred format.
			 *
			 * Only runs when no corresponding PHP or JSON translation file exists yet.
			 *
			 * The preferred format is determined by the {@see 'performant_translations_prefer_php_files'} filter
			 *
			 * Useful for testing/debugging.
			 *
			 * @param bool $convert Whether to convert MO files to PHP files. Default true.
			 */
			$convert = apply_filters( 'performant_translations_convert_files', true );

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
	 * @param bool   $reloadable Whether the text domain can be loaded just-in-time again.
	 * @return bool True on success, false otherwise.
	 */
	public static function unload_textdomain( $override, $domain, $reloadable ) {
		global $l10n;

		// Another override is already in progress, prevent conflicts.
		if ( $override ) {
			return $override;
		}

		/** This action is documented in wp-includes/l10n.php */
		do_action( 'unload_textdomain', $domain );

		unset( $l10n[ $domain ] );

		// Since we support multiple locales, we don't actually need to unload
		// reloadable text domains.
		if ( ! $reloadable && 'default' !== $domain ) {
			return Ginger_MO::instance()->unload( $domain );
		}

		return true;
	}

	/**
	 * Sets the current locale on init.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function set_locale() {
		Ginger_MO::instance()->set_locale( determine_locale() );
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
	 * Creates PHP translation files after the translation updates process.
	 *
	 * @since 0.0.1
	 *
	 * @param WP_Upgrader $upgrader   WP_Upgrader instance. In other contexts this might be a
	 *                                Theme_Upgrader, Plugin_Upgrader, Core_Upgrade, or Language_Pack_Upgrader instance.
	 * @param array       $hook_extra {
	 *     Array of bulk item update data.
	 *
	 *     @type string $action       Type of action. Default 'update'.
	 *     @type string $type         Type of update process. Accepts 'plugin', 'theme', 'translation', or 'core'.
	 *     @type bool   $bulk         Whether the update process is a bulk update. Default true.
	 *     @type array  $plugins      Array of the basename paths of the plugins' main files.
	 *     @type array  $themes       The theme slugs.
	 *     @type array  $translations {
	 *         Array of translations update data.
	 *
	 *         @type string $language The locale the translation is for.
	 *         @type string $type     Type of translation. Accepts 'plugin', 'theme', or 'core'.
	 *         @type string $slug     Text domain the translation is for. The slug of a theme/plugin or
	 *                                'default' for core translations.
	 *         @type string $version  The version of a theme, plugin, or core.
	 *     }
	 * }
	 * @return void
	 *
	 * @phpstan-param array{action: string, type: string, bulk: bool, plugins: string[], themes: string[], translations: array<int, array{language: string, type: string, slug: string, version: string}>} $hook_extra
	 */
	public static function upgrader_process_complete( $upgrader, $hook_extra ) {
		if ( 'translation' !== $hook_extra['type'] || array() === $hook_extra['translations'] ) {
			return;
		}

		foreach ( $hook_extra['translations'] as $translation ) {
			switch ( $translation['type'] ) {
				case 'plugin':
					$file = WP_LANG_DIR . '/plugins/' . $translation['slug'] . '-' . $translation['language'] . '.mo';
					break;
				case 'theme':
					$file = WP_LANG_DIR . '/themes/' . $translation['slug'] . '-' . $translation['language'] . '.mo';
					break;
				default:
					$file = WP_LANG_DIR . '/' . $translation['language'] . '.mo';
					break;
			}

			if ( file_exists( $file ) ) {
				/** This filter is documented in lib/class-performant-translations.php */
				$preferred_format = apply_filters( 'performant_translations_preferred_format', 'php' );
				if ( ! in_array( $preferred_format, array( 'php', 'mo', 'json' ), true ) ) {
					$preferred_format = 'php';
				}

				$mofile_preferred = str_replace( '.mo', ".$preferred_format", $file );

				/** This filter is documented in lib/class-performant-translations.php */
				$convert = apply_filters( 'performant_translations_convert_files', true );

				if ( 'mo' !== $preferred_format && $convert ) {
					$source      = Ginger_MO_Translation_File::create( $file );
					$destination = Ginger_MO_Translation_File::create( $mofile_preferred, 'write' );
					if ( false !== $source && false !== $destination ) {
						$source->export( $destination );
					}
				}
			}
		}
	}

	/**
	 * Adds a <meta> generator tag for the plugin.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function add_generator_tag() {
		echo '<meta name="generator" content="Performant Translations ' . esc_attr( PERFORMANT_TRANSLATIONS_VERSION ) . '">' . "\n";
	}

	/**
	 * Regenerate preferred translation files when an MO file is updated in Loco Translate.
	 *
	 * This compatibility code is added out of courtesy and is not intended
	 * to be merged into WordPress core.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $file Path to translation file.
	 * @return void
	 */
	public static function regenerate_translation_file( string $file ) {
		if ( ! str_ends_with( $file, '.mo' ) ) {
			return;
		}

		/** This filter is documented in lib/class-performant-translations.php */
		$preferred_format = apply_filters( 'performant_translations_preferred_format', 'php' );
		if ( ! in_array( $preferred_format, array( 'php', 'mo', 'json' ), true ) ) {
			$preferred_format = 'php';
		}

		$mofile_preferred = str_replace( '.mo', ".$preferred_format", $file );

		/** This filter is documented in lib/class-performant-translations.php */
		$convert = apply_filters( 'performant_translations_convert_files', true );

		if ( 'mo' !== $preferred_format && $convert ) {
			$source      = Ginger_MO_Translation_File::create( $file );
			$destination = Ginger_MO_Translation_File::create( $mofile_preferred, 'write' );
			if ( false !== $source && false !== $destination ) {
				$source->export( $destination );
			}
		}
	}

	/**
	 * Hook into WordPress.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'override_load_textdomain', array( __CLASS__, 'load_textdomain' ), 100, 4 );
		add_filter( 'override_unload_textdomain', array( __CLASS__, 'unload_textdomain' ), 100, 3 );

		add_action( 'init', array( __CLASS__, 'set_locale' ) );
		add_action( 'change_locale', array( __CLASS__, 'change_locale' ) );

		add_action( 'upgrader_process_complete', array( __CLASS__, 'upgrader_process_complete' ), 10, 2 );

		add_action( 'wp_head', array( __CLASS__, 'add_generator_tag' ) );

		// Plugin integrations.
		add_action( 'loco_file_written', array( __CLASS__, 'regenerate_translation_file' ) );
	}
}
