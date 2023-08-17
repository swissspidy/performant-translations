<?php

define( 'GINGER_MO_TEST_DATA', '' );
define( 'DIR_TESTDATA', '' );
define( 'WP_LANG_DIR', '' );
define( 'DIR_PLUGIN_TESTDATA', '' );
define( 'WP_PLUGIN_DIR', '' );

function i18n_plugin_test() {
	return __( 'This is a dummy plugin', 'internationalized-plugin' );
}
