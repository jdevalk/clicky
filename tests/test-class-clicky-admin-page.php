<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

/**
 * Test class to test the Clicky_Admin_Page class.
 */
class Clicky_Admin_Page_Test extends Clicky_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Clicky_Admin
	 */
	private static $class_instance;

	/**
	 * Set up the class instance to be tested.
	 */
	public static function setUpBeforeClass() {
		self::$class_instance = new Clicky_Admin_Page();
	}

	/**
	 * @covers Clicky_Admin_Page::__construct
	 */
	public function test___construct() {
		$this->assertSame( self::$class_instance->options, Clicky_Options::$option_defaults );

		$this->assertSame( 10, has_action( 'admin_print_scripts', array( self::$class_instance, 'config_page_scripts' ) ) );
		$this->assertSame( 10, has_action( 'admin_print_styles', array( self::$class_instance, 'config_page_styles' ) ) );
		$this->assertSame( 10, has_action( 'admin_head', array( self::$class_instance, 'i18n_module' ) ) );
	}

	/**
	 * @covers Clicky_Admin_Page::config_page_styles
	 */
	public function test_config_page_styles() {
		self::$class_instance->config_page_styles();

		global $wp_styles;
		$this->assertSame( 'clicky-admin-css', $wp_styles->registered['clicky-admin-css']->handle );
	}
}
