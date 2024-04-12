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
	 * Hook into WordPress.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'load_translation_file', array( __CLASS__, 'load_translation_file' ), 100, 2 );

		add_action( 'wp_head', array( __CLASS__, 'add_generator_tag' ) );
		add_action( 'performant_translations_file_written', array( __CLASS__, 'opcache_invalidate' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'upgrader_process_complete' ), 10, 2 );

		// Plugin integrations.
		// TODO: Remove once these plugins do this themselves.
		add_action( 'loco_file_written', array( __CLASS__, 'regenerate_translation_file' ) );
		add_action( 'wpml_st_translation_file_updated', array( __CLASS__, 'regenerate_translation_file' ) );
	}

	/**
	 * Invalidates OPCache for a given file upon write/modification.
	 *
	 * @param string $file File path.
	 * @return void
	 */
	public static function opcache_invalidate( string $file ) {
		wp_opcache_invalidate( $file );
	}

	/**
	 * Filters the file path for loading translations for the given text domain.
	 *
	 * Similar to the {@see 'load_textdomain_mofile'} filter with the difference that
	 * the file path could be for an MO or PHP file.
	 *
	 * @since 6.5.0
	 *
	 * @param string $file   Path to the translation file to load.
	 * @param string $domain The text domain.
	 * @return string Unfiltered path.
	 */
	public static function load_translation_file( $file, $domain ) {
		/**
		 * WP filesystem subclass.
		 *
		 * @var WP_Filesystem_Base $wp_filesystem WP filesystem subclass.
		 */
		global $wp_filesystem;

		if ( ! str_ends_with( $file, '.mo' ) ) {
			return $file;
		}

		/** This filter is documented in lib/class-performant-translations.php */
		$convert = apply_filters( 'performant_translations_convert_files', true );

		if ( ! $convert ) {
			return $file;
		}

		/** This filter is documented in wp-includes/l10n.php */
		$preferred_format = apply_filters( 'translation_file_format', 'php', $domain ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		if ( ! in_array( $preferred_format, array( 'php', 'mo' ), true ) ) {
			$preferred_format = 'php';
		}

		if ( 'php' !== $preferred_format ) {
			return $file;
		}

		$preferred_file = substr_replace( $file, '.l10n.php', - strlen( '.mo' ) );

		if ( file_exists( $preferred_file ) ) {
			return $file;
		}

		$contents = WP_Translation_File::transform( $file, $preferred_format );

		if ( false !== $contents ) {
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
			}

			$modir = dirname( $file );

			$write_success = false;

			if ( true === WP_Filesystem() ) {
				$write_success = $wp_filesystem->put_contents( $preferred_file, $contents, FS_CHMOD_FILE );
			} else {
				if ( is_writable( $modir ) ) {
					$write_success = (bool) file_put_contents( $preferred_file, $contents, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				}
			}

			// If file creation within wp-content/plugins or wp-content/themes failed,
			// try creating it in wp-content/languages instead.
			// See https://github.com/swissspidy/performant-translations/issues/108.
			if ( ! $write_success ) {
				$new_location = '';

				if ( str_contains( $modir, WP_PLUGIN_DIR ) ) {
					$new_location = WP_LANG_DIR . '/plugins/' . $preferred_file;
				} elseif ( str_contains( $modir, basename( get_stylesheet_directory() ) ) ) {
					$new_location = WP_LANG_DIR . '/themes/' . basename( get_stylesheet_directory() ) . '-' . $preferred_file;
				}

				if ( '' !== $new_location ) {
					if ( true === WP_Filesystem() ) {
						$wp_filesystem->put_contents( $new_location, $contents, FS_CHMOD_FILE );
					} else {
						if ( is_writable( $modir ) ) {
							(bool) file_put_contents( $new_location, $contents, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
						}
					}
				}
			}
		}

		return $file;
	}

	/**
	 * Creates PHP translation files after the translation updates process.
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WP filesystem subclass.
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
		/**
		 * WP filesystem subclass.
		 *
		 * @var WP_Filesystem_Base $wp_filesystem WP filesystem subclass.
		 */
		global $wp_filesystem;

		if ( 'translation' !== $hook_extra['type'] || array() === $hook_extra['translations'] ) {
			return;
		}

		foreach ( $hook_extra['translations'] as $translation ) {
			$files = array();
			switch ( $translation['type'] ) {
				case 'plugin':
					$files[] = WP_LANG_DIR . '/plugins/' . $translation['slug'] . '-' . $translation['language'] . '.mo';
					break;
				case 'theme':
					$files[] = WP_LANG_DIR . '/themes/' . $translation['slug'] . '-' . $translation['language'] . '.mo';
					break;
				default:
					$files[] = WP_LANG_DIR . '/' . $translation['language'] . '.mo';
					$files[] = WP_LANG_DIR . '/admin-' . $translation['language'] . '.mo';
					$files[] = WP_LANG_DIR . '/admin-network-' . $translation['language'] . '.mo';
					$files[] = WP_LANG_DIR . '/continents-cities-' . $translation['language'] . '.mo';
					break;
			}

			foreach ( $files as $file ) {
				if ( file_exists( $file ) ) {
					/** This filter is documented in lib/class-performant-translations.php */
					$preferred_format = apply_filters( 'performant_translations_preferred_format', 'php' );
					if ( ! in_array( $preferred_format, array( 'php', 'mo' ), true ) ) {
						$preferred_format = 'php';
					}

					/** This filter is documented in wp-includes/l10n.php */
					$preferred_format = apply_filters( 'translation_file_format', $preferred_format, $translation['slug'] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

					$mofile_preferred = $file;

					if ( 'mo' !== $preferred_format ) {
						$mofile_preferred = substr_replace( $file, ".l10n.$preferred_format", -strlen( '.mo' ) );
					}

					/** This filter is documented in lib/class-performant-translations.php */
					$convert = apply_filters( 'performant_translations_convert_files', true );

					if ( 'mo' !== $preferred_format && $convert ) {
						$contents = WP_Translation_File::transform( $file, $preferred_format );

						if ( false === $contents ) {
							return;
						}

						if ( true === $upgrader->fs_connect( array( dirname( $file ) ) ) ) {
							$file_written = $wp_filesystem->put_contents( $mofile_preferred, $contents, FS_CHMOD_FILE );
						} else {
							$file_written = (bool) file_put_contents( $mofile_preferred, $contents, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
						}

						if ( $file_written ) {
							/** This action is documented in lib/class-performant-translations.php */
							do_action( 'performant_translations_file_written', $mofile_preferred );
						}
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
		echo '<meta name="generator" content="performant-translations ' . esc_attr( PERFORMANT_TRANSLATIONS_VERSION ) . '">' . "\n";
	}

	/**
	 * Regenerates a PHP translation file from a given MO file.
	 *
	 * Useful for plugins such as Loco Translate or WPML which generate custom MO files.
	 * Prevents stale PHP files in those cases.
	 *
	 * This compatibility code is added out of courtesy and is not intended
	 * to be merged into WordPress core.
	 *
	 * @codeCoverageIgnore
	 *
	 * @global WP_Filesystem_Base $wp_filesystem WP filesystem subclass.
	 *
	 * @param string $file Path to translation file.
	 * @return void
	 */
	public static function regenerate_translation_file( string $file ) {
		/**
		 * WP filesystem subclass.
		 *
		 * @var WP_Filesystem_Base $wp_filesystem WP filesystem subclass.
		 */
		global $wp_filesystem;

		if ( ! str_ends_with( $file, '.mo' ) ) {
			return;
		}

		/** This filter is documented in lib/class-performant-translations.php */
		$preferred_format = apply_filters( 'performant_translations_preferred_format', 'php' );

		if ( ! in_array( $preferred_format, array( 'php', 'mo' ), true ) ) {
			$preferred_format = 'php';
		}

		$mofile_preferred = $file;

		if ( 'mo' !== $preferred_format ) {
			$mofile_preferred = substr_replace( $file, ".l10n.$preferred_format", -strlen( '.mo' ) );
		}

		/** This filter is documented in lib/class-performant-translations.php */
		$convert = apply_filters( 'performant_translations_convert_files', true );

		if ( 'mo' !== $preferred_format && $convert ) {
			$contents = WP_Translation_File::transform( $file, $preferred_format );

			if ( false !== $contents ) {
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
				}

				if ( true === WP_Filesystem() ) {
					$file_written = $wp_filesystem->put_contents( $mofile_preferred, $contents, FS_CHMOD_FILE );
				} else {
					$file_written = (bool) file_put_contents( $mofile_preferred, $contents, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				}

				if ( $file_written ) {
					/** This action is documented in lib/class-performant-translations.php */
					do_action( 'performant_translations_file_written', $mofile_preferred );
				}
			}
		}
	}
}
