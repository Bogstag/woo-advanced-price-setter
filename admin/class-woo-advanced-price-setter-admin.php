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
	 * Options of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var array Array with all options and defaults.
	 */
	public $options;

	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options                 = get_option( $this->plugin_name . '-options' );
		$defaults['dollar_rate']       = 1;
		$defaults['customs_duties']    = 1;
		$defaults['shipping_cost']     = 70;
		$defaults['whole_mark_1_from'] = 0;
		$defaults['whole_mark_1_to']   = 1200;
		$defaults['whole_mark_1_mark'] = 1.25;
		$defaults['whole_mark_2_from'] = 1200;
		$defaults['whole_mark_2_to']   = 2000;
		$defaults['whole_mark_2_mark'] = 1.2;
		$defaults['whole_mark_3_from'] = 2000;
		$defaults['whole_mark_3_to']   = 99999999999999;
		$defaults['whole_mark_3_mark'] = 1.18;
		$this->options                 = wp_parse_args( $this->options, $defaults );
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
		wp_register_script( 'waps_dryrun', plugin_dir_url( __FILE__ ) . 'js/woo-advanced-price-setter-admin.js',
			[ 'jquery' ], $this->version, true
		);
		wp_enqueue_script( 'waps_dryrun' );
		global $post;
		wp_localize_script( 'waps_dryrun', 'waps_dryrun_vars', [
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
		echo '<p>This settings adds wholesale mark</p>';
	}

	/**
	 *
	 */
	public function section_general_options() {
		echo '<p>This no not do anything</p>';
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
				'value'       => $this->options['dollar_rate'],
				'class'       => 'wc_input_price',
			]
		);

		add_settings_field( 'option_customs_duties', apply_filters( $this->plugin_name . 'label_customs_duties',
			esc_html__( 'Customs duties', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => 'Enter the customs duties. (1 is default, 1.10 is 10% increase)',
				'id'          => 'customs_duties',
				'value'       => $this->options['customs_duties'],
			]
		);

		add_settings_field( 'option_shipping_cost', apply_filters( $this->plugin_name . 'label_shipping_cost',
			esc_html__( 'Shipping cost', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => 'Shipping cost in ' . get_woocommerce_currency_symbol() . ' per kg',
				'id'          => 'shipping_cost',
				'value'       => $this->options['shipping_cost'],
			]
		);

		add_settings_field( 'option_whole_mark_1_from', apply_filters( $this->plugin_name . 'label_whole_mark_1_from',
			esc_html__( 'Segment 1 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'whole_mark_1_from',
				'value'       => $this->options['whole_mark_1_from'],
			]
		);

		add_settings_field( 'option_whole_mark_1_to', apply_filters( $this->plugin_name . 'label_whole_mark_1_to',
			esc_html__( 'Segment 1 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'whole_mark_1_to',
				'value'       => $this->options['whole_mark_1_to'],
			]
		);

		add_settings_field( 'option_whole_mark_1_mark', apply_filters( $this->plugin_name . 'label_whole_mark_1_mark',
			esc_html__( 'Segment 1 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'then add this mark on price. (1.25 is 25% increase)',
				'id'          => 'whole_mark_1_mark',
				'value'       => $this->options['whole_mark_1_mark'],
			]
		);

		add_settings_field( 'option_whole_mark_2_from', apply_filters( $this->plugin_name . 'label_whole_mark_2_from',
			esc_html__( 'Segment 2 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'whole_mark_2_from',
				'value'       => $this->options['whole_mark_2_from'],
			]
		);

		add_settings_field( 'option_whole_mark_2_to', apply_filters( $this->plugin_name . 'label_whole_mark_2_to',
			esc_html__( 'Segment 2 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'whole_mark_2_to',
				'value'       => $this->options['whole_mark_2_to'],
			]
		);

		add_settings_field( 'option_whole_mark_2_mark', apply_filters( $this->plugin_name . 'label_whole_mark_2_mark',
			esc_html__( 'Segment 2 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'then add this mark on price. (1.20 is 20% increase)',
				'id'          => 'whole_mark_2_mark',
				'value'       => $this->options['whole_mark_2_mark'],
			]
		);

		add_settings_field( 'option_whole_mark_3_from', apply_filters( $this->plugin_name . 'label_whole_mark_3_from',
			esc_html__( 'Segment 3 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'whole_mark_3_from',
				'value'       => $this->options['whole_mark_3_from'],
			]
		);

		add_settings_field( 'option_whole_mark_3_to', apply_filters( $this->plugin_name . 'label_whole_mark_3_to',
			esc_html__( 'Segment 3 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'whole_mark_3_to',
				'value'       => $this->options['whole_mark_3_to'],
			]
		);

		add_settings_field( 'option_whole_mark_3_mark', apply_filters( $this->plugin_name . 'label_whole_mark_3_mark',
			esc_html__( 'Segment 3 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => 'then add this mark on price. (1.18 is 18% increase)',
				'id'          => 'whole_mark_3_mark',
				'value'       => $this->options['whole_mark_3_mark'],
			]
		);
	}

	/**
	 * Creates a text field
	 *
	 * @param    array $args The arguments for the field.
	 */
	public function field_text( $args ) {
		$defaults['class']       = 'text';
		$defaults['description'] = '';
		$defaults['label']       = '';
		$defaults['name']        = $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['placeholder'] = '';
		$defaults['type']        = 'text';
		$defaults['value']       = '';
		apply_filters( $this->plugin_name . '_field_text_options_defaults', $defaults );
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

	public function waps_add_in_price_and_button() {
		woocommerce_wp_text_input( [
				'id'    => '_in_price_dollar',
				'class' => 'short wc_input_price',
				'label' => esc_html__( 'WAPS product prince', 'woo-advanced-price-setter' ) . ' ($)',
				'type'  => 'text',
			]
		);
		submit_button( esc_html__( 'WAPS Dry Run', 'woo-advanced-price-setter' ), 'button small', 'waps_dryrun', false
		);
		echo '<div class="waps_dryrun_response">&nbsp;</div>';
	}

	public function waps_dryrun() {
		$price      = $_POST['current_in_price_dollar'];
		$product_id = intval( $_POST['post_id'] );
		$this->get_new_product_price( $price, $product_id, $dryrun = true );
		wp_die();
	}

	/**
	 * Calcs the nre price.
	 *
	 * @param float $price      WAPS Product price.
	 * @param int   $product_id Woo Product Id.
	 * @param bool  $dryrun     If true then output more info, for debug and test.
	 *
	 * @return mixed|string
	 */
	public function get_new_product_price( $price, $product_id, $dryrun = false ) {
		if ( ! $price > 0 ) {
			if ( $dryrun ) {
				echo 'Price is zero or less, cant do calc';
			}

			return false;
		}
		$price = wc_format_decimal( $price, false, false );
		$price = $this->calc_waps_dollar_rate( $price, $dryrun );
		//$price = $this->calc_waps_customs_duties( $price, $dryrun );
		//$price = $this->calc_waps_shipping_cost( $price, $product_id, $dryrun );
		//$price = $this->calc_waps_all_segments( $price, $dryrun );
		//$price = $this->calc_waps_num_of_dec( $price, $dryrun );

		return $price;
	}

	private function calc_waps_dollar_rate( $price, $dryrun ) {
		$dollar_rate = $this->options['dollar_rate'];
		if ( empty( $dollar_rate ) || ! $dollar_rate > 0 ) {
			if ( $dryrun ) {
				echo '<p>Dollar rate calc skipped</p>';
			}

			return $price;
		}

		$price = $price * $dollar_rate;

		if ( $dryrun ) {
			echo '<p>Current dollar rate: ' . $dollar_rate . ' ' . get_woocommerce_currency_symbol() . ' per $';
			echo '<br/>New price after dollar rate calc: ' . $price . ' ' . get_woocommerce_currency_symbol() . '</p>';
		}

		return $price;
	}
}
