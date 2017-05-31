<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/admin
 * @author     Krister Bogstag <krister@bogstag.se>
 */
class Woo_Advanced_Price_Setter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->set_options();

	}

	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options = get_option( $this->plugin_name . '-options' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Advanced_Price_Setter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Advanced_Price_Setter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-advanced-price-setter-admin.css',
			[], $this->version, 'all'
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Advanced_Price_Setter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Advanced_Price_Setter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-advanced-price-setter-admin.js',
			[ 'jquery' ], $this->version, true
		);
		global $post;
		wp_localize_script( 'aps_dryrun', 'aps_dryrun_vars', [
				'postid' => $post->ID,
			]
		);

	}

	/**
	 * Adds a link to the plugin settings page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links The current array of links.
	 *
	 * @return array The modified array of links
	 */
	public function link_settings( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . $this->plugin_name ) ),
			esc_html__( 'Settings', 'woo-advanced-price-setter' )
		);

		return $links;
	}

	/**
	 * Format numbers to match woo specs.
	 *
	 * @param float $number Run numbers thru woo validator.
	 *
	 * @return string
	 */
	public function format_number( $number ) {
		return wc_format_decimal( $number, false, false );
	}

	/**
	 * Registers plugin settings
	 *
	 * @since        1.0.0
	 * @return        void
	 */
	public function register_settings() {
		register_setting( $this->plugin_name . '-options', $this->plugin_name . '-options',
			[ $this, 'validate_options' ]
		);
	}

	/**
	 * Some simple validation.
	 *
	 * @param array $options Get options and validate them.
	 *
	 * @return mixed
	 */
	public function validate_options( $options ) {
		$options['dollar_rate'] = $this->format_number( $options['dollar_rate'] );

		return $options;
	}

	/**
	 * Registers settings sections with WordPress
	 */
	public function register_sections() {
		add_settings_section( $this->plugin_name . '-calculating-options',
			apply_filters( $this->plugin_name . 'section_calculating_options',
				esc_html__( 'Calculating options', 'woo-advanced-price-setter' )
			), [ $this, 'section_calculating_options' ], $this->plugin_name
		);

		add_settings_section( $this->plugin_name . '-wholesale-mark',
			apply_filters( $this->plugin_name . 'section_wholesale_mark',
				esc_html__( 'Wholesale mark', 'woo-advanced-price-setter' )
			), [ $this, 'section_wholesale_mark' ], $this->plugin_name
		);

		add_settings_section( $this->plugin_name . '-general-options',
			apply_filters( $this->plugin_name . 'section_general_options',
				esc_html__( 'General options', 'woo-advanced-price-setter' )
			), [ $this, 'section_general_options' ], $this->plugin_name
		);
	}

	/**
	 *
	 */
	public function section_calculating_options() {
		echo '<p>This options affect everything.</p>';
	}

	/**
	 *
	 */
	public function section_wholesale_mark() {
		echo '<p></p>';
	}

	/**
	 *
	 */
	public function section_general_options() {
		echo '<p></p>';
	}

	/**
	 *
	 */
	public function register_fields() {
		add_settings_field( 'option_dollar_rate', apply_filters( $this->plugin_name . 'label_dollar_rate',
			esc_html__( 'Dollar exchange rate', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => 'Enter the dollar exchange rate here.',
				'id'          => 'dollar_rate',
				'value'       => '1',
				'class'       => 'wc_input_price widefat',
			]
		);

		add_settings_field( 'option_customs_duties', apply_filters( $this->plugin_name . 'label_customs_duties',
			esc_html__( 'Customs duties', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => 'Enter the customs duties. (1 is default, 1.10 is 10% increase)',
				'id'          => 'customs_duties',
				'value'       => '1',
			]
		);

		add_settings_field( 'option_shipping_cost', apply_filters( $this->plugin_name . 'label_shipping_cost',
			esc_html__( 'Shipping cost', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => 'Shipping cost in ' . get_woocommerce_currency_symbol() . ' per kg',
				'id'          => 'shipping_cost',
				'value'       => '70',
			]
		);
	}

	/**
	 * Creates a text field
	 *
	 * @param    array $args The arguments for the field.
	 */
	public function field_text( $args ) {
		$defaults['class']       = 'text widefat';
		$defaults['description'] = '';
		$defaults['label']       = '';
		$defaults['name']        = $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['placeholder'] = '';
		$defaults['type']        = 'text';
		$defaults['value']       = '';
		apply_filters( $this->plugin_name . '-field-text-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[ $atts['id'] ] ) ) {
			$atts['value'] = $this->options[ $atts['id'] ];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-text.php' );
	}

	/**
	 * Creates the options page
	 *
	 * @since        1.0.0
	 * @return        void
	 */
	public function page_options() {

		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-page-settings.php' );
	}

	/**
	 * Create the settings page.
	 */
	public function waps_options_page() {
		add_options_page( 'WooCommerce Advanced Price Setter Settings', 'WAPS Settings', 'manage_options',
			$this->plugin_name, [ $this, 'page_options' ]
		);
	}
}
