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
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$class_instance = new Clicky_Admin_Page();
	}

	/**
	 * Tests the constructor.
	 *
	 * @covers Clicky_Admin_Page::__construct
	 */
	public function test___construct() {
		$this->assertSame( Clicky_Options::$option_defaults, self::$class_instance->options );

		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', [ self::$class_instance, 'config_page_scripts' ] ) );
		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', [ self::$class_instance, 'config_page_styles' ] ) );
		$this->assertSame( 10, has_action( 'admin_head', [ self::$class_instance, 'i18n_module' ] ) );
	}

	/**
	 * Tests whether the correct style is loaded on the Clicky settings page.
	 *
	 * @covers Clicky_Admin_Page::config_page_styles
	 */
	public function test_config_page_styles_page_is_clicky() {
		self::$class_instance->config_page_styles( 'settings_page_clicky' );

		global $wp_styles;
		$this->assertSame( 'clicky-admin-css', $wp_styles->registered['clicky-admin-css']->handle );
		unset( $wp_styles->registered['clicky-admin-css'] );
	}

	/**
	 * Tests whether the Clicky styles are not loaded when we're not on the Clicky settings page.
	 *
	 * @covers Clicky_Admin_Page::config_page_styles
	 */
	public function test_config_page_styles_page_not_clicky() {
		self::$class_instance->config_page_styles( 'plugins_page' );

		global $wp_styles;
		$this->assertFalse( isset( $wp_styles->registered['clicky-admin-css'] ) );
	}
}
