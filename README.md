# Performant Translations (Ginger MO)

[![Commit activity](https://img.shields.io/github/commit-activity/m/swissspidy/performant-translations)](https://github.com/swissspidy/performant-translations/pulse/monthly)
[![Code Coverage](https://codecov.io/gh/swissspidy/performant-translations/branch/main/graph/badge.svg)](https://codecov.io/gh/swissspidy/performant-translations)
[![License](https://img.shields.io/github/license/swissspidy/performant-translations)](https://github.com/swissspidy/performant-translations/blob/main/LICENSE)

Making internationalization/localization in WordPress faster than ever before.

## Description

This project uses a new approach to handle translation files in WordPress, making localization blazing fast.

An [in-depth i18n performance analysis](https://make.wordpress.org/core/2023/07/24/i18n-performance-analysis/) showed
that localized WordPress sites load significantly slower than a site without translations.

With this plugin's new approach to localization, this overhead is massively reduced, making your site fast again.

The primary purpose of this plugin is to allow broader testing of these enhancements, for which the goal is to eventually land in WordPress core.

Performant Translations supports multiple file formats (`.mo`, `.php`, and `.json`), as well as multiple text domains and locales loaded at the same time.
By default, it converts existing `.mo` files to `.php` and afterwards only loads the translations from the `.php` file.

<details>
<summary><h3>Frequently Asked Questions</h3></summary>

#### What makes this plugin so fast?

By converting `.mo` files to `.php` files, the translations can be parsed much faster.
Plus, `.php` files can be stored in the so-called [OPcache](https://www.php.net/manual/en/book.opcache.php), which provides an additional speed boost.

#### Can I use this plugin on my production site?

While the plugin is mostly considered to be a beta testing plugin, it has been tested and established to a degree where it should be okay to use in production.
Still, as with every plugin, you are doing so at your own risk.

#### Do I need to do anything special to enable this plugin?

No. Once the plugin is activated, it just works. If you run into issues, please open a new support topic.

#### Can I safely remove this plugin after installation?

Yes. Once you deactivate and uninstall the plugin, all `.php` files generated by it will be removed from the server.

#### How can I contribute to the plugin?

Contributions are always welcome! Learn more about how to get involved in the [Core Performance Team Handbook](https://make.wordpress.org/performance/handbook/get-involved/).

#### Where can I submit my plugin feedback?

If you have suggestions or requests for new features, you can submit them as an issue on the [GitHub repository](https://github.com/swissspidy/performant-translations).

If you need help with troubleshooting or have a question about the plugin, please [create a new topic on our support forum](https://wordpress.org/support/plugin/performant-translations/#new-topic-0).

</details>

### Benchmarks

The following numbers are for a site running WordPress trunk (6.4 alpha) with multiple active plugins.

**Twenty Twenty-Three**

| Locale |        Scenario         | Memory Usage | Load Time |
|:-------|:-----------------------:|:------------:|:---------:|
| en_US  |         Default         |   14.86 MB   | 141.15 ms |
| de_DE  |         Default         |   28.29 MB   | 191.22 ms |
| de_DE  | Performant Translations |   16.02 MB   | 148.35 ms |

**Twenty Twenty-One**

| Locale |        Scenario         | Memory Usage | Load Time |
|:-------|:-----------------------:|:------------:|:---------:|
| en_US  |         Default         |   14.46 MB   | 124.66 ms |
| de_DE  |         Default         |   27.96 MB   | 173.44 ms |
| de_DE  | Performant Translations |   15.62 MB   | 132.60 ms |

**WordPress Admin**

| Locale |        Scenario         | Memory Usage | Load Time |
|:-------|:-----------------------:|:------------:|:---------:|
| en_US  |         Default         |   15.74 MB   | 171.44 ms |
| de_DE  |         Default         |   32.35 MB   | 220.61 ms |
| de_DE  | Performant Translations |   17.36 MB   | 172.41 ms |

## Credits

Ginger MO was originally developed by [Dion Hulse](https://github.com/dd32/ginger-mo).
