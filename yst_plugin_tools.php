<?php
/**
 * Backend Class for use in all Yoast plugins
 * @version 0.2
 */

if (!class_exists('Clicky_Base_Plugin_Admin')) {
    /**
     * Class Clicky_Base_Plugin_Admin
     *
     * The Clicky for WordPress plugin by Yoast makes it easy for you to add your Clicky analytics tracking code to
     * your WordPress install, while also giving you some advanced tracking options.
     *
     * @link http://yoast.com/wordpress/clicky/
     */
    class Clicky_Base_Plugin_Admin {

        /**
         * Menu slug for WordPress admin
         *
         * @access private
         * @var string
         */
        var $hook = '';

        /**
         * Name of the plugin (long version)
         *
         * @access private
         * @var string
         */
        var $longname = '';

        /**
         * Name of the plugin (short version)
         *
         * @access private
         * @var string
         */
        var $shortname = '';

        /**
         * Variable 'ohzicon'
         *
         * @access private
         * @var string
         */
        var $ozhicon	= '';

        /**
         * Option name
         *
         * @access private
         * @var string
         */
        var $optionname = '';

        /**
         * Link to Clicky homepage
         *
         * @access private
         * @var string
         */
        var $homepage	= '';

        /**
         * Filename
         *
         * @access private
         * @var string
         */
        var $filename 	= '';

        /**
         * Access level used for creation of (admin) menu items
         *
         * @access private
         * @var string
         */
        var $accesslvl	= 'manage_options';

        /**
         * Loads the CSS file for styling the admin
         *
         * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
         */
        function config_page_styles() {
			if (isset($_GET['page']) && $_GET['page'] == $this->hook) {
				wp_enqueue_style('clicky-admin-css', WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/yst_plugin_tools.css');
			}
		}

        /**
         * Adds a submenu to the settings-page
         *
         * @link http://codex.wordpress.org/Function_Reference/add_options_page
         */
        function register_settings_page() {
			add_options_page($this->longname, $this->shortname, $this->accesslvl, $this->hook, array(&$this,'config_page'));
		}

        /**
         * Creates the link to the URL of the plugin options
         *
         * @link http://codex.wordpress.org/Function_Reference/admin_url
         * @return string|void Returns link to the plugin options page
         */
        function plugin_options_url() {
			return admin_url( 'options-general.php?page='.$this->hook );
		}

        /**
         * Add a link to the settings page to the plugins list
         *
         * @param array $links
         * @param string $file
         * @return array
         */
        function add_action_link( $links, $file ) {
			static $this_plugin;
			if( empty($this_plugin) ) $this_plugin = $this->filename;
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="' . $this->plugin_options_url() . '">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}

        /**
         * Placeholder for function config_page
         *
         * Can be filled in child classes
         */
        function config_page() {
			
		}

        /**
         * Create a Checkbox input field
         *
         * @param int $id ID of the new checkbox
         * @param string $label Text to be displayed next to the checkbox
         * @return string The complete checkbox
         */
        function checkbox($id, $label) {
			$options = get_option($this->optionname);
			return '<input type="checkbox" id="'.$id.'" name="'.$id.'"'. checked($options[$id],true,false).'/> <label for="'.$id.'">'.$label.'</label><br/>';
		}

        /**
         * Create a Text input field
         *
         * @param int $id ID of the new label
         * @param string $label Text to be displayed in the label
         * @return string The complete label
         */
        function textinput($id, $label) {
			$options = get_option($this->optionname);
			return '<label for="'.$id.'">'.$label.':</label><br/><input size="45" type="text" id="'.$id.'" name="'.$id.'" value="'.$options[$id].'"/><br/><br/>';
		}

        /**
         * Create a postbox widget
         *
         * @param int $id ID of the div of the postbox
         * @param string $title Title of the handle
         * @param string $content Content of the postbox
         */
        function postbox($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>
				<div class="inside">
					<?php echo $content; ?>
				</div>
			</div>
		<?php
			$this->toc .= '<li><a href="#'.$id.'">'.$title.'</a></li>';
		}	

        /**
         * Create a form table from an array of rows
         *
         * @param array $rows Array with data to fill the new table
         * @return string The complete table
         */
        function form_table($rows) {
			$content = '<table class="form-table">';
			$i = 1;
			foreach ($rows as $row) {
				$class = '';
				if ($i > 1) {
					$class .= 'yst_row';
				}
				if ($i % 2 == 0) {
					$class .= ' even';
				}
				$content .= '<tr class="'.$row['id'].'_row '.$class.'"><th valign="top" scrope="row">';
				if (isset($row['id']) && $row['id'] != '')
					$content .= '<label for="'.$row['id'].'">'.$row['label'].':</label>';
				else
					$content .= $row['label'];
				$content .= '</th><td valign="top">';
				$content .= $row['content'];
				$content .= '</td></tr>'; 
				if ( isset($row['desc']) && !empty($row['desc']) ) {
					$content .= '<tr class="'.$row['id'].'_row '.$class.'"><td colspan="2" class="yst_desc">'.$row['desc'].'</td></tr>';
				}
					
				$i++;
			}
			$content .= '</table>';
			return $content;
		}

        /**
         * Create a "plugin like" box.
         *
         * @param string $hook Hook the function needs to hook on. If empty code will be executed immediately.
         */
        function plugin_like($hook = '') {
			if (empty($hook)) {
				$hook = $this->hook;
			}
			$content = '<p>'.__('Why not do any or all of the following:', 'clicky' ).'</p>';
			$content .= '<ul>';
			$content .= '<li><a href="'.$this->homepage.'">'.__('Link to it so other folks can find out about it.', 'clicky' ).'</a></li>';
			$content .= '<li><a href="http://wordpress.org/extend/plugins/'.$hook.'/">'.__('Give it a 5 star rating on WordPress.org.', 'clicky' ).'</a></li>';
			$content .= '<li><a href="http://wordpress.org/extend/plugins/'.$hook.'/">'.__('Let other people know that it works with your WordPress setup.', 'clicky' ).'</a></li>';
			$content .= '</ul>';
			$this->postbox($hook.'like', __( 'Like this plugin?', 'clicky' ), $content);
		}	

        /**
         * Info box with link to the bug tracker.
         *
         * @param string $hook Hook the function needs to hook on. If empty code will be executed immediately.
         */
        function plugin_support($hook = '') {
			if (empty($hook)) {
				$hook = $this->hook;
			}
			$content = '<p>'.__("If you're in need of support with Clicky and / or this plugin, please visit the <a href='https://secure.getclicky.com/forums/'>Clicky forums</a>.", 'clicky').'</p>';
			$this->postbox($this->hook.'support', __('Need Support?','clicky'), $content);
		}

		/**
		 * Box with latest news from Clicky
		 */
        function news( ) {
			include_once(ABSPATH . WPINC . '/feed.php');
			$rss = fetch_feed( $this->feed );
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity(3) );
			$content = '<ul>';
			if ( !$rss_items ) {
			    $content .= '<li class="yoast">'.__( 'No news items, feed might be broken...', 'clicky' ).'</li>';
			} else {
			    foreach ( $rss_items as $item ) {
			    	$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls=null, 'display' ) );
					$content .= '<li class="yoast">';
					$content .= '<a class="rsswidget" href="'.$url.'#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin">'. esc_html( $item->get_title() ) .'</a> ';
					$content .= '</li>';
			    }
			}						
			$content .= '<li class="rss"><a href="'.$this->feed.'">'.__( 'Subscribe with RSS', 'clicky' ).'</a></li>';
			$content .= '</ul>';
			$this->postbox('clickylatest', __( 'Latest news from Clicky' , 'clicky' ), $content);
		}

		/**
		 * Box with latest news from Yoast.com for sidebar
		 */
        function yoast_news() {
			$rss = fetch_feed('http://feeds.feedburner.com/joostdevalk');
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity(3) );
			
			$content = '<ul>';
			if ( !$rss_items ) {
			    $content .= '<li class="yoast">'.__( 'No news items, feed might be broken...', 'clicky' ).'</li>';
			} else {
			    foreach ( $rss_items as $item ) {
			    	$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls=null, 'display' ) );
					$content .= '<li class="yoast">';
					$content .= '<a class="rsswidget" href="'.$url.'#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=clickywpplugin">'. esc_html( $item->get_title() ) .'</a> ';
					$content .= '</li>';
			    }
			}						
			$content .= '<li class="facebook"><a href="https://www.facebook.com/yoastcom">'.__( 'Like Yoast on Facebook', 'clicky' ).'</a></li>';
			$content .= '<li class="twitter"><a href="http://twitter.com/yoast">'.__( 'Follow Yoast on Twitter', 'clicky' ).'</a></li>';
			$content .= '<li class="googleplus"><a href="https://plus.google.com/115369062315673853712/posts">'.__( 'Circle Yoast on Google+', 'clicky' ).'</a></li>';
			$content .= '<li class="rss"><a href="'.$this->feed.'">'.__( 'Subscribe with RSS', 'clicky' ).'</a></li>';
			$content .= '<li class="email"><a href="http://yoast.com/wordpress-newsletter/">'.__( 'Subscribe by email', 'clicky' ).'</a></li>';
			$content .= '</ul>';
			$this->postbox('yoastlatest', __( 'Latest news from Yoast', 'clicky' ), $content);
		}

		/**
		 * Donation box
		 */
		function donate() {
			$this->postbox('donate','<strong class="red">'.__( 'Like this plugin?', 'clicky' ).'</strong>','<p><strong>'.__( 'Want to help make it better? All donations are used to improve this plugin, so donate $10, $20 or $50 now!', 'clicky' ).'</strong></p><form style="width:160px;margin:0 auto;" action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="KWQT234DEG7KY">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>'
			.'<p>'.__('Or you could:', 'clicky').'</p>'
			.'<ul>'
			.'<li><a href="http://wordpress.org/extend/plugins/clicky/">'.__('Rate the plugin 5★ on WordPress.org', 'clicky').'</a></li>'
			.'<li><a href="http://yoast.com/wordpress/clicky/#utm_source=wpadmin&utm_medium=sidebanner&utm_term=link&utm_campaign=clickywpplugin">'.__('Blog about it & link to the plugin page', 'clicky').'</a></li>'
			.'</ul>');
		}

        /**
         * Crops text to given length (keeps whole words only)
         *
         * @param string $text The text to crop
         * @param int $limit Maximum length of the result
         * @param string $finish What needs to be the end of the cropped text
         * @return string The cropped text
         */
        function text_limit( $text, $limit, $finish = ' [&hellip;]') {
			if( strlen( $text ) > $limit ) {
		    	$text = substr( $text, 0, $limit );
				$text = substr( $text, 0, - ( strlen( strrchr( $text,' ') ) ) );
				$text .= $finish;
			}
			return $text;
		}

	}
}

?>