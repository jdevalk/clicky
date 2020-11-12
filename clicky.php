<?php
/**
 * Clicky for WordPress Plugin.
 *
 * @package   Yoast/Clicky
 * @copyright Copyright (C) 2012-2019 Yoast BV - support@yoast.com
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name:       Clicky for WordPress
 * Version:           2.0
 * Plugin URI:        https://yoast.com/wordpress/plugins/clicky/
 * Description:       The Clicky for WordPress plugin by Yoast makes it easy for you to add your Clicky analytics tracking code to your WordPress install, while also giving you some advanced tracking options.
 * Author:            Team Yoast
 * Requires PHP:      5.6
 * Requires at least: 5.0
 * Author URI:        https://yoast.com/
 * Text Domain:       clicky
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'CLICKY_PLUGIN_FILE', __FILE__ );
define( 'CLICKY_PLUGIN_VERSION', '2.0' );
define( 'CLICKY_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKY_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Class Yoast Clicky base class.
 */
class Yoast_Clicky {

	/**
	 * Initialize the plugin settings.
	 */
	public function __construct() {
		if (
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
			( defined( 'WP_CLI' ) && WP_CLI ) ||
			( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( file_exists( CLICKY_PLUGIN_DIR_PATH . 'vendor/autoload.php' ) ) {
			require_once CLICKY_PLUGIN_DIR_PATH . 'vendor/autoload.php';
		}

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the whole plugin.
	 */
	public function init() {
		load_plugin_textdomain( 'clicky', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( is_admin() ) {
			new Clicky_Admin();

			return;
		}

		new Clicky_Frontend();
		if ( current_user_can( 'manage_options' ) ) {
			new Clicky_Visitor_Graph();
		}
	}
}

add_action(
	'plugins_loaded',
	function () {
		new Yoast_Clicky();
	}
);
