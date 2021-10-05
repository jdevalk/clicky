<?php
/**
 * Clicky for WordPress plugin test file.
 *
 * @package Yoast/Clicky/Tests
 */

/**
 * Test class to test the Clicky_Admin class.
 */
class Clicky_Admin_Test extends Clicky_UnitTestCase {

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
		self::$class_instance = new Clicky_Admin();
	}

	/**
	 * Tests class constructor.
	 *
	 * @covers Clicky_Admin::__construct
	 */
	public function test___construct() {
		$this->assertSame( Clicky_Options::$option_defaults, self::$class_instance->options );

		$this->assertSame( 10, has_filter( 'plugin_action_links', [ self::$class_instance, 'add_action_link' ] ) );

		$this->assertSame( 10, has_action( 'publish_post', [ self::$class_instance, 'insert_post' ] ) );
		$this->assertSame( 10, has_action( 'admin_notices', [ self::$class_instance, 'admin_warnings' ] ) );
		$this->assertSame( 10, has_action( 'admin_menu', [ self::$class_instance, 'admin_init' ] ) );
	}

	/**
	 * Tests whether metaboxes are set up correctly.
	 *
	 * @covers Clicky_Admin::admin_init
	 */
	public function test_admin_init() {

		self::$class_instance->admin_init();

		global $wp_meta_boxes;
		$this->assertSame( 'clicky', $wp_meta_boxes['post']['side']['default']['clicky']['id'] );
		$this->assertSame( 'clicky', $wp_meta_boxes['page']['side']['default']['clicky']['id'] );
	}

	/**
	 * Tests our warning for new installs.
	 *
	 * @covers Clicky_Admin::setup_warning
	 */
	public function test_setup_warning() {
		$this->expectOutputString( "<div class='updated'><p><strong>Clicky is almost ready. </strong>You must <a href='http://example.org/wp-admin/options-general.php?page=clicky'> enter your Clicky Site ID, Site Key and Admin Site Key</a> for it to work.</p></div>" );
		self::$class_instance->admin_warnings();
	}

	/**
	 * Test whether no warnings are generated when the required keys are set.
	 *
	 * @covers Clicky_Admin::setup_warning
	 */
	public function test_setup_warning_false() {
		// Set the required variables to a value.
		self::$class_instance->options['site_id']        = 1;
		self::$class_instance->options['site_key']       = 1;
		self::$class_instance->options['admin_site_key'] = 1;

		$this->expectOutputString( '' );
		self::$class_instance->admin_warnings();
	}

	/**
	 * Tests whether our metabox has the output we expect.
	 *
	 * @covers Clicky_Admin::meta_box_content
	 */
	public function test_meta_box_content() {
		$this->options = self::$class_instance->options;

		$post_id = $this->factory->post->create();

		global $post;
		$post = get_post( $post_id );

		$clicky_goal = [
			'id'    => 1,
			'value' => 0.5,
		];
		update_post_meta( $post_id, '_clicky_goal', $clicky_goal );

		ob_start();
		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/meta-box.php';
		$output = ob_get_clean();

		$this->expectOutputString( $output );
		self::$class_instance->meta_box_content();
	}

	/**
	 * Tests whether clicky data is properly inserted on post insert.
	 *
	 * @covers Clicky_Admin::insert_post
	 */
	public function test_insert_post() {
		add_action( 'wp_insert_post', [ self::$class_instance, 'insert_post' ] );

		$post_id = $this->factory->post->create();

		$goal = get_post_meta( $post_id, '_clicky_goal', true );

		$expected = [
			'id'    => 0,
			'value' => 0.0,
		];

		$this->assertSame( $expected, $goal );
	}

	/**
	 * Tests whether the dashboard page is loading as expected.
	 *
	 * @covers Clicky_Admin::dashboard_page
	 */
	public function test_dashboard_page() {
		self::$class_instance->options['site_id']  = 1;
		self::$class_instance->options['site_key'] = 2;

		$expected = '<br/>
<iframe style="margin-left: 20px; width: 100%; height: 1000px;"
		src="https://clicky.com/stats/wp-iframe?site_id=1&#038;sitekey=2"></iframe>
';

		$this->expectOutputString( $expected );

		self::$class_instance->dashboard_page();
	}

	/**
	 * Tests whether the plugin action link on the plugins page is added.
	 *
	 * @covers Clicky_Admin::add_action_link
	 */
	public function test_add_action_link() {
		$output   = self::$class_instance->add_action_link( [], CLICKY_PLUGIN_FILE );
		$expected = '<a href="' . admin_url( 'options-general.php?page=clicky' ) . '">Settings</a>';

		$this->assertSame( $expected, $output[0] );
	}
}
