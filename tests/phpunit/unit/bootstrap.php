<?php

$root = dirname( dirname( dirname( __DIR__ ) ) ) . '/';

require $root . 'lib/class-plural-forms.php';
require $root . 'lib/class-ginger-mo.php';
require $root . 'lib/class-ginger-mo-translation-file.php';
require $root . 'lib/class-ginger-mo-translation-file-json.php';
require $root . 'lib/class-ginger-mo-translation-file-mo.php';
require $root . 'lib/class-ginger-mo-translation-file-php.php';
require __DIR__ . '/includes/Ginger_MO_TestCase.php';

define( 'GINGER_MO_TEST_DATA', __DIR__ . '/data/', false );
