<?php
/**
 * @package Yoast\Clicky\Admin
 */

/**
 * Backend Class for the Clicky plugin options
 */
class Clicky_Options_Admin extends Clicky_Options {

	/**
	 * The option group name
	 *
	 * @var string
	 */
	public static $option_group = 'clicky_options';

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		parent::__construct();
	}

	/**
	 * Register the needed option and its settings sections
	 */
	public function admin_init() {
		register_setting( self::$option_group, parent::$option_name, array( $this, 'sanitize_options_on_save' ) );

		$this->register_basic_settings();
		$this->register_advanced_settings();
		$this->register_outbound_settings();
	}

	/**
	 * Register the basic settings
	 */
	private function register_basic_settings() {
		add_settings_section( 'basic-settings', __( 'Basic settings', 'clicky' ), array(
			$this,
			'basic_settings_intro',
		), 'clicky' );

		$clicky_settings = array(
			'site_id'        => __( 'Site ID', 'clicky' ),
			'site_key'       => __( 'Site Key', 'clicky' ),
			'admin_site_key' => __( 'Admin Site Key', 'clicky' ),
		);
		foreach ( $clicky_settings as $key => $label ) {
			add_settings_field( $key, $label, array( $this, 'input_text' ), 'clicky', 'basic-settings', array(
				'name'  => 'clicky[' . $key . ']',
				'value' => $this->options[ $key ],
			) );
		}

		add_settings_section( 'clicky-like', __( 'Like this plugin?', 'clicky' ), array(
			$this,
			'like_text',
		), 'clicky' );

		add_settings_section( 'clicky-support', __( 'Need support?', 'clicky' ), array(
			$this,
			'support_text',
		), 'clicky' );
	}

	/**
	 * Register the separate advanced settings screen
	 */
	private function register_advanced_settings() {
		add_settings_section( 'clicky-advanced', __( 'Advanced Settings', 'clicky' ), null, 'clicky-advanced' );

		$advanced_settings = array(
			'disable_stats'   => array(
				'label' => __( 'Disable Admin Bar stats', 'clicky' ),
				'desc'  => __( 'If you don\'t want to display the stats in your admin menu, check this box.', 'clicky' ),
			),
			'ignore_admin'    => array(
				'label' => __( 'Ignore Admin users', 'clicky' ),
				'desc'  => __( 'If you are using a caching plugin, such as W3 Total Cache or WP-Supercache, please ensure that you have it configured to NOT use the cache for logged in users. Otherwise, admin users <em>will still</em> be tracked.', 'clicky' ),
			),
			'cookies_disable' => array(
				'label' => __( 'Disable cookies', 'clicky' ),
				'desc'  => __( 'If you don\'t want Clicky to use cookies on your site, check this button. By doing so, uniques will instead be determined based on their IP address.', 'clicky' ),
			),
			'track_names'     => array(
				'label' => __( 'Track names of commenters', 'clicky' ),
			),
		);
		foreach ( $advanced_settings as $key => $arr ) {
			add_settings_field( $key, $arr['label'], array(
				$this,
				'input_checkbox',
			), 'clicky-advanced', 'clicky-advanced', array(
				'name'  => $key,
				'value' => isset( $this->options[ $key ] ) ? $this->options[ $key ] : false,
				'desc'  => isset( $arr['desc'] ) ? $arr['desc'] : '',
			) );
		}
	}

	/**
	 * Register the outbound links settings section
	 */
	private function register_outbound_settings() {
		add_settings_section( 'clicky-outbound', __( 'Outbound Links', 'clicky' ), array( $this, 'outbound_explanation' ), 'clicky-advanced' );

		add_settings_field( 'outbound_pattern', __( 'Outbound Link Pattern', 'clicky' ), array( $this, 'input_text' ), 'clicky-advanced', 'clicky-outbound', array(
			'name'  => 'clicky[outbound_pattern]',
			'value' => $this->options['outbound_pattern'],
			'desc'  => __( 'For instance: <code>/out/,/go/</code>', 'clicky' ),
		) );
	}

	/**
	 * Create a "plugin like" box.
	 */
	public function like_text() {
		require 'views/like-box.php';
	}

	/**
	 * Sanitizes and trims a string
	 *
	 * @param string $string String to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_string( $string ) {
		return (string) trim( sanitize_text_field( $string ) );
	}

	/**
	 * Sanitize options
	 *
	 * @param array $new_options Options to sanitize.
	 *
	 * @return array
	 */
	public function sanitize_options_on_save( $new_options ) {
		foreach ( $new_options as $key => $value ) {
			switch ( self::$option_var_types[ $key ] ) {
				case 'string':
					$new_options[ $key ] = $this->sanitize_string( $new_options[ $key ] );
					break;
				case 'bool':
					if ( isset( $new_options[ $key ] ) ) {
						$new_options[ $key ] = true;
					}
					else {
						$new_options[ $key ] = false;
					}
					break;
			}

			switch ( $key ) {
				case 'site_id':
					$new_options[ $key ] = (int) $new_options[ $key ];
					if ( $new_options[ $key ] === 0 ) {
						$new_options[ $key ] = '';
					}
					break;

				case 'site_key':
				case 'admin_site_key':
					$new_options[ $key ] = preg_replace( '~[^a-zA-Z0-9]+~', '', $new_options[ $key ] );
					break;
			}
		}

		return $new_options;
	}

	/**
	 * Intro for the basic settings screen
	 */
	public function basic_settings_intro() {
		echo '<p>';
		printf( __( 'Go to your %1$suser homepage on Clicky%2$s and click &quot;Preferences&quot; under the name of the domain, you will find the Site ID, Site Key, Admin Site Key and Database Server under Site information.', 'clicky' ), '<a href="http://clicky.com/145844">', '</a>' );
		echo '</p>';
	}

	/**
	 * Intro for the the outbound links section
	 */
	public function outbound_explanation() {
		echo '<p>';
		printf( __( 'If your site uses redirects for outbound links, instead of links that point directly to their external source (this is popular with affiliate links, for example), then you\'ll need to use this variable to tell our tracking code additional patterns to look for when automatically tracking outbound links. %1$sRead more here%2$s.', 'clicky' ), '<a href="https://secure.getclicky.com/helpy?type=customization#outbound_pattern">', '</a>' );
		echo '</p>';
	}

	/**
	 * Text for the support box
	 */
	public function support_text() {
		echo '<p>' . sprintf( __( 'If you\'re in need of support with Clicky and / or this plugin, please visit the %1$sClicky forums%2$s.', 'clicky' ), "<a href='https://clicky.com/forums/'>", '</a>' ) . '</p>';
	}

	/**
	 * Output an optional input description
	 *
	 * @param array $args Arguments to get data from.
	 */
	private function input_desc( $args ) {
		if ( isset( $args['desc'] ) ) {
			echo '<p class="description">' . $args['desc'] . '</p>';
		}
	}

	/**
	 * Create a text input
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_text( $args ) {
		echo '<input type="text" class="text" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '"/>';
		$this->input_desc( $args );
	}

	/**
	 * Create a checkbox input
	 *
	 * @param array $args Arguments to get data from.
	 */
	public function input_checkbox( $args ) {
		$option = isset( $this->options[ $args['name'] ] ) ? $this->options[ $args['name'] ] : false;
		echo '<input class="checkbox" type="checkbox" ' . checked( $option, true, false ) . ' name="clicky[' . esc_attr( $args['name'] ) . ']"/>';
		$this->input_desc( $args );
	}
}
