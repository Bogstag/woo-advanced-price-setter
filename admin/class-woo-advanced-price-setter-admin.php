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
	 * @param      array  $options     Options of this plugin.
	 */
	public function __construct( $plugin_name, $version, $options ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->options     = $options;

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

	public function waps_variable_add_in_price_and_button( $loop, $variation_data, $variation ) {
		if ( isset( $variation_data['_in_price_dollar'][0] ) ) {
			$value = esc_attr( $variation_data['_in_price_dollar'][0] );
		} else {
			$value = null;
		}
		woocommerce_wp_text_input( [
				'id'        => '_in_price_dollar_' . $variation->ID,
				'label'     => esc_html__( 'WAPS product prince', 'woo-advanced-price-setter' ) . ' ($)',
				'data_type' => 'price',
				'value'     => $value,
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
			echo '<p>Current dollar rate: ' . wc_price( $this->options['dollar_rate']
				) . ' per $';
			echo '<br/>New price after dollar rate calc: ' . wc_price( $price
				) . '</p>';
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
			$product = wc_get_product( $product_id );
			if ( $product->is_type( 'variation' ) ) {
				if ( $product->get_weight() > 0 ) {
					$waps_product_weight_in_kg = wc_get_weight( $product->get_weight(), 'kg' );
				} else {
					echo '<p>Customs duties calc skipped, missing variation product weight</p>';

					return $price;
				}
			} else {
				echo '<p>Customs duties calc skipped, missing product weight</p>';

				return $price;
			}
		}

		$price = $price + ( $this->options['shipping_cost'] * $waps_product_weight_in_kg );

		if ( $dryrun ) {

			echo '<p>Current shipping costs: ' . wc_price( $this->options['shipping_cost'] ) . ' per kg';
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

	public function waps_woocommerce_save_new_waps_price( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product->is_type( 'variation' ) ) {
			$waps_price = $_POST[ '_in_price_dollar_' . $product_id ];
		} else {
			$waps_price = $_POST['_in_price_dollar'];
		}

		if ( isset( $waps_price ) && $waps_price > 0 ) {
			$this->waps_update_product( $product_id, $waps_price );
			if ( $product->is_type( 'variation' ) ) {
				WC_Product_Variable::sync( $product_id );
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
		delete_transient( 'wc_var_prices_' . $product_id );
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
