=== Performant Translations ===

Contributors:      swissspidy, dd32
Tested up to:      6.3
Stable tag:        0.1.0
License:           GPL-2.0+
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Tags:              performance, i18n, translations, localization, internationalization

A feature project to make the internationalization (i18n) system in WordPress faster than ever before.

== Description ==

This project uses Ginger MO, a lightweight PHP library to read `.mo`, `.php`, and `.json` translation files in WordPress.
It supports multiple text domains and multiple loaded locales.

Real world tests show that this plugin is much faster at loading translations than the built-in localization system in WordPress core.

The primary purpose of this plugin is to allow broader testing of these enhancements, for which the goal is to eventually land in WordPress core.

== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **Performant Translations**.
3. Install and activate the Performant Translations plugin.

= Manual installation =

1. Upload the entire `performant-translations` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Performant Translations plugin.

== Changelog ==

For the plugin's full changelog, please see [the Releases page on GitHub](https://github.com/swissspidy/performant-translations/releases).

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.0 =

Initial release.
