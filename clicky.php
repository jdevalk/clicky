<?php
/*
Plugin Name: Clicky for WordPress
Version: 1.5
Plugin URI: https://yoast.com/wordpress/plugins/clicky/
Description: The Clicky for WordPress plugin by Yoast makes it easy for you to add your Clicky analytics tracking code to your WordPress install, while also giving you some advanced tracking options.
Author: Team Yoast
Author URI: https://yoast.com/
Text Domain: clicky
*/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'CLICKY_PLUGIN_FILE', __FILE__ );
define( 'CLICKY_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKY_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

class Yoast_Clicky {

	/**
	 * Initialize the plugin settings
	 */
	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the whole plugin
	 */
	public function init() {
		// register class autoloader
		spl_autoload_register( array( $this, 'autoload' ) );

		load_plugin_textdomain( 'clicky', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( is_admin() ) {
			new Clicky_Admin();
		} else {
			new Clicky_Frontend();
			if ( current_user_can( 'manage_options' ) ) {
				new Clicky_Visitor_Graph();
			}
		}
	}

	/**
	 * Autoloader method
	 *
	 * @since 1.5
	 *
	 * @param string $class Class to load
	 */
	public function autoload( $class ) {
		static $classes = null;
		if ( $classes === null ) {
			$include_path = dirname( __FILE__ );
			$classes      = array(
				'clicky_admin'         => $include_path . '/admin/class-clicky_admin.php',
				'clicky_admin_page'    => $include_path . '/admin/class-clicky_admin_page.php',
				'clicky_frontend'      => $include_path . '/frontend/class-clicky_frontend.php',
				'clicky_options'       => $include_path . '/includes/class-clicky_options.php',
				'clicky_options_admin' => $include_path . '/admin/class-clicky_options_admin.php',
				'clicky_visitor_graph' => $include_path . '/frontend/class-clicky_visitor_graph.php',
				'yoast_i18n'           => $include_path . '/admin/i18n-module/i18n-module.php',
			);
		}
		$class_name = strtolower( $class );
		if ( isset( $classes[ $class_name ] ) ) {
			require_once( $classes[ $class_name ] );
		}
	}
}

new Yoast_Clicky();

