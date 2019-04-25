<?php

class Clicky_Options_Test extends Clicky_UnitTestCase {

	/**
	 * @var Clicky_Options
	 */
	private static $class_instance;

	public static function setUpBeforeClass() {
		self::$class_instance = new Clicky_Options;
	}

	/**
	 * @covers Clicky_Options::get
	 */
	public function test_get() {
		$this->assertTrue( self::$class_instance->get() === Clicky_Options::$option_defaults );
	}

	/**
	 * @covers Clicky_Options::__construct
	 */
	public function test___construct() {
		$this->assertTrue( self::$class_instance->options === Clicky_Options::$option_defaults );
	}
}