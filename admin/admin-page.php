<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Clicky\Admin
 */

/**
 * Class for the Clicky plugin admin page.
 */
class Clicky_Admin_Page extends Clicky_Admin {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$options_admin = new Clicky_Options_Admin();

		$this->options = $options_admin->get();

		add_action( 'admin_enqueue_scripts', [ $this, 'config_page_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'config_page_styles' ] );
	}

	/**
	 * Enqueue the styles for the admin page.
	 *
	 * @param string $current_page The current page.
	 *
	 * @return void
	 */
	public function config_page_styles( $current_page ) {
		if ( $current_page !== 'settings_page_clicky' ) {
			return;
		}

		wp_enqueue_style(
			'clicky-admin-css',
			CLICKY_PLUGIN_DIR_URL . 'css/dist/clicky_admin.css',
			null,
			CLICKY_PLUGIN_VERSION,
		);
	}

	/**
	 * Enqueue the scripts for the admin page.
	 *
	 * @param string $current_page The current page.
	 *
	 * @return void
	 */
	public function config_page_scripts( $current_page ) {
		if ( $current_page !== 'settings_page_clicky' ) {
			return;
		}

		wp_enqueue_script(
			'clicky-admin-js',
			CLICKY_PLUGIN_DIR_URL . 'js/admin.min.js',
			null,
			CLICKY_PLUGIN_VERSION,
			true,
		);
	}

	/**
	 * Creates the configuration page.
	 *
	 * @return void
	 */
	public function config_page() {
		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/admin-page.php';
	}

	/**
	 * Create a postbox widget.
	 *
	 * @param string $title   Title to display.
	 * @param string $content Content to display.
	 *
	 * @return void
	 */
	private function box( $title, $content ) {
		echo '<div class="clicky_box"><h3>' . esc_html( $title ) . '</h3><div class="inside">' . wp_kses_post( $content ) . '</div></div>';
	}

	/**
	 * Info box with link to the bug tracker.
	 *
	 * @return void
	 */
	private function plugin_support() {
		/* translators: 1: link open tag to clicky forum website; 2: link close tag. */
		$content = '<p>' . sprintf( __( 'If you\'re in need of support with Clicky and / or this plugin, please visit the %1$sClicky forums%2$s.', 'clicky' ), "<a href='https://clicky.com/forums/'>", '</a>' ) . '</p>';
		$this->box( __( 'Need Support?', 'clicky' ), $content );
	}

	/**
	 * Generate an RSS box.
	 *
	 * @param string $feed        Feed URL to parse.
	 * @param string $title       Title of the box.
	 * @param string $extra_links Additional links to add to the output, after the RSS subscribe link.
	 *
	 * @return void
	 */
	private function rss_news( $feed, $title, $extra_links = '' ) {
		include_once ABSPATH . WPINC . '/feed.php';
		$rss = fetch_feed( $feed );

		if ( is_wp_error( $rss ) ) {
			$rss = '<li class="news">' . esc_html__( 'No news items, feed might be broken...', 'clicky' ) . '</li>';
		}
		else {
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );

			$rss = '';
			foreach ( $rss_items as $item ) {
				$url  = preg_replace( '/#.*/', '', esc_url( $item->get_permalink() ) );
				$rss .= '<li class="news">';
				$rss .= '<a href="' . esc_url( $url . '#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin' ) . '">' . $item->get_title() . '</a> ';
				$rss .= '</li>';
			}
		}

		$content  = '<ul>';
		$content .= $rss;
		$content .= '<li class="rss"><a href="' . esc_url( $feed ) . '">' . esc_html__( 'Subscribe with RSS', 'clicky' ) . '</a></li>';
		$content .= $extra_links;
		$content .= '</ul>';

		$this->box( $title, $content );
	}

	/**
	 * Box with latest news from Clicky.
	 *
	 * @return void
	 */
	private function clicky_news() {
		$this->rss_news( 'https://blog.clicky.com/feed/', __( 'Latest news from Clicky', 'clicky' ) );
	}
}
