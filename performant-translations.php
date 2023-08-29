<?php
/**
 * Plugin Name: Performant Translations
 * Plugin URI:  https://github.com/swissspidy/performant-translations
 * Description: A feature project to make the internationalization (i18n) system in WordPress faster than ever before.
 * Version:     0.1.0
 * Author:      WordPress Performance Team
 * Author URI:  https://make.wordpress.org/performance/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: performant-translations
 * Requires at least: 6.3
 * Requires PHP: 7.0
 *
 * @package Performant_Translations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PERFORMANT_TRANSLATIONS_VERSION', '0.1.0' );

require __DIR__ . '/lib/class-ginger-mo.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-json.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-mo.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-php.php';
require __DIR__ . '/lib/class-performant-translations-compat-provider.php';
require __DIR__ . '/lib/class-performant-translations.php';

// All the WordPress magic.
Performant_Translations::init();
