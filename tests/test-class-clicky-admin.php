<?php

class Clicky_Admin_Test extends Clicky_UnitTestCase {

	/**
	 * @var Clicky_Admin
	 */
	private static $class_instance;

	public static function setUpBeforeClass() {
		self::$class_instance = new Clicky_Admin();
	}

	/**
	 * @covers Clicky_Admin::__construct
	 */
	public function test___construct() {
		$this->assertEquals( self::$class_instance->options, Clicky_Options::$option_defaults );

		$this->assertEquals( 10, has_filter( 'plugin_action_links', array(
			self::$class_instance,
			'add_action_link',
		) ) );

		$this->assertEquals( 10, has_action( 'publish_post', array( self::$class_instance, 'insert_post' ) ) );
		$this->assertEquals( 10, has_action( 'admin_notices', array( self::$class_instance, 'admin_warnings' ) ) );
		$this->assertEquals( 10, has_action( 'admin_menu', array( self::$class_instance, 'admin_init' ) ) );
	}

	/**
	 * @covers Clicky_Admin::admin_init
	 */
	public function test_admin_init() {

		self::$class_instance->admin_init();

		global $wp_meta_boxes;
		$this->assertEquals( 'clicky', $wp_meta_boxes['post']['side']['default']['clicky']['id'] );
		$this->assertEquals( 'clicky', $wp_meta_boxes['page']['side']['default']['clicky']['id'] );
	}

	/**
	 * @covers Clicky_Admin::setup_warning
	 */
	public function test_setup_warning() {
		$this->expectOutputString( "<div class='updated'><p><strong>Clicky is almost ready. </strong>You must <a href='http://example.org/wp-admin/options-general.php?page=clicky'> enter your Clicky Site ID, Site Key and Admin Site Key</a> for it to work.</p></div>" );
		self::$class_instance->admin_warnings();
	}

	/**
	 * @covers Clicky_Admin::setup_warning
	 *
	 * Test whether no warnings are generated when the required keys are set
	 */
	public function test_setup_warning_false() {
		// Set the required variables to a value
		self::$class_instance->options['site_id']        = 1;
		self::$class_instance->options['site_key']       = 1;
		self::$class_instance->options['admin_site_key'] = 1;

		$this->expectOutputString( '' );
		self::$class_instance->admin_warnings();
	}

	/**
	 * @covers Clicky_Admin::meta_box_content
	 */
	public function test_meta_box_content() {
		$this->options = self::$class_instance->options;

		$post_id = $this->factory->post->create();

		global $post;
		$post = get_post( $post_id );

		$clicky_goal = array(
			'id'    => 1,
			'value' => 0.5,
		);
		update_post_meta( $post_id, '_clicky_goal', $clicky_goal );

		ob_start();
		require( 'admin/views/meta-box.php' );
		$output = ob_get_clean();

		$this->expectOutputString( $output );
		self::$class_instance->meta_box_content();
	}

	/**
	 * @covers Clicky_Admin::insert_post
	 */
	public function test_insert_post() {
		add_action( 'wp_insert_post', array( self::$class_instance, 'insert_post' ) );

		$post_id = $this->factory->post->create();

		$goal = get_post_meta( $post_id, '_clicky_goal', true );

		$expected = array(
			'id'    => 0,
			'value' => 0.0,
		);

		$this->assertEquals( $expected, $goal );
	}

	/**
	 * @covers Clicky_Admin::dashboard_page
	 */
	public function test_dashboard_page() {
		self::$class_instance->options['site_id']  = 1;
		self::$class_instance->options['site_key'] = 2;

		$expected = '<br/>
<iframe style="margin-left: 20px; width: 100%; height: 1000px;"
		src="https://clicky.com/stats/wp-iframe?site_id=1&amp;sitekey=2"></iframe>
';

		$this->expectOutputString( $expected );

		self::$class_instance->dashboard_page();
	}

	/**
	 * @covers Clicky_Admin::add_action_link
	 */
	public function test_add_action_link() {
		$output   = self::$class_instance->add_action_link( array(), CLICKY_PLUGIN_FILE );
		$expected = '<a href="' . admin_url( 'options-general.php?page=clicky' ) . '">Settings</a>';

		$this->assertEquals( $expected, $output[0] );
	}
}
