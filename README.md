# Ginger MO

[![Commit activity](https://img.shields.io/github/commit-activity/m/swissspidy/ginger-mo)](https://github.com/swissspidy/ginger-mo/pulse/monthly)
[![Code Coverage](https://codecov.io/gh/swissspidy/ginger-mo/branch/main/graph/badge.svg)](https://codecov.io/gh/swissspidy/ginger-mo)
[![License](https://img.shields.io/github/license/swissspidy/ginger-mo)](https://github.com/swissspidy/ginger-mo/blob/main/LICENSE)

A minimal `.mo` reader (with support for PHP & JSON representations), multiple text domains, and multiple loaded locales in the future.

## Description

Ginger MO is a lightweight PHP library to read `.mo`, `.php`, and `.json` translation files.

While the library itself is platform-agnostic, it has been developed with WordPress in mind. Thus, it can be easily installed as a WordPress plugin.

Real world tests show that Ginger MO is much faster at loading translations than the built-in localization system in WordPress core.

The following numbers are for a site running 6.3 alpha with multiple active plugins.

| Solution              | Median load time | Memory usage |
|-----------------------|------------------|--------------|
| No Localization       | 120 ms           | 16.7 MB      |
| Default Localization  | 161 ms           | 33.9 MB      |
| Ginger MO, MO files   | 142 ms           | 21.1 MB      |
| Ginger MO, PHP files  | 127 ms           | 17.3 MB      |
| Ginger MO, JSON files | 141 ms           | 20.2 MB      |

## Credits

Originally developed by [Dion Hulse](https://github.com/dd32/ginger-mo).
