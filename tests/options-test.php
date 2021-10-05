<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

/**
 * Test class to test the Clicky_Options class.
 */
class Clicky_Options_Test extends Clicky_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Clicky_Options
	 */
	private static $class_instance;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$class_instance = new Clicky_Options();
	}

	/**
	 * Tests whether our getter works.
	 *
	 * @covers Clicky_Options::get
	 */
	public function test_get() {
		$this->assertSame( Clicky_Options::$option_defaults, self::$class_instance->get() );
	}

	/**
	 * Tests whether our constructor works as expected.
	 *
	 * @covers Clicky_Options::__construct
	 */
	public function test___construct() {
		$this->assertSame( Clicky_Options::$option_defaults, self::$class_instance->options );
	}
}
