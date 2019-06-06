<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

class Clicky_Admin_Page_Test extends Clicky_UnitTestCase {

	/**
	 * @var Clicky_Admin
	 */
	private static $class_instance;

	public static function setUpBeforeClass() {
		self::$class_instance = new Clicky_Admin_Page();
	}

	/**
	 * @covers Clicky_Admin_Page::__construct
	 */
	public function test___construct() {
		$this->assertEquals( self::$class_instance->options, Clicky_Options::$option_defaults );

		$this->assertEquals( 10, has_action( 'admin_print_scripts', array( self::$class_instance, 'config_page_scripts' ) ) );
		$this->assertEquals( 10, has_action( 'admin_print_styles', array( self::$class_instance, 'config_page_styles' ) ) );
		$this->assertEquals( 10, has_action( 'admin_head', array( self::$class_instance, 'i18n_module' ) ) );
	}

	public function test_config_page_styles() {
		self::$class_instance->config_page_styles();

		global $wp_styles;
		$this->assertEquals( 'clicky-admin-css', $wp_styles->registered['clicky-admin-css']->handle );
	}
}
