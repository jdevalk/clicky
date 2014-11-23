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
		$this->options = Clicky_Options::instance()->get();

		add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );

		add_action( 'admin_head', array( $this, 'i18n_module' ) );
	}

	/**
	 * Enqueue the styles for the admin page
	 */
	function config_page_styles() {
		$ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.css' : '.min.css';
		wp_enqueue_style( 'clicky-admin-css', CLICKY_PLUGIN_DIR_URL . 'css/clicky_admin' . $ext );
	}

	/**
	 * Save the settings if the user has the right to do so.
	 *
	 * @uses clicky_get_options()
	 *
	 * @link https://codex.wordpress.org/Function_Reference/current_user_can
	 * @link https://codex.wordpress.org/Function_Reference/check_admin_referer
	 * @link https://codex.wordpress.org/Function_Reference/update_option
	 * @return string $message
	 */
	function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( __( 'You cannot edit the Clicky settings.', 'clicky' ) );
		}
		check_admin_referer( 'clicky-config' );

		foreach ( array( 'site_id', 'site_key', 'admin_site_key', 'outbound_pattern' ) as $option_name ) {
			if ( isset( $_POST[ $option_name ] ) ) {
				$this->options[ $option_name ] = sanitize_text_field( $_POST[ $option_name ] );
			} else {
				$this->options[ $option_name ] = '';
			}
		}

		foreach ( array( 'ignore_admin', 'track_names', 'cookies_disable' ) as $option_name ) {
			if ( isset( $_POST[ $option_name ] ) ) {
				$this->options[ $option_name ] = true;
			} else {
				$this->options[ $option_name ] = false;
			}
		}

		if ( clicky_get_options() != $this->options ) {
			update_option( 'clicky', $this->options );

			return "<p>" . __( 'Clicky settings have been updated.', 'clicky' ) . "</p>";
		}
	}

	/**
	 * Creates the configuration page for Clicky for WordPress by Yoast
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_nonce_field
	 */
	public function config_page() {
		if ( isset( $_POST['submit'] ) ) {
			$message = $this->save_settings();
		}

		if ( isset( $error ) && $error != "" ) {
			echo "<div id=\"message\" class=\"error\">$error</div>\n";
		} elseif ( isset( $message ) && $message != "" ) {
			echo "<div id=\"updatemessage\" class=\"updated fade\">$message</div>\n";
			echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
		}
		?>
		<div class="wrap">
			<h2>
				<img id="clicky-icon" src="<?php echo CLICKY_PLUGIN_DIR_URL; ?>images/clicky-32x32.png" class="alignleft" /> Clicky <?php _e( "Configuration", 'clicky' ); ?>
			</h2>

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
								'content' => '<input class="text" type="text" value="' . $this->options['site_id'] . '" name="site_id" id="site_id"/>',
							);

							$rows[] = array(
								'id'      => 'site_key',
								'label'   => __( 'Site Key', 'clicky' ),
								'desc'    => '',
								'content' => '<input class="text" type="text" value="' . $this->options['site_key'] . '" name="site_key" id="site_key"/>',
							);

							$rows[] = array(
								'id'      => 'admin_site_key',
								'label'   => __( 'Admin Site Key', 'clicky' ),
								'desc'    => '',
								'content' => '<input class="text" type="text" value="' . $this->options['admin_site_key'] . '" name="admin_site_key" id="admin_site_key"/>',
							);

							$content .= ' ' . $this->form_table( $rows );
							$this->postbox( 'clicky_settings', __( 'Clicky Settings', 'clicky' ), $content );

							$this->plugin_support();

							$rows   = array();
							$rows[] = array(
								'id'      => 'ignore_admin',
								'label'   => __( 'Ignore Admin users', 'clicky' ),
								'desc'    => __( 'If you are using a caching plugin, such as W3 Total Cache or WP-Supercache, please ensure that you have it configured to NOT use the cache for logged in users. Otherwise, admin users <em>will still</em> be tracked.', 'clicky' ),
								'content' => '<input type="checkbox" ' . checked( $this->options['ignore_admin'], true, false ) . ' name="ignore_admin" id="ignore_admin"/>',
							);

							$rows[] = array(
								'id'      => 'cookies_disable',
								'label'   => __( 'Disable cookies', 'clicky' ),
								'desc'    => __( 'If you don\'t want Clicky to use cookies on your site, check this button. By doing so, uniques will instead be determined based on their IP address.', 'clicky' ),
								'content' => '<input type="checkbox" ' . checked( $this->options['cookies_disable'], true, false ) . ' name="cookies_disable" id="cookies_disable"/>',
							);

							$rows[] = array(
								'id'      => 'track_names',
								'label'   => __( 'Track names of commenters', 'clicky' ),
								'desc'    => '',
								'content' => '<input type="checkbox" ' . checked( $this->options['track_names'], true, false ) . ' name="track_names" id="track_names"/>'
							);

							$rows[] = array(
								'id'      => 'outbound_pattern',
								'label'   => __( 'Outbound Link Pattern', 'clicky' ),
								'desc'    => sprintf( __( 'If your site uses redirects for outbound links, instead of links that point directly to their external source (this is popular with affiliate links, for example), then you\'ll need to use this variable to tell our tracking code additional patterns to look for when automatically tracking outbound links. %1$sRead more here%1$s.', 'clicky' ), '<a href="https://secure.getclicky.com/helpy?type=customization#outbound_pattern">', '</a>' ),
								'content' => '<input class="text" type="text" value="' . $this->options['outbound_pattern'] . '" name="outbound_pattern" id="outbound_pattern"/> ' . __( 'For instance: <code>/out/,/go/</code>', 'clicky' ),
							);

							$this->postbox( 'clicky_settings', __( 'Advanced Settings', 'clicky' ), $this->form_table( $rows ) );

							?>
							<div class="submit">
								<input type="submit" class="button-primary" name="submit"
								       value="<?php _e( "Update Clicky Settings", 'clicky' ); ?> &raquo;" />
							</div>
						</form>

						<?php do_action( 'clicky_admin_footer' ); ?>
					</div>
				</div>
			</div>
			<div class="postbox-container" style="width:20%;">
				<div class="metabox-holder">
					<div class="meta-box-sortables">
						<?php
						$this->plugin_like();
						$this->yoast_news();
						$this->clicky_news();
						?>
					</div>
					<br /><br /><br />
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Create a Checkbox input field
	 */
	function checkbox( $id, $label ) {
		return '<input type="checkbox" id="' . $id . '" name="' . $id . '"' . checked( $this->options[ $id ], true, false ) . '/> <label for="' . $id . '">' . $label . '</label><br/>';
	}

	/**
	 * Create a Text input field
	 */
	function textinput( $id, $label ) {
		return '<label for="' . $id . '">' . $label . ':</label><br/><input size="45" type="text" id="' . $id . '" name="' . $id . '" value="' . $this->options[ $id ] . '"/><br/><br/>';
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
	 * Generate an RSS box.
	 *
	 * @param string $id          ID to use for the box
	 * @param string $feed        Feed URL to parse
	 * @param string $title       Title of the box
	 * @param string $extra_links Additional links to add to the output, after the RSS subscribe link
	 */
	function rss_news( $id, $feed, $title, $extra_links = '' ) {
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

		$this->postbox( $id, $title, $content );
	}

	/**
	 * Box with latest news from Clicky
	 */
	function clicky_news() {
		$this->rss_news( 'clickylatest', 'http://clicky.com/blog/rss', __( 'Latest news from Clicky', 'clicky' ) );
	}

	/**
	 * Box with latest news from Yoast.com for sidebar
	 */
	function yoast_news() {
		$extra_links = '<li class="facebook"><a href="https://www.facebook.com/yoast">' . __( 'Like Yoast on Facebook', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="twitter"><a href="https://twitter.com/yoast">' . __( 'Follow Yoast on Twitter', 'clicky' ) . '</a></li>';
		$extra_links .= '<li class="email"><a href="https://yoast.com/newsletter/">' . __( 'Subscribe by email', 'clicky' ) . '</a></li>';

		$this->rss_news( 'yoastlatest', 'https://yoast.com/feed/', __( 'Latest news from Yoast', 'clicky' ), $extra_links );
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