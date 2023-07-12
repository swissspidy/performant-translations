# Ginger MO

[![Commit activity](https://img.shields.io/github/commit-activity/m/swissspidy/ginger-mo)](https://github.com/swissspidy/ginger-mo/pulse/monthly)
[![Code Coverage](https://codecov.io/gh/swissspidy/ginger-mo/branch/main/graph/badge.svg)](https://codecov.io/gh/swissspidy/ginger-mo)
[![License](https://img.shields.io/github/license/swissspidy/ginger-mo)](https://github.com/swissspidy/ginger-mo/blob/main/LICENSE)

A minimal `.mo` reader (with support for PHP & JSON representations), multiple text domains, and multiple loaded locales in the future.

## Description

Ginger MO is a lightweight PHP library to read `.mo`, `.php`, and `.json` translation files.

While the library itself is platform-agnostic, it has been developed with WordPress in mind. Thus, it can be easily installed as a WordPress plugin.

Real world tests show that Ginger MO is much faster at loading translations than the built-in localization system in WordPress core.

The following numbers are for a site running 6.3 Beta with multiple active plugins.

**Twenty Twenty-Three**

| Locale |       Scenario        | Object Cache | wp-memory-usage | wp-total  | TTFB      |
|:-------|:---------------------:|:------------:|:---------------:|:---------:|:----------|
| en_US  |        Default        |              |     5.74 MB     | 139.61 ms | 145.05 ms |
| de_DE  |        Default        |              |    12.74 MB     | 188.56 ms | 194.20 ms |
| de_DE  |    Ginger MO (MO)     |              |     7.58 MB     | 167.98 ms | 173.35 ms |
| de_DE  |    Ginger MO (PHP)    |              |     6.47 MB     | 146.23 ms | 151.55 ms |
| de_DE  |   Ginger MO (JSON)    |              |     7.58 MB     | 161.83 ms | 167.15 ms |
| de_DE  |    Native Gettext     |              |     5.82 MB     | 147.75 ms | 153.40 ms |
| de_DE  |        DynaMo         |              |     7.34 MB     | 164.17 ms | 169.85 ms |
| de_DE  |     Cache in APCu     |              |    23.89 MB     | 187.04 ms | 192.85 ms |
| en_US  |        Default        |      ✅       |     5.81 MB     | 128.12 ms | 133.60 ms |
| de_DE  |        Default        |      ✅       |    12.74 MB     | 179.25 ms | 185.20 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |     7.58 MB     | 159.45 ms | 165.30 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |     6.47 MB     | 138.03 ms | 143.80 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |     7.58 MB     | 154.23 ms | 160.05 ms |
| de_DE  |    Native Gettext     |      ✅       |     5.82 MB     | 138.91 ms | 144.65 ms |
| de_DE  |        DynaMo         |      ✅       |     7.42 MB     | 141.01 ms | 146.70 ms |
| de_DE  |     Cache in APCu     |      ✅       |    23.96 MB     | 180.63 ms | 186.85 ms |
| de_DE  | Cache in object cache |      ✅       |    12.75 MB     | 182.44 ms | 188.70 ms |

**Twenty Twenty-One**

| Locale |       Scenario        | Object Cache | wp-memory-usage | wp-total  | TTFB      |
|:-------|:---------------------:|:------------:|:---------------:|:---------:|:----------|
| en_US  |        Default        |              |     5.73 MB     | 124.66 ms | 129.65 ms |
| de_DE  |        Default        |              |    12.73 MB     | 172.44 ms | 177.85 ms |
| de_DE  |    Ginger MO (MO)     |              |     7.57 MB     | 151.11 ms | 156.30 ms |
| de_DE  |    Ginger MO (PHP)    |              |     6.46 MB     | 130.10 ms | 135.10 ms |
| de_DE  |   Ginger MO (JSON)    |              |     7.57 MB     | 146.06 ms | 151.05 ms |
| de_DE  |    Native Gettext     |              |     5.81 MB     | 132.91 ms | 137.90 ms |
| de_DE  |        DynaMo         |              |     7.34 MB     | 150.18 ms | 155.50 ms |
| de_DE  |     Cache in APCu     |              |    23.90 MB     | 171.78 ms | 177.50 ms |
| en_US  |        Default        |      ✅       |     5.80 MB     | 111.89 ms | 117.15 ms |
| de_DE  |        Default        |      ✅       |    12.73 MB     | 161.84 ms | 167.35 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |     7.58 MB     | 138.71 ms | 143.90 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |     6.47 MB     | 118.71 ms | 124.00 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |     7.58 MB     | 134.22 ms | 139.45 ms |
| de_DE  |    Native Gettext     |      ✅       |     5.81 MB     | 119.22 ms | 124.50 ms |
| de_DE  |        DynaMo         |      ✅       |     7.43 MB     | 122.87 ms | 128.35 ms |
| de_DE  |     Cache in APCu     |      ✅       |    23.97 MB     | 160.44 ms | 166.30 ms |
| de_DE  | Cache in object cache |      ✅       |    12.75 MB     | 161.53 ms | 167.45 ms |

**WordPress Admin**

| Locale |       Scenario        | Object Cache | wp-memory-usage | wp-total  | TTFB      |
|:-------|:---------------------:|:------------:|:---------------:|:---------:|:----------|
| en_US  |        Default        |              |     5.61 MB     | 165.06 ms | 179.80 ms |
| de_DE  |        Default        |              |    14.51 MB     | 198.29 ms | 209.70 ms |
| de_DE  |    Ginger MO (MO)     |              |     7.97 MB     | 170.97 ms | 181.05 ms |
| de_DE  |    Ginger MO (PHP)    |              |     6.51 MB     | 150.53 ms | 161.70 ms |
| de_DE  |   Ginger MO (JSON)    |              |     7.97 MB     | 168.62 ms | 179.55 ms |
| de_DE  |    Native Gettext     |              |     5.69 MB     | 154.93 ms | 165.20 ms |
| de_DE  |        DynaMo         |              |     7.68 MB     | 172.49 ms | 183.50 ms |
| de_DE  |     Cache in APCu     |              |    28.76 MB     | 198.75 ms | 211.30 ms |
| en_US  |        Default        |      ✅       |     5.70 MB     | 116.78 ms | 126.60 ms |
| de_DE  |        Default        |      ✅       |    14.53 MB     | 173.75 ms | 184.70 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |     8.00 MB     | 149.42 ms | 160.30 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |     6.53 MB     | 125.66 ms | 136.70 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |     8.00 MB     | 154.01 ms | 168.25 ms |
| de_DE  |    Native Gettext     |      ✅       |     5.71 MB     | 129.21 ms | 140.05 ms |
| de_DE  |        DynaMo         |      ✅       |     7.71 MB     | 131.91 ms | 142.30 ms |
| de_DE  |     Cache in APCu     |      ✅       |    28.79 MB     | 174.77 ms | 186.05 ms |
| de_DE  | Cache in object cache |      ✅       |    14.55 MB     | 174.39 ms | 185.40 ms |

## Credits

Originally developed by [Dion Hulse](https://github.com/dd32/ginger-mo).
