<?php
/**
 * Plugin Name: Ginger MO No PHP
 * Description: Disable PHP file support in Ginger MO
 * Version: 0.1.0
 * Author: Pascal Birchler
 * Author URI: https://pascalbirchler.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

add_filter( 'ginger_mo_prefer_php_files', '__return_false' );
