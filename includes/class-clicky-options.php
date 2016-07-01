<?php
/**
 * @package Yoast/Clicky/Options
 */

/**
 * Options Class for the Clicky plugin
 *
 * @since 1.5
 */
class Clicky_Options {

	/**
	 * The default options for the Clicky plugin
	 *
	 * @var array
	 */
	public static $option_defaults = array(
		'site_id'          => '',           // There is no default site ID as we don't know it...
		'site_key'         => '',           // There is no default site key as we don't know it...
		'admin_site_key'   => '',           // There is no default admin site key as we don't know it...
		'outbound_pattern' => '',           // By defaulting to an empty string here, we disable this functionality until it's set.
		'ignore_admin'     => false,        // While ignoring an admin by default would make sense, it leads to admins thinking the plugin doesn't work.
		'track_names'      => false,        // Tracking the names of commenters makes sense, but might be illegal in some countries, so we default to off.
		'cookies_disable'  => false,        // No need to disable cookies by default as it severely impacts the quality of tracking.
		'disable_stats'    => false,// The stats on the frontend are often found useful, but some people might want to disable them.
	);

	/**
	 * Holds the type of variable that each option is, so we can cast it to that.
	 *
	 * @var array
	 */
	public static $option_var_types = array(
		'site_id'          => 'string',
		'site_key'         => 'string',
		'admin_site_key'   => 'string',
		'outbound_pattern' => 'string',
		'ignore_admin'     => 'bool',
		'track_names'      => 'bool',
		'cookies_disable'  => 'bool',
		'disable_stats'    => 'bool',
	);

	/**
	 * Name of the option we're using
	 *
	 * @var string
	 */
	public static $option_name = 'clicky';

	/**
	 * Saving instance of it's own in this static var
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds the actual options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->load_options();
		$this->sanitize_options();
	}

	/**
	 * Loads Clicky-options set in WordPress.
	 * If already set: trim some option. Otherwise load defaults.
	 */
	private function load_options() {
		$options = get_option( self::$option_name );
		if ( ! is_array( $options ) ) {
			$this->options = self::$option_defaults;
			update_option( self::$option_name, $this->options );
		}
		else {
			$this->options = array_merge( self::$option_defaults, $options );
		}
	}

	/**
	 * Forces all options to be of the type we expect them to be of.
	 */
	private function sanitize_options() {
		foreach ( $this->options as $key => $value ) {
			switch ( self::$option_var_types[ $key ] ) {
				case 'string':
					$this->options[ $key ] = (string) $value;
					break;
				case 'bool':
					$this->options[ $key ] = (bool) $value;
			}
		}
	}

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
	 * Returns the Clicky options
	 *
	 * @return array
	 */
	public function get() {
		return $this->options;
	}
}
