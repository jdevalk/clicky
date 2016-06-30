<?php
/**
 * @package Yoast\Clicky\FrontEnd
 */

/**
 * Backend Class the Clicky plugin
 */
class Clicky_Visitor_Graph {

	/**
	 * Will hold the color of bars in our image
	 *
	 * @var int
	 */
	private $bar_color;

	/**
	 * Will hold the visitor values for each hour
	 *
	 * @var array
	 */
	private $bar_values = array();

	/**
	 * Height of the generated image
	 *
	 * @var float
	 */
	private $bar_width = 0.02;

	/**
	 * The width of the gap between two bars
	 *
	 * @var int
	 */
	private $gap;

	/**
	 * Holds the generated image
	 *
	 * @var resource
	 */
	private $img;

	/**
	 * Height of the generated image
	 *
	 * @var int
	 */
	private $img_height = 20;

	/**
	 * Width of the generated image
	 *
	 * @var int
	 */
	private $img_width = 99;

	/**
	 * This holds the plugins options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * The ratio between a value and the overall image height
	 *
	 * @var int
	 */
	private $ratio;

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

		if ( empty( $this->options['site_id'] ) || empty( $this->options['site_key'] ) ) {
			return;
		}

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
		if ( false === $img_src ) {
			return;
		}

		$url   = 'https://secure.getclicky.com/stats/?site_id=' . $this->options['site_id'];
		$title = __( 'Visitors over 48 hours. Click for more Clicky Site Stats.', 'clicky' );

		$menu = array(
			'id'    => 'clickystats',
			'title' => "<img width='99' height='20' src='" . $img_src . "' alt='" . esc_attr( $title ) . "' title='" . esc_attr( $title ) . "' />",
			'href'  => $url,
		);

		$wp_admin_bar->add_menu( $menu );
	}

	/**
	 * Creates the graph to be used in the admin bar
	 *
	 * @return bool|string Returns base64-encoded image on success (String) or fail (boolean) on failure
	 */
	private function create_graph() {
		$result = $this->retrieve_clicky_api_details();
		if ( false === $result ) {
			return false;
		}

		if ( count( $this->bar_values ) < 1 ) {
			return false;
		}

		$this->create_base_image();
		$this->calculate_ratio();
		$this->add_bars_to_image();

		return $this->build_img();
	}

	/**
	 * Retrieve the visitor data from the Clicky API
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_remote_get
	 */
	private function retrieve_clicky_api_details() {
		$args = array(
			'site_id' => $this->options['site_id'],
			'sitekey' => $this->options['site_key'],
			'type'    => 'visitors',
			'hourly'  => 1,
			'date'    => 'last-3-days',
			'output'  => 'json',
		);
		$url  = 'https://api.getclicky.com/api/stats/4?' . http_build_query( $args );

		$resp = wp_remote_get( $url );

		if ( is_wp_error( $resp ) || ! isset( $resp['response']['code'] ) || $resp['response']['code'] != 200 ) {
			return false;
		}

		$results = $this->parse_clicky_results( $resp['body'] );
		if ( ! is_array( $results ) ) {
			return false;
		}

		$this->bar_values = $results;

		return true;
	}

	/**
	 * Parse the Clicky results into a usable array
	 *
	 * @param array $json JSON encoded object of results.
	 *
	 * @return array|bool
	 */
	private function parse_clicky_results( $json ) {
		$json = json_decode( $json );

		if ( empty( $json ) ) {
			return false;
		}

		if ( isset( $json[0]->error ) ) {
			return false;
		}

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
	 * Creates the basic rectangle we'll project the bars on
	 */
	private function create_base_image() {
		$this->img = imagecreate( $this->img_width, $this->img_height );

		$black            = imagecolorallocate( $this->img, 0, 0, 0 );
		$background_color = imagecolortransparent( $this->img, $black );

		imagefilledrectangle( $this->img, 0, 0, $this->img_width, $this->img_height, $background_color );
	}

	/**
	 * Calculate the ratio between the max value of the bar values and the image height to adjust bars length
	 */
	private function calculate_ratio() {
		$max_value = max( $this->bar_values );
		if ( $max_value === 0 ) {
			$max_value = 1;
		}
		$this->ratio = ( $this->img_height / $max_value );
	}

	/**
	 * Create the individual bars on the image
	 */
	private function add_bars_to_image() {
		$this->bar_color = imagecolorallocate( $this->img, 240, 240, 240 );
		$total_bars      = count( $this->bar_values ); // Normally 48, but less if there's less data.
		$this->gap       = ( ( $this->img_width - $total_bars * $this->bar_width ) / ( $total_bars + 1 ) );

		foreach ( $this->bar_values as $key => $value ) {
			$this->create_bar( $key, $value );
		}
	}

	/**
	 * Create an individual bar on the image
	 *
	 * @param int $index  Offset.
	 * @param int $height Height.
	 */
	private function create_bar( $index, $height ) {
		$xAxis1 = ( $this->gap + $index * ( $this->gap + $this->bar_width ) );
		$xAxis2 = ( $xAxis1 + $this->bar_width );
		$yAxis1 = ( $this->img_height - intval( $height * $this->ratio ) );
		$yAxis2 = $this->img_height;
		imagefilledrectangle( $this->img, $xAxis1, $yAxis1, $xAxis2, $yAxis2, $this->bar_color );
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
