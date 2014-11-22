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
	function __construct() {
		$this->options = Clicky_Options::instance()->get();
		
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'meta_box' ) );
		add_action( 'publish_post', array( $this, 'insert_post' ) );

		add_action( 'wp_head', array( $this, 'stats_admin_bar_head' ) );

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
	function meta_box() {
		foreach ( get_post_types( array( 'public' => true ) ) as $pt ) {
			add_meta_box( 'clicky', __( 'Clicky Goal Tracking', 'clicky' ), array( $this, 'meta_box_content' ), $pt, 'side' );
		}
	}

	/**
	 * Add meta box for entering specific goals
	 *
	 * @link https://codex.wordpress.org/Function_Reference/get_post_meta
	 */
	function meta_box_content() {
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
	 * @param int $pID The post ID
	 *
	 * @link https://codex.wordpress.org/Function_Reference/delete_post_meta
	 * @link https://codex.wordpress.org/Function_Reference/add_post_meta
	 */
	function insert_post( $pID ) {
		$clicky_goal = array(
			'id'    => (int) $_POST['clicky_goal_id'],
			'value' => floatval( $_POST['clicky_goal_value'] )
		);
		delete_post_meta( $pID, '_clicky_goal' );
		add_post_meta( $pID, '_clicky_goal', $clicky_goal, true );
	}

	/**
	 * Creates the dashboard page for Clicky for WordPress plugin by Yoast
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_dashboard_page
	 */
	function register_dashboard_page() {
		add_dashboard_page( __( 'Clicky Stats', 'clicky' ), __( 'Clicky Stats', 'clicky' ), 'manage_options', $this->hook, array(
			&$this,
			'dashboard_page'
		) );
	}

	/**
	 * Loads (external) stats page in an iframe
	 */
	function dashboard_page() {
		?>
		<br />
		<iframe style="margin-left: 20px; width: 850px; height: 1000px;"
		        src="https://clicky.com/stats/wp-iframe?site_id=<?php echo $this->options['site_id']; ?>&amp;sitekey=<?php echo $this->options['site_key']; ?>"></iframe>
	<?php
	}

	/**
	 * Creates (CSS for) head for the admin menu bar
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_action
	 */
	function stats_admin_bar_head() {
		add_action( 'admin_bar_menu', array( &$this, 'stats_admin_bar_menu' ), 1200 );
		?>

		<style type='text/css'>
			#wpadminbar .quicklinks li#wp-admin-bar-clickystats {
				height: 28px
			}

			#wpadminbar .quicklinks li#wp-admin-bar-clickystats a {
				height: 28px;
				padding: 0
			}

			#wpadminbar .quicklinks li#wp-admin-bar-clickystats a img {
				padding: 4px 5px;
				height: 20px;
				width: 99px;
			}
		</style>
	<?php
	}

	/**
	 * Adds Clicky (graph) to the admin bar of the website
	 *
	 * @param object $wp_admin_bar Class that contains all information for the admin bar. Passed by reference.
	 *
	 * @uses create_graph()
	 * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar
	 */
	function stats_admin_bar_menu( &$wp_admin_bar ) {
		$img_src = $this->create_graph();

		$url = 'https://secure.getclicky.com/stats/?site_id=' . $this->options['site_id'];

		$title = __( 'Visitors over 48 hours. Click for more Clicky Site Stats.', 'clicky' );

		$menu = array(
			'id'    => 'clickystats',
			'title' => "<img width='99' height='20' src='" . $img_src . "' alt='" . $title . "' title='" . $title . "' />",
			'href'  => $url
		);

		$wp_admin_bar->add_menu( $menu );
	}

	/**
	 * Creates the graph to be used in the admin bar
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
	 * @link https://codex.wordpress.org/Function_Reference/is_wp_error
	 *
	 * @return bool|string Returns base64-encoded image on succes (String) or fail (boolean) on failure
	 */
	function create_graph() {
		if ( ! function_exists( 'imagecreate' ) ) {
			return false;
		}

		$resp = wp_remote_get( "https://api.getclicky.com/api/stats/4?site_id=" . $this->options['site_id'] . "&sitekey=" . $this->options['site_key'] . "&type=visitors&hourly=1&date=last-3-days" );

		if ( is_wp_error( $resp ) || ! isset( $resp['response']['code'] ) || $resp['response']['code'] != 200 ) {
			return false;
		}

		$xml = simplexml_load_string( $resp['body'] );

		$i      = 0;
		$j      = 0;
		$k      = 0;
		$values = array();
		foreach ( $xml->type->date as $value ) {
			foreach ( $xml->type->date[ $i ]->item->value as $art ) //nested loop for multiple values in tag
			{

				$data = (int) ( $xml->type->date[ $i ]->item->value[ $j ] ); //$i and $j is used to iterate multiples of both tags respectively
				array_push( $values, $data );
				$j = $j + 1;
				$k ++;
				if ( $k == 48 ) {
					break 2;
				}
			}
			$j = 0; //so that in next item it starts from 0(zero)
			$i ++;
		}
		if ( count( $values ) == 0 ) {
			return false;
		}
		$values = array_reverse( $values );
		//-----------------------------------------------------------------------------------------------------------------------------
		//for graph
		$img_width  = 99;
		$img_height = 20;
		$margins    = 0;


		# ---- Find the size of graph by substracting the size of borders
		$graph_width  = $img_width - $margins * 2;
		$graph_height = $img_height - $margins * 2;
		$img          = imagecreate( $img_width, $img_height );


		$bar_width  = 0.01;
		$total_bars = count( $values );
		$gap        = ( $graph_width - $total_bars * $bar_width ) / ( $total_bars + 1 );

		# -------  Define Colors ----------------
		$bar_color = imagecolorallocate( $img, 220, 220, 220 );

		$black            = imagecolorallocate( $img, 0, 0, 0 );
		$background_color = imagecolortransparent( $img, $black );
		$border_color     = imagecolorallocate( $img, 50, 50, 50 );

		# ------ Create the border around the graph ------

		imagefilledrectangle( $img, 1, 1, $img_width - 2, $img_height - 2, $border_color );
		imagefilledrectangle( $img, $margins, $margins, $img_width - 1 - $margins, $img_height - 1 - $margins, $background_color );

		# ------- Max value is required to adjust the scale	-------
		$max_value = max( $values );
		if ( $max_value == 0 ) {
			$max_value = 1;
		}
		$ratio = $graph_height / $max_value;


		# ----------- Draw the bars here ------
		for ( $i = 0; $i < $total_bars; $i ++ ) {
			# ------ Extract key and value pair from the current pointer position
			list( $key, $value ) = each( $values );
			$x1 = $margins + $gap + $i * ( $gap + $bar_width );
			$x2 = $x1 + $bar_width;
			$y1 = $margins + $graph_height - intval( $value * $ratio );
			$y2 = $img_height - $margins;
			imagefilledrectangle( $img, $x1, $y1, $x2, $y2, $bar_color );
		}

		ob_start();
		imagepng( $img );
		$image = ob_get_contents();
		ob_end_clean();

		return 'data:image/png;base64,' . base64_encode( $image );
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
