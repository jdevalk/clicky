<?php
/**
 * Clicky for WordPress Plugin.
 *
 * @package   Yoast/Clicky
 * @copyright Copyright (C) 2012-2019 Yoast BV - support@yoast.com
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Clicky for WordPress
 * Version:     1.9
 * Plugin URI:  https://yoast.com/wordpress/plugins/clicky/
 * Description: The Clicky for WordPress plugin by Yoast makes it easy for you to add your Clicky analytics tracking code to your WordPress install, while also giving you some advanced tracking options.
 * Author:      Team Yoast
 * Author URI:  https://yoast.com/
 * Text Domain: clicky
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'CLICKY_PLUGIN_FILE', __FILE__ );
define( 'CLICKY_PLUGIN_VERSION', '1.9' );
define( 'CLICKY_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKY_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( CLICKY_PLUGIN_DIR_PATH . 'vendor/autoload_52.php' ) ) {
	require CLICKY_PLUGIN_DIR_PATH . 'vendor/autoload_52.php';
}

/**
 * Class Yoast Clicky base class.
 */
class Yoast_Clicky {

	/**
	 * Initialize the plugin settings.
	 */
	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the whole plugin.
	 */
	public function init() {
		load_plugin_textdomain( 'clicky', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( is_admin() ) {
			new Clicky_Admin();
		}
		else {
			new Clicky_Frontend();
			if ( current_user_can( 'manage_options' ) ) {
				new Clicky_Visitor_Graph();
			}
		}
	}
}

new Yoast_Clicky();
