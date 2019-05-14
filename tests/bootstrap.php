<?php

// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the Clicky Test Suite' . PHP_EOL;
echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	define( 'WP_DEVELOP_DIR', getenv( 'WP_DEVELOP_DIR' ) );
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'clicky/clicky.php' ),
);

if ( defined( 'WP_DEVELOP_DIR' ) ) {
	require WP_DEVELOP_DIR . 'tests/phpunit/includes/bootstrap.php';
}
else {
	require '../../../../tests/phpunit/includes/bootstrap.php';
}

// Include unit test base class.
require_once dirname( __FILE__ ) . '/framework/class-clicky-unit-test-case.php';
