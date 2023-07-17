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
| en_US  |        Default        |              |    15.62 MB     | 157.27 ms | 162.90 ms |
| de_DE  |        Default        |              |    29.12 MB     | 211.01 ms | 217.10 ms |
| de_DE  |    Ginger MO (MO)     |              |    19.26 MB     | 187.72 ms | 193.40 ms |
| de_DE  |    Ginger MO (PHP)    |              |    17.00 MB     | 164.92 ms | 170.60 ms |
| de_DE  |   Ginger MO (JSON)    |              |    19.26 MB     | 180.43 ms | 186.10 ms |
| de_DE  |    Native Gettext     |              |    16.02 MB     | 167.00 ms | 172.70 ms |
| de_DE  |        DynaMo         |              |    19.60 MB     | 186.78 ms | 193.10 ms |
| de_DE  |     Cache in APCu     |              |    50.26 MB     | 219.16 ms | 225.25 ms |
| en_US  |        Default        |      ✅       |    15.70 MB     | 141.71 ms | 147.90 ms |
| de_DE  |        Default        |      ✅       |    28.99 MB     | 193.13 ms | 200.05 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |    19.13 MB     | 174.06 ms | 180.30 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |    16.88 MB     | 149.77 ms | 155.65 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |    19.13 MB     | 167.30 ms | 173.35 ms |
| de_DE  |    Native Gettext     |      ✅       |    15.89 MB     | 151.55 ms | 157.75 ms |
| de_DE  |        DynaMo         |      ✅       |    18.59 MB     | 156.32 ms | 162.20 ms |
| de_DE  |     Cache in APCu     |      ✅       |    50.19 MB     | 202.61 ms | 209.15 ms |
| de_DE  | Cache in object cache |      ✅       |    29.04 MB     | 196.95 ms | 203.75 ms |

**Twenty Twenty-One**

| Locale |       Scenario        | Object Cache | wp-memory-usage | wp-total  | TTFB      |
|:-------|:---------------------:|:------------:|:---------------:|:---------:|:----------|
| en_US  |        Default        |              |    15.38 MB     | 140.26 ms | 145.50 ms |
| de_DE  |        Default        |              |    28.77 MB     | 192.69 ms | 199.00 ms |
| de_DE  |    Ginger MO (MO)     |              |    18.86 MB     | 171.89 ms | 177.60 ms |
| de_DE  |    Ginger MO (PHP)    |              |    16.59 MB     | 147.50 ms | 152.80 ms |
| de_DE  |   Ginger MO (JSON)    |              |    18.86 MB     | 162.50 ms | 167.80 ms |
| de_DE  |    Native Gettext     |              |    15.61 MB     | 150.07 ms | 155.45 ms |
| de_DE  |        DynaMo         |              |    19.22 MB     | 172.42 ms | 178.90 ms |
| de_DE  |     Cache in APCu     |              |    50.02 MB     | 200.39 ms | 206.25 ms |
| en_US  |        Default        |      ✅       |    15.22 MB     | 122.90 ms | 128.80 ms |
| de_DE  |        Default        |      ✅       |    28.57 MB     | 174.61 ms | 181.00 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |    18.66 MB     | 153.67 ms | 159.80 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |    16.40 MB     | 130.44 ms | 136.40 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |    18.66 MB     | 150.01 ms | 156.25 ms |
| de_DE  |    Native Gettext     |      ✅       |    15.41 MB     | 132.34 ms | 138.05 ms |
| de_DE  |        DynaMo         |      ✅       |    18.12 MB     | 137.01 ms | 143.20 ms |
| de_DE  |     Cache in APCu     |      ✅       |    49.88 MB     | 182.54 ms | 188.65 ms |
| de_DE  | Cache in object cache |      ✅       |    28.63 MB     | 175.69 ms | 182.45 ms |

**WordPress Admin**

| Locale |       Scenario        | Object Cache | wp-memory-usage | wp-total  | TTFB      |
|:-------|:---------------------:|:------------:|:---------------:|:---------:|:----------|
| en_US  |        Default        |              |    15.45 MB     | 159.38 ms | 169.70 ms |
| de_DE  |        Default        |              |    31.92 MB     | 227.88 ms | 246.85 ms |
| de_DE  |    Ginger MO (MO)     |              |    20.08 MB     | 198.58 ms | 213.20 ms |
| de_DE  |    Ginger MO (PHP)    |              |    17.11 MB     | 168.39 ms | 179.90 ms |
| de_DE  |   Ginger MO (JSON)    |              |    20.08 MB     | 188.22 ms | 206.85 ms |
| de_DE  |    Native Gettext     |              |    15.97 MB     | 169.81 ms | 181.05 ms |
| de_DE  |        DynaMo         |              |    20.61 MB     | 203.86 ms | 221.60 ms |
| de_DE  |     Cache in APCu     |              |    58.10 MB     | 230.67 ms | 245.10 ms |
| en_US  |        Default        |      ✅       |    15.69 MB     | 129.18 ms | 147.65 ms |
| de_DE  |        Default        |      ✅       |    31.85 MB     | 190.05 ms | 205.95 ms |
| de_DE  |    Ginger MO (MO)     |      ✅       |    20.01 MB     | 165.63 ms | 180.00 ms |
| de_DE  |    Ginger MO (PHP)    |      ✅       |    17.04 MB     | 141.34 ms | 153.15 ms |
| de_DE  |   Ginger MO (JSON)    |      ✅       |    20.01 MB     | 159.72 ms | 173.80 ms |
| de_DE  |    Native Gettext     |      ✅       |    15.89 MB     | 144.95 ms | 156.65 ms |
| de_DE  |        DynaMo         |      ✅       |    19.75 MB     | 147.13 ms | 158.30 ms |
| de_DE  |     Cache in APCu     |      ✅       |    58.02 MB     | 200.69 ms | 214.55 ms |
| de_DE  | Cache in object cache |      ✅       |    31.87 MB     | 195.01 ms | 211.35 ms |

## Credits

Originally developed by [Dion Hulse](https://github.com/dd32/ginger-mo).
