<?php

$dir = dirname( __DIR__ ) . '/';

require $dir . 'lib/class-ginger-mo.php';
require $dir . 'lib/class-ginger-mo-translation-file.php';
require $dir . 'lib/class-ginger-mo-translation-file-json.php';
require $dir . 'lib/class-ginger-mo-translation-file-mo.php';
require $dir . 'lib/class-ginger-mo-translation-file-php.php';
require $dir . 'lib/class-ginger-mo-translation-compat-provider.php';
require $dir . 'lib/class-ginger-mo-translation-compat.php';
require $dir . 'tests/Ginger_MO_TestCase.php';
require $dir . 'tests/Testable_Ginger_MO_Translation_File.php';

define( 'GINGER_MO_TEST_DATA', $dir . 'tests/data/', false );
