<?php

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Class for the Clicky plugin admin page
 */
class Clicky_Admin_Page extends Clicky_Admin {

	/**
	 * Class constructor
	 */
	function __construct() {
		new Clicky_Options_Admin();

		$this->options = Clicky_Options_Admin::instance()->get();

		add_action( 'admin_print_scripts', array( $this, 'config_page_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );

		add_action( 'admin_head', array( $this, 'i18n_module' ) );
	}

	/**
	 * Determine whether or not to send the minified version
	 *
	 * @param string $ext
	 *
	 * @return string
	 */
	private function file_ext( $ext ) {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? $ext : '.min' . $ext;
	}

	/**
	 * Enqueue the styles for the admin page
	 */
	function config_page_styles() {
		wp_enqueue_style( 'clicky-admin-css', CLICKY_PLUGIN_DIR_URL . 'css/clicky_admin' . $this->file_ext( '.css' ) );
	}

	/**
	 * Enqueue the scripts for the admin page
	 */
	function config_page_scripts() {
		wp_enqueue_script( 'yoast_ga_admin', CLICKY_PLUGIN_DIR_URL . 'js/admin' . $this->file_ext( '.js' ) );
	}

	/**
	 * Creates the configuration page
	 */
	public function config_page() {
		require 'views/admin_page.php';
	}

	/**
	 * Create a postbox widget
	 *
	 * @param string $title
	 * @param string $content
	 */
	function box( $title, $content ) {
		echo '<div class="yoast_box"><h3>' . $title . '</h3><div class="inside">' . $content . '</div></div>';
	}

	/**
	 * Create a "plugin like" box.
	 */
	function plugin_like() {
		$content = '<p>' . __( 'Why not do any or all of the following:', 'clicky' ) . '</p>';
		$content .= '<ul>';
		$content .= '<li><a href="' . $this->homepage . '">' . __( 'Link to it so other folks can find out about it.', 'clicky' ) . '</a></li>';
		$content .= '<li><a href="https://wordpress.org/plugins/' . $this->hook . '/">' . __( 'Give it a 5 star rating on WordPress.org.', 'clicky' ) . '</a></li>';
		$content .= '<li><a href="https://wordpress.org/plugins/' . $this->hook . '/">' . __( 'Let other people know that it works with your WordPress setup.', 'clicky' ) . '</a></li>';
		$content .= '</ul>';
		$this->box( __( 'Like this plugin?', 'clicky' ), $content );
	}

	/**
	 * Info box with link to the bug tracker.
	 */
	function plugin_support() {
		$content = '<p>' . sprintf( __( 'If you\'re in need of support with Clicky and / or this plugin, please visit the %1$sClicky forums%2$s.', 'clicky' ), "<a href='https://clicky.com/forums/'>", '</a>' ) . '</p>';
		$this->box( __( 'Need Support?', 'clicky' ), $content );
	}

	/**
	 * Generate an RSS box.
	 *
	 * @param string $feed        Feed URL to parse
	 * @param string $title       Title of the box
	 * @param string $extra_links Additional links to add to the output, after the RSS subscribe link
	 */
	function rss_news( $feed, $title, $extra_links = '' ) {
		include_once( ABSPATH . WPINC . '/feed.php' );
		$rss       = fetch_feed( $feed );
		$rss_items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );
		$content   = '<ul>';
		if ( ! $rss_items ) {
			$content .= '<li class="yoast">' . __( 'No news items, feed might be broken...', 'clicky' ) . '</li>';
		} else {
			foreach ( $rss_items as $item ) {
				$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls = null, 'display' ) );
				$content .= '<li class="yoast">';
				$content .= '<a class="rsswidget" href="' . $url . '#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin">' . esc_html( $item->get_title() ) . '</a> ';
				$content .= '</li>';
			}
			$content .= '<li class="rss"><a href="' . $feed . '">' . __( 'Subscribe with RSS', 'clicky' ) . '</a></li>';
			$content .= $extra_links;
		}
		$content .= '</ul>';

		$this->box( $title, $content );
	}

	/**
	 * Box with latest news from Clicky
	 */
	function clicky_news() {
		$this->rss_news( 'http://clicky.com/blog/rss', __( 'Latest news from Clicky', 'clicky' ) );
	}

	/**
	 * Box with latest news from Yoast.com for sidebar
	 */
	function yoast_news() {
		$extra_links = '<li class="facebook"><a href="https://www.facebook.com/yoast">' . __( 'Like Yoast on Facebook', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="twitter"><a href="https://twitter.com/yoast">' . __( 'Follow Yoast on Twitter', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="email"><a href="https://yoast.com/newsletter/">' . __( 'Subscribe by email', 'clicky' ) . '</a></li>';

		$this->rss_news( 'https://yoast.com/feed/', __( 'Latest news from Yoast', 'clicky' ), $extra_links );
	}

	/**
	 * Instantiate the i18n module
	 */
	function i18n_module() {
		new yoast_i18n(
			array(
				'textdomain'     => 'clicky',
				'project_slug'   => 'clicky-wordpress-plugin',
				'plugin_name'    => 'Clikcy for WordPress',
				'hook'           => 'clicky_admin_footer',
				'glotpress_url'  => 'http://translate.yoast.com',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'https://cdn.yoast.com/wp-content/uploads/i18n-images/Yoast_Translate.svg',
				'register_url '  => 'http://translate.yoast.com/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=clicky-i18n-promo',
			)
		);
	}
}