<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

// Disable xdebug backtrace.
if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

echo 'Welcome to the Clicky Test Suite' . PHP_EOL;
echo 'Version: 2.0' . PHP_EOL . PHP_EOL;

// Determine the WP_TEST_DIR.
if ( getenv( 'WP_TESTS_DIR' ) !== false ) {
	$_tests_dir = getenv( 'WP_TESTS_DIR' );
}

// Fall back on the WP_DEVELOP_DIR environment variable.
if ( empty( $_tests_dir ) && getenv( 'WP_DEVELOP_DIR' ) !== false ) {
	$_tests_dir = rtrim( getenv( 'WP_DEVELOP_DIR' ), '/' ) . '/tests/phpunit';
}

// Give access to tests_add_filter() function.
require_once rtrim( $_tests_dir, '/' ) . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/clicky.php';
}

/**
 * Filter the plugins URL to pretend the plugin is installed in the test environment.
 *
 * @param string $url    The complete URL to the plugins directory including scheme and path.
 * @param string $path   Path relative to the URL to the plugins directory. Blank string
 *                       if no path is specified.
 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin
 *                       is specified.
 *
 * @return string
 */
function _plugins_url( $url, $path, $plugin ) {
	$plugin_dir = dirname( __DIR__ );
	if ( $plugin === $plugin_dir . '/clicky.php' ) {
		$url = str_replace( dirname( $plugin_dir ), '', $url );
	}

	return $url;
}

// Add plugin to active mu-plugins - to make sure it gets loaded.
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Overwrite the plugin URL to not include the full path.
tests_add_filter( 'plugins_url', '_plugins_url', 10, 3 );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Include unit test base class.
require_once __DIR__ . '/framework/unittestcase.php';
