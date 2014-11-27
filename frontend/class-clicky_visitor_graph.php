<?php

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Backend Class the Clicky plugin
 */

class Clicky_Visitor_Graph {

	/**
	 * Width of the generated image
	 * @var int
	 */
	private $img_width  = 99;

	/**
	 * Height of the generated image
	 *
	 * @var int
	 */
	private $img_height = 20;

	/**
	 * Margins around the generated image
	 * @var int
	 */
	private $margins    = 0;

	/**
	 * This holds the plugins options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		if ( ! function_exists( 'imagecreate' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->options = Clicky_Options::instance()->get();

		if ( isset( $this->options['disable_stats'] ) && $this->options['disable_stats'] ) {
			return;
		}

		add_action( 'wp_head', array( $this, 'stats_css' ) );
		add_action( 'admin_bar_menu', array( $this, 'stats_admin_bar_menu' ), 100 );
	}

	/**
	 * Creates (CSS for) head for the admin menu bar
	 *
	 * @link https://codex.wordpress.org/Function_Reference/add_action
	 */
	public function stats_css() {
		$ext = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.css' : '.min.css';

		echo "\n";
		echo "<style type='text/css'>\n";
		echo file_get_contents( CLICKY_PLUGIN_DIR_PATH . '/css/adminbar' . $ext );
		echo "\n";
		echo "</style>\n";
	}

	/**
	 * Adds Clicky (graph) to the admin bar of the website
	 *
	 * @param object $wp_admin_bar Class that contains all information for the admin bar. Passed by reference.
	 *
	 * @link https://codex.wordpress.org/Class_Reference/WP_Admin_Bar
	 */
	public function stats_admin_bar_menu( $wp_admin_bar ) {
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
	 * Retrieve the visitor data from the Clicky API
	 *
	 * @return bool|SimpleXMLElement
	 */
	private function retrieve_clicky_api_details() {
		$resp = wp_remote_get( "https://api.getclicky.com/api/stats/4?site_id=" . $this->options['site_id'] . "&sitekey=" . $this->options['site_key'] . "&type=visitors&hourly=1&date=last-3-days" );

		if ( is_wp_error( $resp ) || ! isset( $resp['response']['code'] ) || $resp['response']['code'] != 200 ) {
			return false;
		}

		$xml = simplexml_load_string( $resp['body'] );

		if ( ! $xml ) {
			return false;
		}

		$values = $this->parse_clicky_results( $xml );

		return $values;
	}

	/**
	 * Parse the Clicky resultset into a usable array
	 *
	 * @param SimpleXMLElement $xml
	 *
	 * @return array|bool
	 */
	private function parse_clicky_results( $xml ) {
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

		return $values;
	}

	/**
	 * Use the image input to build a PNG then returns it as a base64 encoded image usable in a src tag
	 *
	 * @param resource $image_res an image resource identifier
	 *
	 * @return string
	 */
	private function build_img( $image_res ) {
		ob_start();
		imagepng( $image_res );
		$image = ob_get_contents();
		ob_end_clean();

		$image = 'data:image/png;base64,' . base64_encode( $image );

		return $image;
	}

	/**
	 * Creates the graph to be used in the admin bar
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
	 * @link https://codex.wordpress.org/Function_Reference/is_wp_error
	 *
	 * @return bool|string Returns base64-encoded image on success (String) or fail (boolean) on failure
	 */
	private function create_graph() {
		$values = $this->retrieve_clicky_api_details();

		if ( $values === false ) {
			return false;
		}

		# ---- Find the size of graph by substracting the size of borders
		$graph_width  = $this->img_width - $this->margins * 2;
		$graph_height = $this->img_height - $this->margins * 2;
		$img          = imagecreate( $this->img_width, $this->img_height );

		$bar_width  = 0.01;
		$total_bars = count( $values );
		$gap        = ( $graph_width - $total_bars * $bar_width ) / ( $total_bars + 1 );

		# -------  Define Colors ----------------
		$bar_color = imagecolorallocate( $img, 220, 220, 220 );

		$black            = imagecolorallocate( $img, 0, 0, 0 );
		$background_color = imagecolortransparent( $img, $black );
		$border_color     = imagecolorallocate( $img, 50, 50, 50 );

		# ------ Create the border around the graph ------

		imagefilledrectangle( $img, 1, 1, $this->img_width - 2, $this->img_height - 2, $border_color );
		imagefilledrectangle( $img, $this->margins, $this->margins, $this->img_width - 1 - $this->margins, $this->img_height - 1 - $this->margins, $background_color );

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
			$x1 = $this->margins + $gap + $i * ( $gap + $bar_width );
			$x2 = $x1 + $bar_width;
			$y1 = $this->margins + $graph_height - intval( $value * $ratio );
			$y2 = $this->img_height - $this->margins;
			imagefilledrectangle( $img, $x1, $y1, $x2, $y2, $bar_color );
		}

		return $this->build_img( $img );
	}
}