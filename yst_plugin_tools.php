<?php

/**
 * Backend Class for use in all Yoast plugins
 *
 * @version 0.2
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Clicky_Base_Plugin_Admin' ) ) {
	class Clicky_Base_Plugin_Admin {

		var $hook = '';
		var $longname = '';
		var $shortname = '';
		var $ozhicon = '';
		var $optionname = '';
		var $homepage = '';
		var $filename = '';
		var $accesslvl = 'manage_options';

		function config_page_styles() {
			if ( isset( $_GET['page'] ) && $_GET['page'] == $this->hook ) {
				wp_enqueue_style( 'clicky-admin-css', plugin_dir_url( __FILE__ ) . 'yst_plugin_tools.css' );
			}
		}

		function register_settings_page() {
			add_options_page( $this->longname, $this->shortname, $this->accesslvl, $this->hook, array(
					&$this,
					'config_page'
				) );
		}

		function plugin_options_url() {
			return admin_url( 'options-general.php?page=' . $this->hook );
		}

		/**
		 * Add a link to the settings page to the plugins list
		 */
		function add_action_link( $links, $file ) {
			static $this_plugin;
			if ( empty( $this_plugin ) ) {
				$this_plugin = $this->filename;
			}
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="' . $this->plugin_options_url() . '">' . __( 'Settings' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}

		function config_page() {

		}

		/**
		 * Create a Checkbox input field
		 */
		function checkbox( $id, $label ) {
			$options = get_option( $this->optionname );

			return '<input type="checkbox" id="' . $id . '" name="' . $id . '"' . checked( $options[ $id ], true, false ) . '/> <label for="' . $id . '">' . $label . '</label><br/>';
		}

		/**
		 * Create a Text input field
		 */
		function textinput( $id, $label ) {
			$options = get_option( $this->optionname );

			return '<label for="' . $id . '">' . $label . ':</label><br/><input size="45" type="text" id="' . $id . '" name="' . $id . '" value="' . $options[ $id ] . '"/><br/><br/>';
		}

		/**
		 * Create a postbox widget
		 */
		function postbox( $id, $title, $content ) {
			?>
			<div id="<?php echo $id; ?>" class="postbox">
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>

				<div class="inside">
					<?php echo $content; ?>
				</div>
			</div>
			<?php
			$this->toc .= '<li><a href="#' . $id . '">' . $title . '</a></li>';
		}


		/**
		 * Create a form table from an array of rows
		 */
		function form_table( $rows ) {
			$content = '<table class="form-table">';
			$i       = 1;
			foreach ( $rows as $row ) {
				$class = 'yst_row';
				if ( $i % 2 == 0 ) {
					$class .= ' even';
				}
				$content .= '<tr class="' . $row['id'] . '_row ' . $class . '"><th valign="top" scrope="row">';
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					$content .= '<label for="' . $row['id'] . '">' . $row['label'] . ':</label>';
				} else {
					$content .= $row['label'];
				}
				$content .= '</th><td valign="top">';
				$content .= $row['content'];
				$content .= '</td></tr>';
				if ( isset( $row['desc'] ) && ! empty( $row['desc'] ) ) {
					$content .= '<tr class="' . $row['id'] . '_row ' . $class . '"><td colspan="2" class="yst_desc">' . $row['desc'] . '</td></tr>';
				}

				$i ++;
			}
			$content .= '</table>';

			return $content;
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
			$this->postbox( $this->hook . 'like', __( 'Like this plugin?', 'clicky' ), $content );
		}

		/**
		 * Info box with link to the bug tracker.
		 */
		function plugin_support() {
			$content = '<p>' . sprintf( __( 'If you\'re in need of support with Clicky and / or this plugin, please visit the %1$sClicky forums%2$s.', 'clicky' ), "<a href='https://clicky.com/forums/'>", '</a>' ) . '</p>';
			$this->postbox( $this->hook . 'support', __( 'Need Support?', 'clicky' ), $content );
		}

		/**
		 * Box with latest news from Clicky
		 */
		function news() {
			include_once( ABSPATH . WPINC . '/feed.php' );
			$rss       = fetch_feed( $this->feed );
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
			}
			$content .= '<li class="rss"><a href="' . $this->feed . '">' . __( 'Subscribe with RSS', 'clicky' ) . '</a></li>';
			$content .= '</ul>';
			$this->postbox( 'clickylatest', __( 'Latest news from Clicky', 'clicky' ), $content );
		}

		/**
		 * Box with latest news from Yoast.com for sidebar
		 */
		function yoast_news() {
			$rss       = fetch_feed( 'https://yoast.com/feed/' );
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( 3 ) );

			$content = '<ul>';
			if ( ! $rss_items ) {
				$content .= '<li class="yoast">' . __( 'No news items, feed might be broken...', 'clicky' ) . '</li>';
			} else {
				foreach ( $rss_items as $item ) {
					$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls = null, 'display' ) );
					$content .= '<li class="yoast">';
					$content .= '<a class="rsswidget" href="' . $url . '#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin">' . esc_html( $item->get_title() ) . '</a> ';
					$content .= '</li>';
				}
			}
			$content .= '<li class="facebook"><a href="https://www.facebook.com/yoast">' . __( 'Like Yoast on Facebook', 'clicky' ) . '</a></li>';
			$content .= '<li class="twitter"><a href="https://twitter.com/yoast">' . __( 'Follow Yoast on Twitter', 'clicky' ) . '</a></li>';
			$content .= '<li class="googleplus"><a href="https://plus.google.com/+Yoastcom/posts">' . __( 'Circle Yoast on Google+', 'clicky' ) . '</a></li>';
			$content .= '<li class="rss"><a href="https://yoast.com/feed/">' . __( 'Subscribe with RSS', 'clicky' ) . '</a></li>';
			$content .= '<li class="email"><a href="https://yoast.com/newsletter/">' . __( 'Subscribe by email', 'clicky' ) . '</a></li>';
			$content .= '</ul>';
			$this->postbox( 'yoastlatest', __( 'Latest news from Yoast', 'clicky' ), $content );
		}
	}
}
