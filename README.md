# Performant Translations (Ginger MO)

[![Commit activity](https://img.shields.io/github/commit-activity/m/swissspidy/performant-translations)](https://github.com/swissspidy/performant-translations/pulse/monthly)
[![Code Coverage](https://codecov.io/gh/swissspidy/performant-translations/branch/main/graph/badge.svg)](https://codecov.io/gh/swissspidy/performant-translations)
[![License](https://img.shields.io/github/license/swissspidy/performant-translations)](https://github.com/swissspidy/performant-translations/blob/main/LICENSE)

A feature project to make the internationalization (i18n) system in WordPress faster than ever before.

## Description

This project uses Ginger MO, a lightweight PHP library to read `.mo`, `.php`, and `.json` translation files in WordPress.
It supports multiple text domains and multiple loaded locales.

Real world tests show that this plugin is much faster at loading translations than the built-in localization system in WordPress core.

The primary purpose of this plugin is to allow broader testing of these enhancements, for which the goal is to eventually land in WordPress core.

### Benchmarks

The following numbers are for a site running WordPress trunk (6.4 alpha) with multiple active plugins.

**Twenty Twenty-Three**

| Locale |     Scenario     | wp-memory-usage | wp-total  |
|:-------|:----------------:|:---------------:|:---------:|
| en_US  |     Default      |    14.86 MB     | 141.15 ms |
| de_DE  |     Default      |    28.29 MB     | 191.22 ms |
| de_DE  |  Ginger MO (MO)  |    18.30 MB     | 171.49 ms |
| de_DE  | Ginger MO (PHP)  |    16.02 MB     | 148.35 ms |
| de_DE  | Ginger MO (JSON) |    18.30 MB     | 165.39 ms |

**Twenty Twenty-One**

| Locale |     Scenario     | wp-memory-usage | wp-total  |
|:-------|:----------------:|:---------------:|:---------:|
| en_US  |     Default      |    14.46 MB     | 124.66 ms |
| de_DE  |     Default      |    27.96 MB     | 173.44 ms |
| de_DE  |  Ginger MO (MO)  |    17.92 MB     | 152.18 ms |
| de_DE  | Ginger MO (PHP)  |    15.62 MB     | 132.60 ms |
| de_DE  | Ginger MO (JSON) |    17.92 MB     | 146.56 ms |

**WordPress Admin**

| Locale |     Scenario     | wp-memory-usage | wp-total  |
|:-------|:----------------:|:---------------:|:---------:|
| en_US  |     Default      |    15.74 MB     | 171.44 ms |
| de_DE  |     Default      |    32.35 MB     | 220.61 ms |
| de_DE  |  Ginger MO (MO)  |    20.38 MB     | 194.42 ms |
| de_DE  | Ginger MO (PHP)  |    17.36 MB     | 172.41 ms |
| de_DE  | Ginger MO (JSON) |    20.37 MB     | 190.97 ms |

## Credits

Ginger MO was originally developed by [Dion Hulse](https://github.com/dd32/ginger-mo).
