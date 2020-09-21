<?php
/**
 * Clicky for WordPress plugin file.
 *
 * @package Yoast\Clicky\Admin
 */

/**
 * Backend Class the Clicky plugin.
 */
class Clicky_Admin {

	/**
	 * This holds the plugins options.
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Menu slug for WordPress admin.
	 *
	 * @var string
	 */
	public $hook = 'clicky';

	/**
	 * Construct of class Clicky_admin.
	 *
	 * @link   https://codex.wordpress.org/Function_Reference/add_action
	 * @link   https://codex.wordpress.org/Function_Reference/add_filter
	 */
	public function __construct() {
		$this->options = Clicky_Options::instance()->get();

		add_filter( 'plugin_action_links', [ $this, 'add_action_link' ], 10, 2 );

		add_action( 'publish_post', [ $this, 'insert_post' ] );
		add_action( 'admin_notices', [ $this, 'admin_warnings' ] );
		add_action( 'admin_menu', [ $this, 'admin_init' ] );
	}

	/**
	 * Initialize needed actions.
	 */
	public function admin_init() {
		$public_post_types = get_post_types( [ 'public' => true ] );

		foreach ( $public_post_types as $post_type ) {
			add_meta_box(
				'clicky',
				__( 'Clicky Goal Tracking', 'clicky' ),
				[ $this, 'meta_box_content' ],
				$post_type,
				'side'
			);
		}

		$this->register_menu_pages();
	}

	/**
	 * Creates the dashboard and options pages.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_options_page
	 * @link https://codex.wordpress.org/Function_Reference/add_dashboard_page
	 */
	private function register_menu_pages() {
		add_options_page(
			__( 'Clicky settings', 'clicky' ),
			__( 'Clicky', 'clicky' ),
			'manage_options',
			$this->hook,
			[ new Clicky_Admin_Page(), 'config_page' ]
		);

		add_dashboard_page(
			__( 'Clicky Stats', 'clicky' ),
			__( 'Clicky Stats', 'clicky' ),
			'manage_options',
			'clicky_stats',
			[ $this, 'dashboard_page' ]
		);
	}

	/**
	 * Creates warnings for empty fields in the admin.
	 */
	public function admin_warnings() {
		$required_options = [ 'site_id', 'site_key', 'admin_site_key' ];

		foreach ( $required_options as $option ) {
			if ( empty( $this->options[ $option ] ) ) {
				$this->setup_warning();

				return;
			}
		}
	}

	/**
	 * Outputs a warning.
	 */
	private function setup_warning() {
		echo "<div class='updated'><p><strong>";
		esc_html_e( 'Clicky is almost ready. ', 'clicky' );
		echo '</strong>';
		printf(
			/* translators: 1: link open tag to the plugin settings page; 2: link close tag. */
			esc_html__( 'You must %1$s enter your Clicky Site ID, Site Key and Admin Site Key%2$s for it to work.', 'clicky' ),
			"<a href='" . esc_url( $this->plugin_options_url() ) . "'>",
			'</a>'
		);
		echo '</p></div>';
	}

	/**
	 * Returns the plugins settings page URL.
	 *
	 * @return string Admin URL to the current plugins settings URL.
	 */
	private function plugin_options_url() {
		return admin_url( 'options-general.php?page=' . $this->hook );
	}

	/**
	 * Add meta box for entering specific goals.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/get_post_meta
	 */
	public function meta_box_content() {
		global $post;

		if ( ! isset( $this->options['site_id'] ) || empty( $this->options['site_id'] ) ) {
			return;
		}

		$clicky_goal = get_post_meta( $post->ID, '_clicky_goal', true );

		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/meta-box.php';
	}

	/**
	 * Updates post meta for '_clicky_goal' with goal ID and value.
	 *
	 * @param int $post_id The post ID.
	 */
	public function insert_post( $post_id ) {
		$clicky_goal = [
			'id'    => (int) filter_input( INPUT_POST, 'clicky_goal_id' ),
			'value' => (float) filter_input( INPUT_POST, 'clicky_goal_value' ),
		];
		update_post_meta( $post_id, '_clicky_goal', $clicky_goal );
	}

	/**
	 * Loads (external) stats page in an iframe.
	 */
	public function dashboard_page() {
		$args       = [
			'site_id' => $this->options['site_id'],
			'sitekey' => $this->options['site_key'],
		];
		$iframe_url = add_query_arg( $args, 'https://clicky.com/stats/wp-iframe?' );

		require CLICKY_PLUGIN_DIR_PATH . 'admin/views/stats-page.php';
	}

	/**
	 * Add a link to the settings page to the plugins list.
	 *
	 * @param array  $links Links to add.
	 * @param string $file  Plugin file name.
	 *
	 * @return array
	 */
	public function add_action_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = CLICKY_PLUGIN_FILE;
		}
		if ( $file === $this_plugin ) {
			$settings_link = '<a href="' . esc_url( $this->plugin_options_url() ) . '">' . esc_html__( 'Settings', 'clicky' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
}
