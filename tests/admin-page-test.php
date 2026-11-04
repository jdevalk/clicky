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

	/**
	 * Tests that the i18n module is not started for users without manage_options,
	 * even on the Clicky settings page.
	 *
	 * @covers Clicky_Admin_Page::i18n_module
	 */
	public function test_i18n_module_not_loaded_for_subscriber() {
		$this->set_up_i18n_context( 'subscriber', 'settings_page_clicky' );

		self::$class_instance->i18n_module();

		$this->assertFalse( has_action( 'Yoast\WP\Clicky\admin_footer' ) );
	}

	/**
	 * Tests that the i18n module is not started on admin pages other than the Clicky settings page.
	 *
	 * @covers Clicky_Admin_Page::i18n_module
	 */
	public function test_i18n_module_not_loaded_on_other_page() {
		$this->set_up_i18n_context( 'administrator', 'profile' );

		self::$class_instance->i18n_module();

		$this->assertFalse( has_action( 'Yoast\WP\Clicky\admin_footer' ) );
	}

	/**
	 * Tests that the i18n module is started for administrators on the Clicky settings page.
	 *
	 * @covers Clicky_Admin_Page::i18n_module
	 */
	public function test_i18n_module_loaded_for_admin_on_settings_page() {
		$this->set_up_i18n_context( 'administrator', 'settings_page_clicky' );

		self::$class_instance->i18n_module();

		$this->assertTrue( has_action( 'Yoast\WP\Clicky\admin_footer' ) );
	}

	/**
	 * Logs in a user with a non-en_US admin locale and sets the current admin screen.
	 *
	 * @param string $role      The role of the user to log in.
	 * @param string $screen_id The admin screen to simulate.
	 */
	private function set_up_i18n_context( $role, $screen_id ) {
		$user_id = self::factory()->user->create(
			[
				'role'   => $role,
				'locale' => 'de_DE',
			]
		);
		wp_set_current_user( $user_id );
		set_current_screen( $screen_id );
		delete_transient( 'yoast_i18n_clicky-wordpress-plugin_promo_hide' );
		delete_option( 'i18n_notice_clicky-wordpress-plugin_promo_hide' );
		remove_all_actions( 'Yoast\WP\Clicky\admin_footer' );
	}

	/**
	 * Tests that a dismissal stored by the old yoast/i18n-module is carried over.
	 *
	 * @covers Clicky_Admin_Page::i18n_module
	 * @covers Clicky_Admin_Page::migrate_i18n_promo_dismissal
	 */
	public function test_i18n_module_migrates_old_dismissal() {
		$this->set_up_i18n_context( 'administrator', 'settings_page_clicky' );
		set_transient( 'yoast_i18n_clicky-wordpress-plugin_promo_hide', true );

		self::$class_instance->i18n_module();

		$this->assertTrue( (bool) get_option( 'i18n_notice_clicky-wordpress-plugin_promo_hide' ) );
		$this->assertFalse( get_transient( 'yoast_i18n_clicky-wordpress-plugin_promo_hide' ) );
	}

	/**
	 * Tests that nothing is migrated when the promo was never dismissed.
	 *
	 * @covers Clicky_Admin_Page::migrate_i18n_promo_dismissal
	 */
	public function test_i18n_module_no_migration_without_old_dismissal() {
		$this->set_up_i18n_context( 'administrator', 'settings_page_clicky' );

		self::$class_instance->i18n_module();

		$this->assertFalse( get_option( 'i18n_notice_clicky-wordpress-plugin_promo_hide' ) );
	}

	/**
	 * Resets the admin screen and current user after each test.
	 */
	public function tear_down() {
		set_current_screen( 'front' );
		wp_set_current_user( 0 );
		remove_all_actions( 'Yoast\WP\Clicky\admin_footer' );
		parent::tear_down();
	}
}
