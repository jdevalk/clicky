<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast\Clicky\Admin
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

		add_action( 'admin_head', [ $this, 'i18n_module' ] );
	}

	/**
	 * Enqueue the styles for the admin page.
	 *
	 * @param string $current_page The current page.
	 */
	public function config_page_styles( $current_page ) {
		if ( $current_page !== 'settings_page_clicky' ) {
			return;
		}

		wp_enqueue_style(
			'clicky-admin-css',
			CLICKY_PLUGIN_DIR_URL . 'css/dist/clicky_admin.css',
			null,
			CLICKY_PLUGIN_VERSION
		);
	}

	/**
	 * Enqueue the scripts for the admin page.
	 *
	 * @param string $current_page The current page.
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
			true
		);
	}

	/**
	 * Creates the configuration page.
	 */
	public function config_page() {
		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/admin-page.php';
	}

	/**
	 * Create a postbox widget.
	 *
	 * @param string $title   Title to display.
	 * @param string $content Content to display.
	 */
	private function box( $title, $content ) {
		echo '<div class="yoast_box"><h3>' . esc_html( $title ) . '</h3><div class="inside">' . wp_kses_post( $content ) . '</div></div>';
	}

	/**
	 * Info box with link to the bug tracker.
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
	 */
	private function rss_news( $feed, $title, $extra_links = '' ) {
		include_once ABSPATH . WPINC . '/feed.php';
		$rss = fetch_feed( $feed );

		if ( is_wp_error( $rss ) ) {
			$rss = '<li class="yoast">' . esc_html__( 'No news items, feed might be broken...', 'clicky' ) . '</li>';
		}
		else {
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );

			$rss = '';
			foreach ( $rss_items as $item ) {
				$url  = preg_replace( '/#.*/', '', esc_url( $item->get_permalink() ) );
				$rss .= '<li class="yoast">';
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
	 */
	private function clicky_news() {
		$this->rss_news( 'https://blog.clicky.com/feed/', __( 'Latest news from Clicky', 'clicky' ) );
	}

	/**
	 * Box with latest news from Yoast.com for sidebar.
	 */
	private function yoast_news() {
		$extra_links  = '<li class="facebook"><a href="https://www.facebook.com/yoast">' . esc_html__( 'Like Yoast on Facebook', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="twitter"><a href="https://twitter.com/yoast">' . esc_html__( 'Follow Yoast on Twitter', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="email"><a href="https://yoast.com/newsletter/">' . esc_html__( 'Subscribe by email', 'clicky' ) . '</a></li>';

		$this->rss_news( 'https://yoast.com/feed/', __( 'Latest news from Yoast', 'clicky' ), $extra_links );
	}

	/**
	 * Instantiate the i18n module.
	 *
	 * Shows a promo asking for help with the translation when the admin's language is
	 * incomplete on translate.wordpress.org.
	 *
	 * Only runs on the Clicky settings page and for users who can manage options.
	 * Dismissing the promo hides it for the whole site, so it must not be reachable
	 * for other users or on other admin pages.
	 */
	public function i18n_module() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen === null || $screen->id !== 'settings_page_clicky' ) {
			return;
		}

		$this->migrate_i18n_promo_dismissal();

		/*
		 * Translation stats come from https://translate.wordpress.org/api/projects/wp-plugins/clicky/stable/.
		 * The project slug is only used in the names of the options the module stores, so it is kept
		 * the same as before to leave existing dismissals in place.
		 */
		new I18n_Notice_WordPressOrg(
			[
				'textdomain'     => 'clicky',
				'project_slug'   => 'clicky-wordpress-plugin',
				'plugin_name'    => __( 'Clicky for WordPress', 'clicky' ),
				'hook'           => 'Yoast\WP\Clicky\admin_footer',
				'glotpress_name' => 'translate.wordpress.org',
			]
		);
	}

	/**
	 * Carries over a promo dismissal stored by the old yoast/i18n-module.
	 *
	 * The old module stored the dismissal in a transient; wpbp/i18n-notice stores it in an option.
	 * Without this, sites that dismissed the promo would see it again.
	 */
	private function migrate_i18n_promo_dismissal() {
		$old_key = 'yoast_i18n_clicky-wordpress-plugin_promo_hide';
		if ( ! get_transient( $old_key ) ) {
			return;
		}

		update_option( 'i18n_notice_clicky-wordpress-plugin_promo_hide', true );
		delete_transient( $old_key );
	}
}
