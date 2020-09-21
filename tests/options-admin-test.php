<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

/**
 * Test class to test the Clicky_Options_Admin class.
 */
class Clicky_Options_Admin_Test extends Clicky_UnitTestCase {

	/**
	 * Instance of the class being tested.
	 *
	 * @var Clicky_Options_Admin
	 */
	private static $class_instance;

	/**
	 * Used for testing input fields.
	 *
	 * @var string[]
	 */
	public $test_args = [
		'name'  => 'testname',
		'value' => 'testvalue',
	];

	/**
	 * Set up the class instance to be tested.
	 */
	public static function setUpBeforeClass() {
		self::$class_instance = new Clicky_Options_Admin();
	}

	/**
	 * Tests the options constructor.
	 *
	 * @covers Clicky_Options_Admin::__construct
	 */
	public function test___construct() {
		$this->assertSame( Clicky_Options::$option_defaults, self::$class_instance->options );
	}

	/**
	 * Tests whether the plugin properly initializes on admin.
	 *
	 * @covers Clicky_Options_Admin::admin_init
	 */
	public function test_admin_init() {

		self::$class_instance->admin_init();

		global $wp_settings_sections, $wp_settings_fields;

		$this->assertArrayHasKey( 'clicky', $wp_settings_sections );
		$this->assertArrayHasKey( 'clicky', $wp_settings_fields );
	}

	/**
	 * Tests whether the like box is loading.
	 *
	 * @covers Clicky_Options_Admin::like_text
	 */
	public function test_like_text() {
		ob_start();
		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/like-box.php';
		$output = ob_get_clean();

		$this->expectOutputString( $output );
		self::$class_instance->like_text();
	}

	/**
	 * Makes sure there is output and makes sure our affiliate link is in it.
	 *
	 * @covers Clicky_Options_Admin::basic_settings_intro
	 */
	public function test_basic_settings_intro() {
		ob_start();
		self::$class_instance->basic_settings_intro();
		$output = ob_get_clean();

		$this->assertGreaterThan( 0, strlen( $output ) );
		$this->assertContains( '//clicky.com/145844', $output );
	}

	/**
	 * Makes sure there is output.
	 *
	 * @covers Clicky_Options_Admin::outbound_explanation
	 */
	public function test_outbound_explanation() {
		ob_start();
		self::$class_instance->outbound_explanation();
		$output = ob_get_clean();

		$this->assertGreaterThan( 0, strlen( $output ) );
	}

	/**
	 * Makes sure link to the forums is in the text.
	 *
	 * @covers Clicky_Options_Admin::support_text
	 */
	public function test_support_text() {
		ob_start();
		self::$class_instance->support_text();
		$output = ob_get_clean();

		$this->assertGreaterThan( 0, strlen( $output ) );
		$this->assertContains( 'clicky.com/forums', $output );
	}

	/**
	 * Tests whether a proper input field is generated.
	 *
	 * @covers Clicky_Options_Admin::input_text
	 */
	public function test_input_text() {
		$this->expectOutputString( '<input type="text" class="text" name="testname" value="testvalue"/>' );

		self::$class_instance->input_text( $this->test_args );
	}

	/**
	 * Tests whether a proper checkbox input field is generated.
	 *
	 * @covers Clicky_Options_Admin::input_checkbox
	 */
	public function test_input_checkbox() {
		$this->expectOutputString( '<input class="checkbox" type="checkbox"  name="clicky[testname]"/>' );

		self::$class_instance->input_checkbox( $this->test_args );
	}

	/**
	 * Tests whether a proper checked input field is generated when the setting is true.
	 *
	 * @covers Clicky_Options_Admin::input_text
	 */
	public function test_input_checkbox_checked() {
		$this->expectOutputString( '<input class="checkbox" type="checkbox"  checked=\'checked\' name="clicky[testname]"/>' );

		self::$class_instance->options['testname'] = true;
		self::$class_instance->input_checkbox( $this->test_args );
	}
}
