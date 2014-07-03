<?php
/*
Plugin Name: Clicky for WordPress
Version: 1.4.3
Plugin URI: https://yoast.com/wordpress/plugins/clicky/
Description: Integrates Clicky on your blog!
Author: Team Yoast
Author URI: https://yoast.com/
*/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * The Clicky for WordPress plugin by Yoast makes it easy for you to add your Clicky analytics tracking code to your WordPress install, while also giving you some advanced tracking options.
 *
 * @link https://yoast.com/wordpress/plugins/clicky/
 */

/**
 * Load the proper text domain
 */
function clicky_init() {
	load_plugin_textdomain( 'clicky', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

add_action( 'plugins_loaded', 'clicky_init' );

if ( ! class_exists( 'Clicky_Admin' ) ) {

	require_once( 'yst_plugin_tools.php' );

	/**
	 * Class Clicky_Admin
	 *
	 * Creates the admin for the Clicky for WordPress plugin by Yoast
	 */
	class Clicky_Admin extends Clicky_Base_Plugin_Admin {

		/**
		 * Menu slug for WordPress admin
		 *
		 * @access private
		 * @var string
		 */
		var $hook = 'clicky';

		/**
		 * Name of the plugin (long version)
		 *
		 * @access private
		 * @var string
		 */
		var $longname = 'Clicky Configuration';

		/**
		 * Name of the plugin (short version)
		 *
		 * @access private
		 * @var string
		 */
		var $shortname = 'Clicky';

		/**
		 * Link to Clicky homepage
		 *
		 * @access private
		 * @var string
		 */
		var $homepage = 'https://yoast.com/wordpress/plugins/clicky/';

		/**
		 * Link to Clicky RSS feed
		 *
		 * @access private
		 * @var string
		 */
		var $feed = 'http://clicky.com/blog/rss';

		/**
		 * Adds meta boxes to the Admin interface
		 *
		 * @link https://codex.wordpress.org/Function_Reference/add_meta_box
		 * @link https://codex.wordpress.org/Function_Reference/get_post_types
		 */
		function meta_box() {
			foreach ( get_post_types() as $pt ) {
				add_meta_box( 'clicky', __( 'Clicky Goal Tracking', 'clicky' ), array(
					'Clicky_Admin',
					'clicky_meta_box'
				), $pt, 'side' );
			}
		}

		/**
		 * Creates  warnings for empty fields in the admin
		 *
		 * @uses clicky_get_options()
		 * @uses clicky_warning()
		 * @link https://codex.wordpress.org/Function_Reference/add_action
		 */
		function clicky_admin_warnings() {
			$options = clicky_get_options();
			if ( ( ! $options['site_id'] || empty( $options['site_id'] ) || ! $options['site_key'] || empty( $options['site_key'] ) || ! $options['admin_site_key'] || empty( $options['admin_site_key'] ) ) && ! $_POST ) {
				/**
				 * Outputs a warning
				 */
				function clicky_warning() {
					echo "<div id='clickywarning' class='updated fade'><p><strong>";
					_e( 'Clicky is almost ready. ', 'clicky' );
					echo "</strong>";
					printf( __( 'You must %1$s enter your Clicky Site ID, Site Key and Admin Site Key%2$s for it to work.', 'clicky' ), "<a href='" . admin_url( 'options-general.php?page=clicky' ) . "'>", "</a>" );
					echo "</p></div>";
					echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#clickywarning').hide('slow');}, 10000);</script>";
				}

				add_action( 'admin_notices', 'clicky_warning' );

				return;
			}
		}

		/**
		 * Add meta box for entering specific goals
		 *
		 * @link https://codex.wordpress.org/Function_Reference/get_post_meta
		 */
		function clicky_meta_box() {
			global $post;

			$options = clicky_get_options();
			if ( ! isset( $options['site_id'] ) || empty( $options['site_id'] ) ) {
				return;
			}

			$clicky_goal = get_post_meta( $post->ID, '_clicky_goal', true );

			echo '<p>';
			printf( __( 'Clicky can track Goals for you too, %1$syou can create them here%2$s. To be able to track a goal on this post, you need to specify the goal ID here. Optionally, you can also provide the goal revenue.', 'clicky' ), '<a target="_blank" href="https://clicky.com/stats/goals-setup?site_id=' . $options['site_id'] . '">', '</a>' );
			echo '</p>';
			echo '<table>';
			echo '<tr>';
			echo '<th><label for="clicky_goal_id">' . __( 'Goal ID', 'clicky' ) . ':</label></th>';
			echo '<td><input type="text" name="clicky_goal_id" id="clicky_goal_id" value="' . ( isset( $clicky_goal['id'] ) ? esc_attr( $clicky_goal['id'] ) : '' ) . '"/></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th><label for="clicky_goal_value">' . __( 'Goal Revenue', 'clicky' ) . ': &nbsp;</label></th>';
			echo '<td><input type="text" name="clicky_goal_value" id="clicky_goal_value" value="' . ( isset( $clicky_goal['value'] ) ? esc_attr( $clicky_goal['value'] ) : '' ) . '"/></td>';
			echo '</tr>';
			echo '</table>';
		}

		/**
		 * Construct of class Clicky_admin
		 *
		 * @access private
		 * @link   https://codex.wordpress.org/Function_Reference/add_action
		 * @link   https://codex.wordpress.org/Function_Reference/add_filter
		 */
		function __construct() {
			$this->filename = __FILE__;

			add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
			add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );

			add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );

			add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );

			add_action( 'admin_menu', array( $this, 'meta_box' ) );
			add_action( 'publish_post', array( $this, 'clicky_insert_post' ) );

			add_action( 'wp_head', array( $this, 'stats_admin_bar_head' ) );

			$this->clicky_admin_warnings();
		}

		/**
		 * Updates post meta for '_clicky_goal' with goal ID and value
		 *
		 * @param int $pID The post ID
		 *
		 * @link https://codex.wordpress.org/Function_Reference/delete_post_meta
		 * @link https://codex.wordpress.org/Function_Reference/add_post_meta
		 */
		function clicky_insert_post( $pID ) {
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
			add_dashboard_page( $this->shortname . ' ' . __( 'Stats', 'clicky' ), $this->shortname . ' ' . __( 'Stats', 'clicky' ), $this->accesslvl, $this->hook, array(
				&$this,
				'dashboard_page'
			) );
		}

		/**
		 * Loads (external) stats page in an iframe
		 *
		 * @uses clicky_get_options()
		 */
		function dashboard_page() {
			$options = clicky_get_options();
			?>
			<br />
			<iframe style="margin-left: 20px; width: 850px; height: 1000px;"
			        src="https://clicky.com/stats/wp-iframe?site_id=<?php echo $options['site_id']; ?>&amp;sitekey=<?php echo $options['site_key']; ?>"></iframe>
		<?php
		}

		/**
		 * Creates the configuration page for Clicky for WordPress by Yoast
		 *
		 * @uses clicky_get_options()
		 * @link https://codex.wordpress.org/Function_Reference/current_user_can
		 * @link https://codex.wordpress.org/Function_Reference/check_admin_referer
		 * @link https://codex.wordpress.org/Function_Reference/update_option
		 * @link https://codex.wordpress.org/Function_Reference/wp_nonce_field
		 */
		function config_page() {
			$options = clicky_get_options();

			if ( isset( $_POST['submit'] ) ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					die( __( 'You cannot edit the Clicky settings.', 'clicky' ) );
				}
				check_admin_referer( 'clicky-config' );

				foreach ( array( 'site_id', 'site_key', 'admin_site_key', 'outbound_pattern' ) as $option_name ) {
					if ( isset( $_POST[ $option_name ] ) ) {
						$options[ $option_name ] = sanitize_text_field( $_POST[ $option_name ] );
					} else {
						$options[ $option_name ] = '';
					}
				}

				foreach ( array( 'ignore_admin', 'track_names', 'cookies_disable' ) as $option_name ) {
					if ( isset( $_POST[ $option_name ] ) ) {
						$options[ $option_name ] = true;
					} else {
						$options[ $option_name ] = false;
					}
				}

				if ( clicky_get_options() != $options ) {
					update_option( 'clicky', $options );
					$message = "<p>" . __( 'Clicky settings have been updated.', 'clicky' ) . "</p>";
				}
			}

			if ( isset( $error ) && $error != "" ) {
				echo "<div id=\"message\" class=\"error\">$error</div>\n";
			} elseif ( isset( $message ) && $message != "" ) {
				echo "<div id=\"updatemessage\" class=\"updated fade\">$message</div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
			}
			?>
			<div class="wrap">
				<h2><img id="clicky-icon" src="<?php echo plugin_dir_url( __FILE__ ); ?>images/clicky-32x32.png" class="alignleft"/> Clicky <?php _e( "Configuration", 'clicky' ); ?></h2>

				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
							<form action="" method="post" id="clicky-conf" enctype="multipart/form-data">
								<?php
								wp_nonce_field( 'clicky-config' );

								$content = '<p style="text-align:left; margin: 0 10px; font-size: 13px; line-height: 150%;">' . sprintf( __( 'Go to your %1$suser homepage on Clicky%2$s and click &quot;Preferences&quot; under the name of the domain, you will find the Site ID, Site Key, Admin Site Key and Database Server under Site information.', 'clicky' ), '<a href="http://clicky.com/145844">', '</a>' ) . '</p>';

								$rows   = array();
								$rows[] = array(
									'id'      => 'site_id',
									'label'   => __( 'Site ID', 'clicky' ),
									'desc'    => '',
									'content' => '<input class="text" type="text" value="' . $options['site_id'] . '" name="site_id" id="site_id"/>',
								);

								$rows[] = array(
									'id'      => 'site_key',
									'label'   => __( 'Site Key', 'clicky' ),
									'desc'    => '',
									'content' => '<input class="text" type="text" value="' . $options['site_key'] . '" name="site_key" id="site_key"/>',
								);

								$rows[] = array(
									'id'      => 'admin_site_key',
									'label'   => __( 'Admin Site Key', 'clicky' ),
									'desc'    => '',
									'content' => '<input class="text" type="text" value="' . $options['admin_site_key'] . '" name="admin_site_key" id="admin_site_key"/>',
								);

								$content .= ' ' . $this->form_table( $rows );
								$this->postbox( 'clicky_settings', __( 'Clicky Settings', 'clicky' ), $content );

								$rows   = array();
								$rows[] = array(
									'id'      => 'ignore_admin',
									'label'   => __( 'Ignore Admin users', 'clicky' ),
									'desc'    => __( 'If you are using a caching plugin, such as W3 Total Cache or WP-Supercache, please ensure that you have it configured to NOT use the cache for logged in users. Otherwise, admin users <em>will still</em> be tracked.', 'clicky' ),
									'content' => '<input type="checkbox" ' . checked( $options['ignore_admin'], true, false ) . ' name="ignore_admin" id="ignore_admin"/>',
								);

								$rows[] = array(
									'id'      => 'cookies_disable',
									'label'   => __( 'Disable cookies', 'clicky' ),
									'desc'    => __( 'If you don\'t want Clicky to use cookies on your site, check this button. By doing so, uniques will instead be determined based on their IP address.', 'clicky' ),
									'content' => '<input type="checkbox" ' . checked( $options['cookies_disable'], true, false ) . ' name="cookies_disable" id="cookies_disable"/>',
								);

								$rows[] = array(
									'id'      => 'track_names',
									'label'   => __( 'Track names of commenters', 'clicky' ),
									'desc'    => '',
									'content' => '<input type="checkbox" ' . checked( $options['track_names'], true, false ) . ' name="track_names" id="track_names"/>'
								);

								$rows[] = array(
									'id'      => 'outbound_pattern',
									'label'   => __( 'Outbound Link Pattern', 'clicky' ),
									'desc'    => sprintf( __( 'If your site uses redirects for outbound links, instead of links that point directly to their external source (this is popular with affiliate links, for example), then you\'ll need to use this variable to tell our tracking code additional patterns to look for when automatically tracking outbound links. %1$sRead more here%1$s.', 'clicky' ), '<a href="https://secure.getclicky.com/helpy?type=customization#outbound_pattern">', '</a>' ),
									'content' => '<input class="text" type="text" value="' . $options['outbound_pattern'] . '" name="outbound_pattern" id="outbound_pattern"/> ' . __( 'For instance: <code>/out/,/go/</code>', 'clicky' ),
								);

								$this->postbox( 'clicky_settings', __( 'Advanced Settings', 'clicky' ), $this->form_table( $rows ) );

								?>
								<div class="submit">
									<input type="submit" class="button-primary" name="submit"
									       value="<?php _e( "Update Clicky Settings", 'clicky' ); ?> &raquo;" />
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:20%;">
					<div class="metabox-holder">
						<div class="meta-box-sortables">
							<?php
							$this->plugin_like();
							$this->plugin_support();
							$this->yoast_news();
							$this->news();
							?>
						</div>
						<br /><br /><br />
					</div>
				</div>
			</div>
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
		 * @uses clicky_get_options()
		 * @uses create_graph()
		 * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar
		 */
		function stats_admin_bar_menu( &$wp_admin_bar ) {
			$options = clicky_get_options();

			$img_src = $this->create_graph();

			$url = 'https://secure.getclicky.com/stats/?site_id=' . $options['site_id'];

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
		 * @uses clicky_get_options()
		 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
		 * @link https://codex.wordpress.org/Function_Reference/is_wp_error
		 * @return bool|string Returns base64-encoded image on succes (String) or fail (boolean) on failure
		 */
		function create_graph() {
			$options = clicky_get_options();

			if ( ! function_exists( 'imagecreate' ) ) {
				return false;
			}

			$resp = wp_remote_get( "https://api.getclicky.com/api/stats/4?site_id=" . $options['site_id'] . "&sitekey=" . $options['site_key'] . "&type=visitors&hourly=1&date=last-3-days" );

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

	}

	$clicky_admin = new Clicky_Admin();
}

/**
 * Loads Clicky-options set in WordPress.
 * If already set: trim some option. Otherwise load defaults.
 *
 * @link https://codex.wordpress.org/Function_Reference/get_option
 * @uses clicky_defaults()
 * @return array Returns the trimmed/default options for clicky
 */
function clicky_get_options() {
	$options = get_option( 'clicky' );
	if ( ! is_array( $options ) ) {
		clicky_defaults();
	} else {
		$options['site_id']        = trim( $options['site_id'] );
		$options['site_key']       = trim( $options['site_key'] );
		$options['admin_site_key'] = trim( $options['admin_site_key'] );
	}

	return $options;
}

/**
 * Default options for Clicky for WordPress plugin by Yoast
 *
 * @link https://codex.wordpress.org/Function_Reference/add_option
 */
function clicky_defaults() {
	$options = array(
		'site_id'          => '',
		'site_key'         => '',
		'admin_site_key'   => '',
		'outbound_pattern' => '',
		'ignore_admin'     => false,
		'track_names'      => true,
		'cookies_disable'  => false,
	);
	add_option( 'clicky', $options );
}

/**
 * Add clicky scripts to footer
 *
 * @return bool
 *
 * @link https://codex.wordpress.org/Function_Reference/current_user_can
 */
function clicky_script() {
	$options = clicky_get_options();

	if ( is_preview() ) {
		return false;
	}

	// Bail early if current user is admin and ignore admin is true
	if ( $options['ignore_admin'] && current_user_can( "manage_options" ) ) {
		echo "\n<!-- " . __( "Clicky tracking not shown because you're an administrator and you've configured Clicky to ignore administrators.", 'clicky' ) . " -->\n";

		return false;
	}


	// Debug
	?>
	<!-- Clicky Web Analytics - http://clicky.com, WordPress Plugin by Yoast - https://yoast.com/wordpress/plugins/clicky/ -->
	<?php
	// Track commenter name if track_names is true
	if ( $options['track_names'] ) {
		?>
		<script type='text/javascript'>
			function clicky_gc(name) {
				var ca = document.cookie.split(';');
				for (var i in ca) {
					if (ca[i].indexOf(name + '=') != -1) {
						return decodeURIComponent(ca[i].split('=')[1]);
					}
				}
				return '';
			}
			var username_check = clicky_gc('comment_author_<?php echo md5( get_option( "siteurl" ) ); ?>');
			if (username_check) var clicky_custom_session = {username: username_check};
		</script>
	<?php
	}

	$clicky_extra = '';

	// Goal tracking
	if ( is_singular() ) {
		global $post;
		$clicky_goal = get_post_meta( $post->ID, '_clicky_goal', true );
		if ( is_array( $clicky_goal ) && ! empty( $clicky_goal['id'] ) ) {
			$clicky_extra .= 'var clicky_goal = { id: "' . trim( esc_attr( $clicky_goal['id'] ) ) . '"';
			if ( isset( $clicky_goal['value'] ) && ! empty( $clicky_goal['value'] ) ) {
				$clicky_extra .= ', revenue: "' . esc_attr( $clicky_goal['value'] ) . '"';
			}
			$clicky_extra .= ' };' . "\n";
		}
	}

	if ( isset( $options['outbound_pattern'] ) && trim( $options['outbound_pattern'] ) != '' ) {
		$patterns = explode( ',', $options['outbound_pattern'] );
		$pattern  = '';
		foreach ( $patterns as $pat ) {
			if ( $pattern != '' ) {
				$pattern .= ',';
			}
			$pat = trim( str_replace( '"', '', str_replace( "'", "", $pat ) ) );
			$pattern .= "'" . $pat . "'";
		}
		$clicky_extra .= 'clicky_custom.outbound_pattern = [' . $pattern . '];' . "\n";
	}

	if ( isset( $options['cookies_disable'] ) && $options['cookies_disable'] ) {
		$clicky_extra .= "clicky_custom.cookies_disable = 1;\n";
	}

	?>
	<script type="text/javascript">
		<?php
		if ( ! empty( $clicky_extra ) ) {
				echo "\t".'var clicky_custom = clicky_custom || {}; ';
				echo $clicky_extra;
		} ?>
		var clicky = { log : function () { return true;	}, goal: function () { return true;	} };
		var clicky_site_id = <?php echo $options['site_id']; ?>;
		(function () {
			var s = document.createElement('script');s.type = 'text/javascript';s.async = true;s.src = '//static.getclicky.com/js';
			( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild(s);
		})();
	</script>
	<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/<?php echo $options['site_id']; ?>ns.gif" /></p></noscript>
	<?php
	return true;
}

add_action( 'wp_footer', 'clicky_script', 90 );

/**
 * Create the log for clicky
 *
 * @uses clicky_get_options()
 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
 *
 * @param array $a The array with basic log-data
 *
 * @return bool Returns true on success or false on failure
 */
function clicky_log( $a ) {
	$options = clicky_get_options();

	if ( ! isset( $options['site_id'] ) || empty( $options['site_id'] ) || ! isset( $options['admin_site_key'] ) || empty( $options['admin_site_key'] ) ) {
		return false;
	}

	$type = $a['type'];
	if ( ! in_array( $type, array( "pageview", "download", "outbound", "click", "custom", "goal" ) ) ) {
		$type = "pageview";
	}

	$file = "https://in.getclicky.com/in.php?site_id=" . $options['site_id'] . "&sitekey_admin=" . $options['admin_site_key'] . "&type=" . $type;

	# referrer and user agent - will only be logged if this is the very first action of this session
	if ( $a['ref'] ) {
		$file .= "&ref=" . urlencode( $a['ref'] );
	}

	if ( $a['ua'] ) {
		$file .= "&ua=" . urlencode( $a['ua'] );
	}

	# we need either a session_id or an ip_address...
	if ( is_numeric( $a['session_id'] ) ) {
		$file .= "&session_id=" . $a['session_id'];
	} else {
		if ( ! $a['ip_address'] ) {
			$a['ip_address'] = $_SERVER['REMOTE_ADDR'];
		} # automatically grab IP that PHP gives us.
		if ( ! preg_match( "#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#", $a['ip_address'] ) ) {
			return false;
		}
		$file .= "&ip_address=" . $a['ip_address'];
	}

	# goals can come in as integer or array, for convenience
	if ( $a['goal'] ) {
		if ( is_numeric( $a['goal'] ) ) {
			$file .= "&goal[id]=" . $a['goal'];
		} else {
			if ( ! is_numeric( $a['goal']['id'] ) ) {
				return false;
			}
			foreach ( $a['goal'] as $key => $value ) {
				$file .= "&goal[" . urlencode( $key ) . "]=" . urlencode( $value );
			}
		}
	}

	# custom data, must come in as array of key=>values
	foreach ( $a['custom'] as $key => $value ) {
		$file .= "&custom[" . urlencode( $key ) . "]=" . urlencode( $value );
	}

	if ( $type == "goal" || $type == "custom" ) {
		# dont do anything, data has already been cat'd
	} else {
		if ( $type == "outbound" ) {
			if ( ! preg_match( "#^(https?|telnet|ftp)#", $a['href'] ) ) {
				return false;
			}
		} else {
			# all other action types must start with either a / or a #
			if ( ! preg_match( "#^(/|\#)#", $a['href'] ) ) {
				$a['href'] = "/" . $a['href'];
			}
		}
		$file .= "&href=" . urlencode( $a['href'] );
		if ( $a['title'] ) {
			$file .= "&title=" . urlencode( $a['title'] );
		}
	}

	return wp_remote_get( $file ) ? true : false;
}

/**
 * Tracks comments that are not spam and not ping- or trackbacks
 *
 * @param int $commentID      The ID of the comment that needs to be tracked
 * @param int $comment_status Status of the comment (e.g. spam)
 */
function clicky_track_comment( $commentID, $comment_status ) {
	// Make sure to only track the comment if it's not spam (but do it for moderated comments).
	if ( $comment_status != 'spam' ) {
		$comment = get_comment( $commentID );
		// Only do this for normal comments, not for pingbacks or trackbacks
		if ( $comment->comment_type != 'pingback' && $comment->comment_type != 'trackback' ) {
			clicky_log(
				array(
					"type"       => "click",
					"href"       => "/wp-comments-post.php",
					"title"      => __( "Posted a comment", 'clicky' ),
					"ua"         => $comment->comment_agent,
					"ip_address" => $comment->comment_author_IP,
					"custom"     => array(
						"username" => $comment->comment_author,
						"email"    => $comment->comment_author_email,
					)
				)
			);
		}
	}
}

add_action( 'comment_post', 'clicky_track_comment', 10, 2 );