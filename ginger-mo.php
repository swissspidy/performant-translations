<?php
/**
 * Plugin Name: Ginger MO
 * Plugin URI:  https://github.com/swissspidy/ginger-mo
 * Description: A minimal .mo reader (with support for PHP & JSON representations), multiple text domains, and multiple loaded locales in the future.
 * Version:     0.0.2
 * Author:      Pascal Birchler
 * Author URI:  https://pascalbirchler.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ginger-mo
 * Requires at least: 6.3
 * Requires PHP: 7.0
 *
 * @package Ginger_MO
 */

/*
 * Ginger-Mo is not-quite-gold, but almost.
 * It's a "lightweight" .mo reader for WordPress, it's designed to use the minimal memory and processing.
 * This is a POC plugin and includes the ability to hook into WordPress.
 */

require __DIR__ . '/lib/class-ginger-mo.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-json.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-mo.php';
require __DIR__ . '/lib/class-ginger-mo-translation-file-php.php';
require __DIR__ . '/lib/class-ginger-mo-translation-compat-provider.php';
require __DIR__ . '/lib/class-ginger-mo-translation-compat.php';

// All the WordPress magic.
Ginger_MO_Translation_Compat::overwrite_wordpress();
