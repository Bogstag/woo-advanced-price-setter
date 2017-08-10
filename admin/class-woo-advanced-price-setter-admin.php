<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
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
	 * Variable to hold log messages.
	 * @var null
	 */
	private $log;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var float $new_sales_price
	 */
	private $new_sales_price;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var float|false $retailPrice
	 */
	private $retailPrice;

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
	 * @return array
	 */
	public function validate_options( $options ) {
		$options['dollar_rate'] = $this->format_number( $options['dollar_rate'] );

		return $options;
	}

	/**
	 * Add the dryrun button to simple product page.
	 */
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
				'label'     => esc_html__( 'WAPS product price', $this->plugin_name ) . ' ($)',
				'data_type' => 'price',
			]
		);
		submit_button( esc_html__( 'WAPS Dry Run', $this->plugin_name ), 'button small', 'waps_dryrun', false
		);
		echo '<div class="waps_dryrun_response">&nbsp;</div>';
	}

	/**
	 * Adds WAPS to variation product page.
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 */
	public function waps_variable_add_in_price_and_button( $loop, $variation_data, $variation ) {
		if ( isset( $variation_data['_in_price_dollar'][0] ) ) {
			$value = esc_attr( $variation_data['_in_price_dollar'][0] );
		} else {
			$value = null;
		}
		woocommerce_wp_text_input( [
				'id'        => '_in_price_dollar_' . $variation->ID,
				'label'     => esc_html__( 'WAPS product price', $this->plugin_name ) . ' ($)',
				'data_type' => 'price',
				'value'     => $value,
			]
		);
	}

	/**
	 * Ajax calls this function to display drurun results.
	 */
	public function waps_dryrun() {
		$price      = (float) $_POST['current_in_price_dollar'];
		$product_id = intval( $_POST['post_id'] );
		$this->waps_get_new_product_price( $price, $product_id, $dryRun = true );
		echo $this->log;
		wp_die();
	}

	/**
	 * Calcs the new price.
	 *
	 * @param float   $price      WAPS Product price.
	 * @param int     $product_id Woo Product Id.
	 * @param boolean $dryRun     If true then output more info, for debug and test.
	 *
	 * @return false|double
	 */
	public function waps_get_new_product_price( $price, $product_id, $dryRun = false ) {
		if ( ! $price > 0 ) {
			$this->waps_log( true, 'Price is zero or less, cant do calc' );

			return false;
		}
		$product = wc_get_product( $product_id );

		$price    = wc_format_decimal( $price, false, false );
		$price    = $this->calc_waps_dollar_rate( $price, $dryRun );
		$price    = $this->calc_waps_customs_duties( $price, $dryRun );
		$price    = $this->calc_waps_shipping_cost( $price, $product, $dryRun );
		$price    = $this->calc_waps_all_segments( $price, $dryRun );
		$rawPrice = $price;
		$price    = $this->calc_waps_num_of_dec( $price, $dryRun );
		$this->calc_waps_new_sales_price( $rawPrice, $product, $dryRun );
		$this->retailPrice = $this->calc_waps_retail_segments( $rawPrice, $dryRun );

		return $price;
	}

	/**
	 * Function to echo out the changes to price.
	 *
	 * @param bool        $dryRun
	 * @param string|null $string
	 */
	public function waps_log( $dryRun = false, $string ) {
		if ( $dryRun ) {
			$this->log = $this->log . $string . '<hr/>';
		}
	}

	/**
	 * @param float   $price
	 * @param boolean $dryRun
	 *
	 * @return float $price
	 */
	private function calc_waps_dollar_rate( $price, $dryRun ) {
		if ( empty( $this->options['dollar_rate'] ) || ! $this->options['dollar_rate'] > 0 ) {
			$this->waps_log( true, 'Dollar rate calc skipped, missing setting or less then zero.' );

			return $price;
		}
		$price = $price * $this->options['dollar_rate'];

		$this->waps_log( $dryRun, 'Convert currency<br/>Current dollar rate: ' . wc_price( $this->options['dollar_rate']
			) . ' per $<br/>New price after dollar rate calc: ' . esc_html( $price )
		);

		return $price;
	}

	/**
	 * @param float   $price
	 * @param boolean $dryRun
	 *
	 * @return float $price
	 */
	private function calc_waps_customs_duties( $price, $dryRun ) {
		if ( empty( $this->options['customs_duties'] ) || ! $this->options['customs_duties'] > 0 ) {
			$this->waps_log( true, 'Customs duties calc skipped, missing setting or less then zero.' );

			return $price;
		}
		$price = $price * $this->options['customs_duties'];

		$this->waps_log( $dryRun,
			'Add custom duties<br/>Current customs duties: ' . esc_html( $this->options['customs_duties']
			) . '<br/>Price after customs duties: ' . esc_html( $price )
		);

		return $price;
	}

	/**
	 * @param float      $price
	 * @param wc_product $product
	 * @param boolean    $dryRun
	 *
	 * @return float $price
	 */
	private function calc_waps_shipping_cost( $price, $product, $dryRun ) {
		if ( empty( $this->options['shipping_cost'] ) || ! $this->options['shipping_cost'] > 0 ) {
			$this->waps_log( true, 'Customs duties calc skipped, missing shipping cost' );

			return $price;
		}
		$waps_prod_weight_kg = wc_get_weight( $product->get_weight(), 'kg' );

		if ( empty( $waps_prod_weight_kg ) || ! $waps_prod_weight_kg > 0 ) {
			if ( $product->is_type( 'variation' ) ) {
				$this->waps_log( true, 'Customs duties calc skipped, missing variation product weight' );

				return $price;
			} else {
				$this->waps_log( true, 'Customs duties calc skipped, missing product weight' );

				return $price;
			}
		}
		$price = $price + ( $this->options['shipping_cost'] * $waps_prod_weight_kg );

		$this->waps_log( $dryRun,
			'Add shipping costs<br/>Current shipping costs: ' . wc_price( $this->options['shipping_cost']
			) . ' per kg<br/>Product weight in kg: ' . esc_html( $waps_prod_weight_kg
			) . '<br/>Price after shipping costs: ' . esc_html( $price )
		);

		return $price;
	}

	/**
	 * @param float   $price
	 * @param boolean $dryRun
	 *
	 * @return float $price
	 */
	private function calc_waps_all_segments( $price, $dryRun ) {

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
		$this->waps_log( $dryRun, 'Add wholesale profit<br/>Wholesale mark: ' . esc_html( $mark
			) . '<br/>Price after wholesale mark: ' . esc_html( $price )
		);

		return $price;
	}

	/**
	 * @param float   $price
	 * @param boolean $dryRun
	 *
	 * @return float $price
	 */
	private function calc_waps_num_of_dec( $price, $dryRun ) {
		$price = $this->waps_round( $price );
		$this->waps_log( $dryRun, 'Round price<br/>Price after rounded: ' . wc_price( $price ) );

		return $price;
	}

	/**
	 * @param double $value
	 *
	 * @return float
	 */
	private function waps_round( $value ) {
		$waps_num_of_dec = get_option( 'aps_num_of_dec', absint( get_option( 'woocommerce_price_num_decimals', 2 ) )
		);

		return round( $value, $waps_num_of_dec );
	}

	/**
	 * @param float      $price
	 * @param wc_product $product
	 * @param boolean    $dryRun
	 */
	private function calc_waps_new_sales_price( $price, $product, $dryRun ) {
		$sale_price = $product->get_sale_price();
		$reg_price  = $product->get_regular_price();
		unset( $this->new_sales_price );

		if ( empty( $sale_price ) || ! $sale_price > 0 ) {
			$this->waps_log( true, 'New sales price calc skipped, missing price or less then zero.' );
		} else {
			$precent_sale          = $sale_price / $reg_price;
			$this->new_sales_price = $this->waps_round( $price * $precent_sale );
			$this->waps_log( $dryRun,
				'Set new sales price (if on sale)<br/>Current sale %: ' . esc_html( $precent_sale * 100
				) . '<br/>New sales price: ' . wc_price( $this->new_sales_price )
			);
		}
	}

	/**
	 * @param            $price
	 * @param boolean    $dryRun
	 *
	 * @return   float|false   $retailPrice
	 */
	private function calc_waps_retail_segments( $price, $dryRun ) {
		if ( $price >= $this->options['retail_mark_1_from'] && $price < $this->options['retail_mark_1_to'] ) {
			$mark = $this->options['retail_mark_1_mark'];
		} elseif ( $price >= $this->options['retail_mark_2_from'] && $price < $this->options['retail_mark_2_to'] ) {
			$mark = $this->options['retail_mark_2_mark'];
		} elseif ( $price >= $this->options['retail_mark_3_from'] && $price < $this->options['retail_mark_3_to'] ) {
			$mark = $this->options['retail_mark_3_mark'];
		} else {
			echo '<p>Retail mark calc skipped</p>';

			return false;
		}
		$retailPrice = ( $price * $mark );
		$retailPrice = $this->waps_round( $retailPrice );
		$this->waps_log( $dryRun, 'Set recomended retail price incl tax<br/>Retail mark: ' . esc_html( $mark
			) . '<br/>Price after retail mark and tax: ' . wc_price( $retailPrice )
		);

		return $retailPrice;
	}

	/**
	 * @param $product_id
	 */
	public function waps_woocommerce_save_new_waps_price( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product->is_type( 'variation' ) ) {
			$waps_price = $_POST[ '_in_price_dollar_' . $product_id ];
		} else {
			$waps_price = $_POST['_in_price_dollar'];
		}

		if ( isset( $waps_price ) && $waps_price > 0 ) {
			$this->waps_update_product( $product_id, $waps_price );
		} elseif ( '' === $_POST['_in_price_dollar'] ) {
			delete_post_meta( $product_id, '_in_price_dollar' );
		}
	}

	/**
	 * Actually changes the price.
	 *
	 * @param string $product_id Product Id.
	 * @param float  $price
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
	 * @param integer $product_id
	 * @param float   $waps_price
	 */
	public function waps_update_product( $product_id, $waps_price ) {
		update_post_meta( $product_id, '_in_price_dollar', $waps_price );
		$price = $this->waps_get_new_product_price( $waps_price, $product_id );
		if ( $price ) {
			$this->waps_update_price( $product_id, $price );
			WC_Product_Variable::sync( $product_id, 0 );
			//todo: Save retail price to product attribute. Bara Inkl moms

			if ( $this->retailPrice ) {
				$this->waps_set_retail_price_attribute( $product_id );
			}
			echo $this->log;
			$this->log = null;
		} else {
			print_r( new WP_Error( 'price', 'No WAPS price found' ) );
		}
	}

	/**
	 * @param int $product_id
	 */
	private function waps_set_retail_price_attribute( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( $product->is_type( 'variation' ) ) {
			$productParent = wc_get_product( $product->get_parent_id() );
			$prices        = $productParent->get_variation_prices( true );
			$min_price     = $this->calc_waps_retail_segments( current( $prices['regular_price'] ), false
			);
			$max_price     = $this->calc_waps_retail_segments( end( $prices['regular_price'] ), false );
			$retailPrice   = wc_price( $min_price ) . ' - ' . wc_price( $max_price );
			$this->waps_save_retail_price_attribute( $product->get_parent_id(), $retailPrice );
		} else {
			$this->waps_save_retail_price_attribute( $product_id, wc_price( $this->retailPrice ) );
		}

	}

	/**
	 * @param $product_id
	 * @param $retailPrice
	 */
	private function waps_save_retail_price_attribute( $product_id, $retailPrice ) {
		wp_set_object_terms( $product_id, $retailPrice, $this->options['retail_price_attribute'] );
		$thedata = [
			$this->options['retail_price_attribute'] => [
				'name'         => $this->options['retail_price_attribute'],
				'value'        => $retailPrice,
				'is_visible'   => '1',
				'is_variation' => '0',
				'is_taxonomy'  => '1'
			]
		];
		update_post_meta( $product_id, '_product_attributes', $thedata );
	}
}
