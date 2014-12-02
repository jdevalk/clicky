<?php

/**
 * Backend Class the Clicky plugin
 */
class Clicky_Visitor_Graph {

	/**
	 * Will hold the visitor values for each hour
	 *
	 * @var array
	 */
	private $bar_values = array();

	/**
	 * Holds the generated image
	 *
	 * @var resource
	 */
	private $img;

	/**
	 * Width of the generated image
	 * @var int
	 */
	private $img_width = 99;

	/**
	 * Height of the generated image
	 *
	 * @var int
	 */
	private $img_height = 20;

	/**
	 * Height of the generated image
	 *
	 * @var float
	 */
	private $bar_width  = 0.02;

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
		readfile( CLICKY_PLUGIN_DIR_PATH . '/css/adminbar' . $ext );
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
	 * Creates the basic rectangle we'll project the bars on
	 */
	private function create_base_image() {
		$this->img = imagecreate( $this->img_width, $this->img_height );

		$black            = imagecolorallocate( $this->img, 0, 0, 0 );
		$background_color = imagecolortransparent( $this->img, $black );

		imagefilledrectangle( $this->img, 0, 0, $this->img_width, $this->img_height, $background_color );
	}

	/**
	 * Create the individual bars on the image
	 */
	private function add_bars_to_image() {
		$bar_color  = imagecolorallocate( $this->img, 240, 240, 240 );
		$total_bars = count( $this->bar_values ); // Normally 48, but less if there's less data
		$gap        = ( $this->img_width - $total_bars * $this->bar_width ) / ( $total_bars + 1 );

		# ------- Max value is required to adjust the scale	-------
		$max_value = max( $this->bar_values );
		if ( $max_value == 0 ) {
			$max_value = 1;
		}
		$ratio = $this->img_height / $max_value;

		foreach( $this->bar_values as $key => $value ) {
			$x1 = $gap + $key * ( $gap + $this->bar_width );
			$x2 = $x1 + $this->bar_width;
			$y1 = $this->img_height - intval( $value * $ratio );
			imagefilledrectangle( $this->img, $x1, $y1, $x2, $this->img_height, $bar_color );
		}
	}

	/**
	 * Creates the graph to be used in the admin bar
	 *
	 * @link https://codex.wordpress.org/Function_Reference/is_wp_error
	 *
	 * @return bool|string Returns base64-encoded image on success (String) or fail (boolean) on failure
	 */
	private function create_graph() {
		$this->retrieve_clicky_api_details();

		if ( count( $this->bar_values ) < 1 ) {
			return false;
		}

		$this->create_base_image();
		$this->add_bars_to_image();

		return $this->build_img();
	}

	/**
	 * Retrieve the visitor data from the Clicky API
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
	 *
	 * @return array Array of values
	 */
	private function retrieve_clicky_api_details() {
		$args = array(
			'site_id' => $this->options['site_id'],
			'sitekey' => $this->options['site_key'],
			'type'    => 'visitors',
			'hourly'  => 1,
			'date'    => 'last-3-days',
			'output'  => 'json'
		);
		$url  = "https://api.getclicky.com/api/stats/4?" . http_build_query( $args );

		$resp = wp_remote_get( $url );

		if ( is_wp_error( $resp ) || ! isset( $resp['response']['code'] ) || $resp['response']['code'] != 200 ) {
			return false;
		}

		$this->bar_values = $this->parse_clicky_results( $resp['body'] );
	}

	/**
	 * Parse the Clicky results into a usable array
	 *
	 * @param array $json JSON encoded object of results
	 *
	 * @return array|bool
	 */
	private function parse_clicky_results( $json ) {

		$json = json_decode( $json );

		$hours  = 0;
		$values = array();

		foreach ( $json[0]->dates as $date ) {
			foreach ( $date->items as $item ) {
				if ( $hours === 48 ) {
					break 2;
				}
				$values[] = $item->value;
				$hours ++;
			}
		}
		$values = array_reverse( $values );

		return $values;
	}

	/**
	 * Use the image input to build a PNG then returns it as a base64 encoded image usable in a src tag
	 *
	 * @return string
	 */
	private function build_img() {
		ob_start();
		imagepng( $this->img );
		$image = ob_get_contents();
		ob_end_clean();

		$image = 'data:image/png;base64,' . base64_encode( $image );

		return $image;
	}
}