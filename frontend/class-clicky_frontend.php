<?php

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Frontend Class the Clicky plugin
 */
class Clicky_Frontend {

	/**
	 * Holds the plugin options
	 * 
	 * @var array
	 */
	private $options = array();
	
	/**
	 * Class constructor
	 */
	function __construct() {
		$this->options = Clicky_Options::instance()->get();
		
		add_action( 'wp_footer', array( $this, 'script' ), 90 );

		add_action( 'comment_post', array( $this, 'track_comment' ), 10, 2 );
	}

	/**
	 * Add Clicky scripts to footer
	 */
	public function script() {
		if ( is_preview() ) {
			return;
		}

		echo '<!-- Clicky Web Analytics - https://clicky.com, WordPress Plugin by Yoast - https://yoast.com/wordpress/plugins/clicky/ -->';

		// Bail early if current user is admin and ignore admin is true
		if ( $this->options['ignore_admin'] && current_user_can( "manage_options" ) ) {
			echo "\n<!-- " . __( "Clicky tracking not shown because you're an administrator and you've configured Clicky to ignore administrators.", 'clicky' ) . " -->\n";

			return;
		}

		if ( $this->options['track_names'] ) {
			require 'views/comment_author_script.php';
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

		if ( isset( $this->options['outbound_pattern'] ) && trim( $this->options['outbound_pattern'] ) != '' ) {
			$patterns = explode( ',', $this->options['outbound_pattern'] );
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

		if ( isset( $this->options['cookies_disable'] ) && $this->options['cookies_disable'] ) {
			$clicky_extra .= "clicky_custom.cookies_disable = 1;\n";
		}

		require 'views/script.php';
	}

	/**
	 * Create the log for clicky
	 *
	 * @param array $a The array with basic log-data
	 *
	 * @return bool Returns true on success or false on failure
	 */
	function log( $a ) {
		if ( ! isset( $this->options['site_id'] ) || empty( $this->options['site_id'] ) || ! isset( $this->options['admin_site_key'] ) || empty( $this->options['admin_site_key'] ) ) {
			return false;
		}

		$type = $a['type'];
		if ( ! in_array( $type, array( "pageview", "download", "outbound", "click", "custom", "goal" ) ) ) {
			$type = "pageview";
		}

		$file = "https://in.getclicky.com/in.php?site_id=" . $this->options['site_id'] . "&sitekey_admin=" . $this->options['admin_site_key'] . "&type=" . $type;

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
			if ( ! preg_match( "`^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$`", $a['ip_address'] ) ) {
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
				if ( ! preg_match( "`^(https?|telnet|ftp)`", $a['href'] ) ) {
					return false;
				}
			} else {
				# all other action types must start with either a / or a #
				if ( ! preg_match( "`^(/|#)`", $a['href'] ) ) {
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
	function track_comment( $commentID, $comment_status ) {
		// Make sure to only track the comment if it's not spam (but do it for moderated comments).
		if ( $comment_status != 'spam' ) {
			$comment = get_comment( $commentID );
			// Only do this for normal comments, not for pingbacks or trackbacks
			if ( $comment->comment_type != 'pingback' && $comment->comment_type != 'trackback' ) {
				$this->log(
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
}