<?php

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Options Class for the Clicky plugin
 *
 * @since 1.5
 */
class Clicky_Options {

	/**
	 * Name of the option we're using
	 *
	 * @var string
	 */
	public static $option_name = 'clicky';

	/**
	 * Holds the actual options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Saving instance of it's own in this static var
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Getting instance of this object. If instance doesn't exists it will be created.
	 *
	 * @return object|Clicky_Options
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Clicky_Options();
		}
		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Returns the Clicky options
	 *
	 * @return array
	 */
	public function get() {
		return $this->options;
	}

	/**
	 * Loads Clicky-options set in WordPress.
	 * If already set: trim some option. Otherwise load defaults.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/get_option
	 *
	 * @uses clicky_defaults()
	 *
	 * @return array Returns the trimmed/default options for clicky
	 */
	public function load() {
		$this->options = get_option( self::$option_name );
		if ( ! is_array( $this->options ) ) {
			$this->defaults();
		} else {
			$this->options['site_id']        = trim( $this->options['site_id'] );
			$this->options['site_key']       = trim( $this->options['site_key'] );
			$this->options['admin_site_key'] = trim( $this->options['admin_site_key'] );
		}
	}

	/**
	 * Default options for Clicky for WordPress plugin by Yoast
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_option
	 */
	function defaults() {
		$this->options = array(
			'site_id'          => '',
			'site_key'         => '',
			'admin_site_key'   => '',
			'outbound_pattern' => '',
			'ignore_admin'     => false,
			'track_names'      => true,
			'cookies_disable'  => false,
		);
		add_option( self::$option_name, $this->options );
	}
}