<?php
/**
 * Plugin Name: Performant Translations
 * Plugin URI:  https://github.com/swissspidy/performant-translations
 * Description: Making internationalization/localization in WordPress faster than ever before.
 * Version:     1.2.0
 * Network:     true
 * Author:      WordPress Performance Team
 * Author URI:  https://make.wordpress.org/performance/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: performant-translations
 * Requires at least: 6.5
 * Requires PHP: 7.0
 *
 * @package Performant_Translations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PERFORMANT_TRANSLATIONS_VERSION', '1.2.0' );

require __DIR__ . '/lib/class-performant-translations.php';

// All the WordPress magic.
Performant_Translations::init();
