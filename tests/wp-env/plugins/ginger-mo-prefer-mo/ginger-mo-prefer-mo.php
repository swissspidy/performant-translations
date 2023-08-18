<?php
/**
 * Plugin Name: Ginger MO Prefer MO
 * Description: Prefer MO file format.
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_filter(
	'performant_translations_preferred_format',
	static function () {
		return 'mo';
	}
);
