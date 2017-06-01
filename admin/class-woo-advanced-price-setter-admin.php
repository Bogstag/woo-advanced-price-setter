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

	private $new_sales_price;

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
		wp_register_script( 'waps_dryrun', plugin_dir_url( __FILE__ ) . 'js/woo-advanced-price-setter-admin.js',
			[ 'jquery' ], $this->version, true
		);
		wp_enqueue_script( 'waps_dryrun' );
		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}
		wp_localize_script( 'waps_dryrun', 'waps_dryrun_vars', [
				'postid' => $product->get_id(),
			]
		);
		woocommerce_wp_text_input( [
				'id'        => '_in_price_dollar',
				'label'     => esc_html__( 'WAPS product prince', 'woo-advanced-price-setter' ) . ' ($)',
				'data_type' => 'price',
			]
		);
		submit_button( esc_html__( 'WAPS Dry Run', 'woo-advanced-price-setter' ), 'button small', 'waps_dryrun', false
		);
		echo '<div class="waps_dryrun_response">&nbsp;</div>';
	}

	public function waps_variable_add_in_price_and_button($loop, $variation_data, $variation) {
		woocommerce_wp_text_input( [
				'id'        => '_in_price_dollar_' . $variation->ID,
				'label'     => esc_html__( 'WAPS product prince', 'woo-advanced-price-setter' ) . ' ($)',
				'data_type' => 'price',
			]
		);
		//submit_button( esc_html__( 'WAPS Dry Run', 'woo-advanced-price-setter' ), 'button small', 'waps_dryrun', false
		//);
		//echo '<div class="waps_dryrun_response">&nbsp;</div>';
	}

	public function waps_dryrun() {
		$price      = (float) $_POST['current_in_price_dollar'];
		$product_id = intval( $_POST['post_id'] );
		$this->waps_get_new_product_price( $price, $product_id, $dryrun = true );
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
	public function waps_get_new_product_price( $price, $product_id, $dryrun = false ) {
		if ( ! $price > 0 ) {
			if ( $dryrun ) {
				echo 'Price is zero or less, cant do calc';
			}

			return false;
		}
		$price = wc_format_decimal( $price, false, false );
		$price = $this->calc_waps_dollar_rate( $price, $dryrun );
		$price = $this->calc_waps_customs_duties( $price, $dryrun );
		$price = $this->calc_waps_shipping_cost( $price, $product_id, $dryrun );
		$price = $this->calc_waps_all_segments( $price, $dryrun );
		$price = $this->calc_waps_num_of_dec( $price, $dryrun );
		$this->calc_waps_new_sales_price( $price, $product_id, $dryrun );

		return $price;
	}

	private function calc_waps_dollar_rate( $price, $dryrun ) {
		if ( empty( $this->options['dollar_rate'] ) || ! $this->options['dollar_rate'] > 0 ) {
			if ( $dryrun ) {
				echo '<p>Dollar rate calc skipped</p>';
			}

			return $price;
		}

		$price = $price * $this->options['dollar_rate'];

		if ( $dryrun ) {
			echo '<p>Current dollar rate: ' . esc_html( $this->options['dollar_rate']
				) . ' ' . esc_html( get_woocommerce_currency_symbol() ) . ' per $';
			echo '<br/>New price after dollar rate calc: ' . esc_html( $price
				) . ' ' . esc_html( get_woocommerce_currency_symbol() ) . '</p>';
		}

		return $price;
	}

	private function calc_waps_customs_duties( $price, $dryrun ) {
		if ( empty( $this->options['customs_duties'] ) || ! $this->options['customs_duties'] > 0 ) {
			if ( $dryrun ) {
				echo '<p>Customs duties calc skipped</p>';
			}

			return $price;
		}
		$price = $price * $this->options['customs_duties'];

		if ( $dryrun ) {
			echo '<p>Current customs duties: ' . esc_html( $this->options['customs_duties'] );
			echo '<br/>Price after customs duties: ' . esc_html( $price ) . '</p>';
		}

		return $price;
	}

	private function calc_waps_shipping_cost( $price, $product_id, $dryrun ) {
		if ( empty( $this->options['shipping_cost'] ) || ! $this->options['shipping_cost'] > 0 ) {
			if ( $dryrun ) {
				echo '<p>Customs duties calc skipped, missing shipping cost</p>';
			}

			return $price;
		}

		$waps_product_weight_in_kg = wc_get_weight( get_post_meta( $product_id, '_weight', true ), 'kg' );

		if ( empty( $waps_product_weight_in_kg ) || ! $waps_product_weight_in_kg > 0 ) {
			echo '<p>Customs duties calc skipped, missing product weight</p>';

			return $price;
		}

		$price = $price + ( $this->options['shipping_cost'] * $waps_product_weight_in_kg );

		if ( $dryrun ) {
			echo '<p>Current shipping costs: ' . esc_html( $this->options['shipping_cost']
				) . ' ' . esc_html( get_woocommerce_currency_symbol() ) . ' per kg';
			echo '<br/>Product weight in kg: ' . esc_html( $waps_product_weight_in_kg );
			echo '<br/>Price after shipping costs: ' . esc_html( $price ) . '</p>';
		}

		return $price;
	}

	private function calc_waps_all_segments( $price, $dryrun ) {

		if ( $price >= $this->options['whole_mark_1_from'] && $price < $this->options['whole_mark_1_to'] ) {
			$mark = $this->options['whole_mark_1_mark'];
		} elseif ( $price >= $this->options['whole_mark_2_from'] && $price < $this->options['whole_mark_2_to'] ) {
			$mark = $this->options['whole_mark_2_mark'];
		} elseif ( $price >= $this->options['whole_mark_3_from'] && $price < $this->options['whole_mark_3_to'] ) {
			$mark = $this->options['whole_mark_3_mark'];
		} else {
			echo '<p>Whole mark calc skipped</p>';

			return $price;
		}
		$price = $price * $mark;
		if ( $dryrun ) {
			echo '<p>Wholesale mark: ' . esc_html( $mark );
			echo '<br/>Price after wholesale mark: ' . esc_html( $price ) . '</p>';
		}

		return $price;
	}

	private function calc_waps_num_of_dec( $price, $dryrun ) {
		$price = $this->waps_round( $price );
		if ( $dryrun ) {
			echo '<p>Price after rounded: ' . esc_html( $price ) . '</p>';
		}

		return $price;
	}

	private function waps_round( $value ) {
		$waps_num_of_dec = get_option( 'aps_num_of_dec', absint( get_option( 'woocommerce_price_num_decimals', 2 ) )
		);

		return round( $value, $waps_num_of_dec );
	}

	private function calc_waps_new_sales_price( $price, $product_id, $dryrun ) {
		$product    = wc_get_product( $product_id );
		$sale_price = $product->get_sale_price();
		$reg_price  = $product->get_regular_price();
		unset( $this->new_sales_price );

		if ( empty( $sale_price ) || ! $sale_price > 0 ) {
			if ( $dryrun ) {
				echo '<p>New sales price calc skipped</p>';
			}
		}
		$precent_sale          = $sale_price / $reg_price;
		$this->new_sales_price = $this->waps_round( $price * $precent_sale );
		if ( $dryrun ) {
			echo '<p>Current sale %: ' . esc_html( $precent_sale );
			echo '<br/>New sales price: ' . esc_html( $this->new_sales_price ) . '</p>';
		}
	}

	/**
	 * Save / update meta
	 *
	 * @param string $product_id Product Id.
	 */
	public function waps_woocommerce_process_product_meta_simple( $product_id ) {
		$waps_price = $_POST['_in_price_dollar'];
		if ( isset( $waps_price ) && $waps_price > 0 ) {
			$product = wc_get_product( $product_id ); // Handling variable products.
			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_available_variations();
				foreach ( $variations as $variation ) {
					$this->waps_update_product( $variation['variation_id'], $waps_price );
				}
				delete_transient( 'wc_var_prices_' . $product_id );
				WC_Product_Variable::sync( $product_id );
			} else {
				$this->waps_update_product( $product_id, $waps_price );
			}
		} elseif ( '' === $_POST['_in_price_dollar'] ) {
			delete_post_meta( $product_id, '_in_price_dollar' );
		}
	}

	/**
	 * Actually changes the price.
	 *
	 * @param string $product_id Product Id.
	 */
	private function waps_update_price( $product_id, $price ) {
		update_post_meta( $product_id, '_regular_price', $price );
		if ( $this->new_sales_price ) {
			update_post_meta( $product_id, '_sale_price', $this->new_sales_price );
			update_post_meta( $product_id, '_price', $this->new_sales_price );
		} else {
			update_post_meta( $product_id, '_price', $price );
		}
	}

	/**
	 * @param $product_id
	 * @param $waps_price
	 */
	private function waps_update_product( $product_id, $waps_price ) {
		update_post_meta( $product_id, '_in_price_dollar', $waps_price );
		$price = $this->waps_get_new_product_price( $waps_price, $product_id );
		$this->waps_update_price( $product_id, $price );
	}
}
