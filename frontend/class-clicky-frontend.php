<?php
/**
 * @package Yoast\Clicky\FrontEnd
 */

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
	public function __construct() {
		$this->options = Clicky_Options::instance()->get();

		if ( empty( $this->options['site_id'] ) || empty( $this->options['site_key'] ) ) {
			return;
		}

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

		// Bail early if current user is admin and ignore admin is true.
		if ( $this->options['ignore_admin'] && current_user_can( 'manage_options' ) ) {
			echo "\n<!-- " . __( "Clicky tracking not shown because you're an administrator and you've configured Clicky to ignore administrators.", 'clicky' ) . " -->\n";

			return;
		}

		if ( $this->options['track_names'] ) {
			require 'views/comment-author-script.php';
		}

		$clicky_extra = $this->goal_tracking();
		$clicky_extra .= $this->outbound_tracking();
		$clicky_extra .= $this->disable_cookies();

		require 'views/script.php';
	}

	/**
	 * Handles the script generation for goal tracking
	 *
	 * @return string Script code
	 */
	private function goal_tracking() {
		if ( is_singular() ) {
			global $post;
			$clicky_goal = get_post_meta( $post->ID, '_clicky_goal', true );
			if ( is_array( $clicky_goal ) && ! empty( $clicky_goal['id'] ) ) {
				$script = 'var clicky_goal = { id: "' . trim( esc_attr( $clicky_goal['id'] ) ) . '"';
				if ( isset( $clicky_goal['value'] ) && ! empty( $clicky_goal['value'] ) ) {
					$script .= ', revenue: "' . esc_attr( $clicky_goal['value'] ) . '"';
				}
				$script .= ' };' . "\n";

				return $script;
			}
		}

		return '';
	}

	/**
	 * Handles the script generation for outbound link tracking
	 *
	 * @return string
	 */
	private function outbound_tracking() {
		if ( isset( $this->options['outbound_pattern'] ) && trim( $this->options['outbound_pattern'] ) != '' ) {

			$patterns = preg_replace( '~[^\/a-zA-Z0-9,]+~', '', $this->options['outbound_pattern'] );

			$patterns = explode( ',', $patterns );
			$pattern  = '';
			foreach ( $patterns as $pat ) {
				if ( $pattern != '' ) {
					$pattern .= ',';
				}
				$pat = trim( str_replace( '"', '', str_replace( "'", '', $pat ) ) );
				$pattern .= "'" . $pat . "'";
			}

			return 'clicky_custom.outbound_pattern = [' . $pattern . '];' . "\n";
		}

		return '';
	}

	/**
	 * Determines whether or not we should disable cookie usage
	 *
	 * @return string
	 */
	private function disable_cookies() {
		if ( isset( $this->options['cookies_disable'] ) && $this->options['cookies_disable'] ) {
			return "clicky_custom.cookies_disable = 1;\n";
		}

		return '';
	}

	/**
	 * Tracks comments that are not spam and not ping- or trackbacks
	 *
	 * @param int $commentID      The ID of the comment that needs to be tracked.
	 * @param int $comment_status Status of the comment (e.g. spam).
	 */
	public function track_comment( $commentID, $comment_status ) {
		// Make sure to only track the comment if it's not spam (but do it for moderated comments).
		if ( $comment_status != 'spam' ) {
			$comment = get_comment( $commentID );
			// Only do this for normal comments, not for pingbacks or trackbacks.
			if ( $comment->comment_type != 'pingback' && $comment->comment_type != 'trackback' ) {
				$args   = array(
					'type'       => 'click',
					'href'       => '/wp-comments-post.php',
					'title'      => __( 'Posted a comment', 'clicky' ),
					'ua'         => $comment->comment_agent,
					'ip_address' => $comment->comment_author_IP,
				);
				$custom = array(
					'username' => $comment->comment_author,
					'email'    => $comment->comment_author_email,
				);

				$this->log_comment( $args, $custom );
			}
		}
	}

	/**
	 * Create the log for clicky
	 *
	 * @param array $log_data The array with basic log-data.
	 * @param array $custom   The array with custom log-data for the comment author.
	 */
	private function log_comment( $log_data, $custom ) {
		$log_data['site_id']       = $this->options['site_id'];
		$log_data['sitekey_admin'] = $this->options['admin_site_key'];

		$file = 'https://in.getclicky.com/in.php?' . http_build_query( $log_data );

		// Custom data, must come in as array of key=>values.
		foreach ( $custom as $key => $value ) {
			$file .= '&custom[' . urlencode( $key ) . ']=' . urlencode( $value );
		}

		wp_remote_get( $file );
	}
}
