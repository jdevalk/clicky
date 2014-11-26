<?php

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Backend Class the Clicky plugin
 */

class Clicky_Admin {

	/**
	 * This holds the plugins options
	 * 
	 * @var array
	 */
	var $options = array();
	
	/**
	 * Menu slug for WordPress admin
	 *
	 * @access private
	 * @var string
	 */
	var $hook = 'clicky';

	/**
	 * Link to Clicky homepage
	 *
	 * @access private
	 * @var string
	 */
	var $homepage = 'https://yoast.com/wordpress/plugins/clicky/';

	/**
	 * Construct of class Clicky_admin
	 *
	 * @access private
	 * @link   https://codex.wordpress.org/Function_Reference/add_action
	 * @link   https://codex.wordpress.org/Function_Reference/add_filter
	 */
	public function __construct() {
		$this->options = Clicky_Options::instance()->get();
		
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'meta_box' ) );
		add_action( 'publish_post', array( $this, 'insert_post' ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->hook ) {
			new Clicky_Admin_Page();
		}

		$this->clicky_admin_warnings();
	}
	
	/**
	 * Outputs a warning
	 */
	public function setup_warning() {
		echo "<div id='clickywarning' class='updated fade'><p><strong>";
		_e( 'Clicky is almost ready. ', 'clicky' );
		echo "</strong>";
		printf( __( 'You must %1$s enter your Clicky Site ID, Site Key and Admin Site Key%2$s for it to work.', 'clicky' ), "<a href='" . $this->plugin_options_url() . "'>", "</a>" );
		echo "</p></div>";
		echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#clickywarning').hide('slow');}, 10000);</script>";
	}

	/**
	 * Creates  warnings for empty fields in the admin
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_action
	 */
	public function clicky_admin_warnings() {
		if ( $_POST ) {
			return;
		}
		foreach ( array( 'site_id', 'site_key', 'admin_site_key' ) as $option ) {
			if ( empty( $this->options[$option] ) ) {
				add_action( 'admin_notices', array( $this, 'setup_warning' ) );
				return;
			}
		}
	}

	/**
	 * Adds meta boxes to the Admin interface
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_meta_box
	 * @link https://codex.wordpress.org/Function_Reference/get_post_types
	 */
	public function meta_box() {
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			add_meta_box( 'clicky', __( 'Clicky Goal Tracking', 'clicky' ), array( $this, 'meta_box_content' ), $post_type, 'side' );
		}
	}

	/**
	 * Add meta box for entering specific goals
	 *
	 * @link https://codex.wordpress.org/Function_Reference/get_post_meta
	 */
	public function meta_box_content() {
		global $post;

		if ( ! isset( $this->options['site_id'] ) || empty( $this->options['site_id'] ) ) {
			return;
		}

		$clicky_goal = get_post_meta( $post->ID, '_clicky_goal', true );

		echo '<p>';
		printf( __( 'Clicky can track Goals for you too, %1$syou can create them here%2$s. To be able to track a goal on this post, you need to specify the goal ID here. Optionally, you can also provide the goal revenue.', 'clicky' ), '<a target="_blank" href="https://clicky.com/stats/goals-setup?site_id=' . $this->options['site_id'] . '">', '</a>' );
		echo '</p>';
		echo '<table>';
		echo '<tr>';
		echo '<th><label for="clicky_goal_id">' . __( 'Goal ID:', 'clicky' ) . '</label></th>';
		echo '<td><input type="text" name="clicky_goal_id" id="clicky_goal_id" value="' . ( isset( $clicky_goal['id'] ) ? esc_attr( $clicky_goal['id'] ) : '' ) . '"/></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th><label for="clicky_goal_value">' . __( 'Goal Revenue:', 'clicky' ) . '</label></th>';
		echo '<td><input type="text" name="clicky_goal_value" id="clicky_goal_value" value="' . ( isset( $clicky_goal['value'] ) ? esc_attr( $clicky_goal['value'] ) : '' ) . '"/></td>';
		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Updates post meta for '_clicky_goal' with goal ID and value
	 *
	 * @param int $post_id The post ID
	 *
	 * @link https://codex.wordpress.org/Function_Reference/delete_post_meta
	 * @link https://codex.wordpress.org/Function_Reference/add_post_meta
	 */
	public function insert_post( $post_id ) {
		$clicky_goal = array(
			'id'    => (int) $_POST['clicky_goal_id'],
			'value' => floatval( $_POST['clicky_goal_value'] )
		);
		delete_post_meta( $post_id, '_clicky_goal' );
		add_post_meta( $post_id, '_clicky_goal', $clicky_goal, true );
	}

	/**
	 * Creates the dashboard page for Clicky for WordPress plugin by Yoast
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_dashboard_page
	 */
	public function register_dashboard_page() {
		add_dashboard_page( __( 'Clicky Stats', 'clicky' ), __( 'Clicky Stats', 'clicky' ), 'manage_options', 'clicky_stats', array( $this, 'dashboard_page' ) );
	}

	/**
	 * Loads (external) stats page in an iframe
	 */
	public function dashboard_page() {
		?>
		<br />
		<iframe style="margin-left: 20px; width: 850px; height: 1000px;"
		        src="https://clicky.com/stats/wp-iframe?site_id=<?php echo $this->options['site_id']; ?>&amp;sitekey=<?php echo $this->options['site_key']; ?>"></iframe>
	<?php
	}

	/**
	 * Register the plugins settings page
	 */
	function register_settings_page() {
		add_options_page( __( 'Clicky settings', 'clicky' ), __( 'Clicky', 'clicky' ), 'manage_options', $this->hook, array( new Clicky_Admin_Page, 'config_page' ) );
	}

	/**
	 * Returns the plugins settings page URL
	 *
	 * @return string
	 */
	function plugin_options_url() {
		return admin_url( 'options-general.php?page=' . $this->hook );
	}

	/**
	 * Add a link to the settings page to the plugins list
	 */
	function add_action_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = CLICKY_PLUGIN_FILE;
		}
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . $this->plugin_options_url() . '">' . __( 'Settings', 'clicky' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

}
