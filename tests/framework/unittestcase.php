<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

/**
 * TestCase base class for convenience methods.
 */
class Clicky_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Fake a request to the WP front page.
	 */
	protected function go_to_home() {
		$this->go_to( home_url( '/' ) );
	}

	/**
	 * @param string $string   Expected output.
	 * @param mixed  $function Unused.
	 */
	protected function expectOutput( $string, $function = null ) {
		$output = ob_get_contents();
		ob_clean();
		$this->assertSame( $string, $output );
	}
}
