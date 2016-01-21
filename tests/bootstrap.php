<?php

$dir = dirname( dirname( __FILE__ ) ) . '/';

include $dir . 'lib/class-ginger-mo.php';
include $dir . 'lib/class-ginger-mo-translation-file.php';
include $dir . 'tests/Ginger_MO_TestCase.php';
include $dir . 'tests/Testable_Ginger_MO_Translation_File.php';

define( 'GINGER_MO_TEST_DATA', $dir . 'tests/data/' );
