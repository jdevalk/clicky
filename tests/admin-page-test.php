<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Clicky/Tests
 */

/**
 * Test class to test the Clicky_Admin_Page class.
 */
final class Clicky_Admin_Page_Test extends Clicky_UnitTestCase {

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

	/**
	 * Tests that the settings page footer fires the clicky/admin_footer action,
	 * and the deprecated clicky_admin_footer and Yoast\WP\Clicky\admin_footer actions for backward compatibility.
	 *
	 * @covers Clicky_Admin_Page::config_page
	 */
	public function test_config_page_fires_footer_actions() {
		$this->setExpectedDeprecated( 'clicky_admin_footer' );
		$this->setExpectedDeprecated( 'Yoast\WP\Clicky\admin_footer' );

		// Don't fetch the news feed in the sidebar.
		add_filter( 'pre_http_request', [ $this, 'block_http_request' ] );

		add_action( 'clicky/admin_footer', [ $this, 'echo_marker_new' ] );
		add_action( 'clicky_admin_footer', [ $this, 'echo_marker_legacy' ] );
		add_action( 'Yoast\WP\Clicky\admin_footer', [ $this, 'echo_marker_yoast' ] );

		ob_start();
		self::$class_instance->config_page();
		$output = ob_get_clean();

		remove_filter( 'pre_http_request', [ $this, 'block_http_request' ] );
		remove_action( 'clicky/admin_footer', [ $this, 'echo_marker_new' ] );
		remove_action( 'clicky_admin_footer', [ $this, 'echo_marker_legacy' ] );
		remove_action( 'Yoast\WP\Clicky\admin_footer', [ $this, 'echo_marker_yoast' ] );

		$this->assertStringContainsString( 'clicky-footer-new', $output );
		$this->assertStringContainsString( 'clicky-footer-legacy', $output );
		$this->assertStringContainsString( 'clicky-footer-yoast', $output );
		$this->assertStringNotContainsString( 'yoast', strtolower( str_replace( 'clicky-footer-yoast', '', $output ) ) );
	}

	/**
	 * Short-circuits HTTP requests during a test.
	 *
	 * @return WP_Error
	 */
	public function block_http_request() {
		return new WP_Error( 'http_request_blocked', 'Blocked in tests.' );
	}

	/**
	 * Echoes a marker from the clicky/admin_footer action.
	 */
	public function echo_marker_new() {
		echo 'clicky-footer-new';
	}

	/**
	 * Echoes a marker from the deprecated clicky_admin_footer action.
	 */
	public function echo_marker_legacy() {
		echo 'clicky-footer-legacy';
	}

	/**
	 * Echoes a marker from the deprecated Yoast\WP\Clicky\admin_footer action.
	 */
	public function echo_marker_yoast() {
		echo 'clicky-footer-yoast';
	}
}
