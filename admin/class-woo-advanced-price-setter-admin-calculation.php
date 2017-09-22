<?php
/**
 * The calculation-specific functionality of the plugin.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/admin
 * @author     Krister Bogstag <krister@bogstag.se>
 */

class Woo_Advanced_Price_Setter_Admin_Calculation {

	/**
	 * Options of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var array Array with all options and defaults.
	 */
	private $options;

	/**
	 * Price of product
	 *
	 * @since    1.0.0
	 *
	 * @var float $price price of the product.
	 */
	private $price;

	/**
	 * Product
	 *
	 * @since    1.0.0
	 *
	 * @var wc_product $product
	 */
	private $product;

	/**
	 * DryRun
	 *
	 * @since    1.0.0
	 *
	 * @var boolean $dryRun
	 */
	private $dryRun = false;

	/**
	 * New Sales Price
	 *
	 * @since    1.0.0
	 *
	 * @var float|false $new_sales_price
	 */
	private $new_sales_price = false;

	/**
	 * Retail Price
	 *
	 * @since    1.0.0
	 *
	 * @var string $retailPrice
	 */
	private $retailPrice = false;

	/**
	 * Retail Price
	 *
	 * @since    1.0.0
	 *
	 * @var false|wc_product $productParent
	 */
	private $productParent = false;

	/**
	 * Dollar Price
	 *
	 * @since    1.0.0
	 *
	 * @var float $productParent
	 */
	private $dollarPrice = 0;

	/**
	 * Variable to hold log messages.
	 *
	 * @since    1.0.0
	 *
	 * @var false|string $log
	 */
	private $log;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      array              $options Options of this plugin.
	 * @param      float              $price
	 * @param      wc_product|integer $product or product id.
	 * @param      boolean            $dryRun
	 */
	public function __construct( $options, $price, $product, $dryRun = false ) {

		$this->options = $options;
		$this->price   = $price;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}
		$this->product = $product;
		$this->dryRun  = $dryRun;
		if ( ! $this->price > 0 ) {
			wp_die( 'Price is zero or less, cant do calc' );
		}

		$this->price = wc_format_decimal( $this->price, false, false );

		$this->calc_waps_dollar_rate();
		$this->calc_waps_customs_duties();
		$this->calc_waps_shipping_cost();
		$this->calc_waps_all_segments();

		$this->calc_waps_new_sales_price();
		$this->waps_format_retail_price_attribute();

		// This needs to run last because it removes a lot of decimals.
		$this->calc_waps_num_of_dec();
	}

	/**
	 * @return float
	 */
	public function getPrice() {
		return $this->price;
	}

	/**
	 * @return double|false
	 */
	public function getNewSalesPrice() {
		return $this->new_sales_price;
	}

	/**
	 * @return string
	 */
	public function getRetailPrice() {
		return $this->retailPrice;
	}

	/**
	 * @return false|wc_product
	 */
	public function getProductParent() {
		return $this->productParent;
	}

	/**
	 * @return false|string
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * @return wc_product
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * @return float
	 */
	public function getDollarPrice() {
		return $this->dollarPrice;
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

	private function calc_waps_dollar_rate() {
		$this->dollarPrice = $this->multiply_price_and_another_variable( $this->options['dollar_rate'],
			'Dollar rate calc skipped, missing setting or less then zero.',
			'Convert currency<br/>Current dollar rate: %1$s per $<br/>New price after dollar rate calc: %2$s'
		);
	}

	private function calc_waps_customs_duties() {
		$this->multiply_price_and_another_variable( $this->options['customs_duties'],
			'Customs duties calc skipped, missing setting or less then zero.',
			'Add custom duties<br/>Current customs duties: %1$s<br/>Price after customs duties: %2$s'
		);
	}

	/**
	 * @param float  $option
	 * @param string $errorMessage
	 * @param string $logMessage
	 */
	private function multiply_price_and_another_variable( $option, $errorMessage, $logMessage ) {
		if ( empty( $option ) || ! $option > 0 ) {
			$this->waps_log( true, $errorMessage );

			return;
		}
		$this->price = $this->price * $option;

		$this->waps_log( $this->dryRun, sprintf( $logMessage, $option, $this->price ) );

		return $this->price;
	}

	private function calc_waps_shipping_cost() {
		if ( empty( $this->options['shipping_cost'] ) || ! $this->options['shipping_cost'] > 0 ) {
			$this->waps_log( true, 'Customs duties calc skipped, missing shipping cost' );

			return;
		}
		$waps_prod_weight_kg = wc_get_weight( $this->product->get_weight(), 'kg' );

		if ( empty( $waps_prod_weight_kg ) || ! $waps_prod_weight_kg > 0 ) {
			$this->waps_log( true, 'Customs duties calc skipped, missing product weight' );

			return;
		}
		$this->price = $this->price + ( $this->options['shipping_cost'] * $waps_prod_weight_kg );

		$this->waps_log( $this->dryRun,
			'Add shipping costs<br/>Current shipping costs: ' . wc_price( $this->options['shipping_cost']
			) . ' per kg<br/>Product weight in kg: ' . esc_html( $waps_prod_weight_kg
			) . '<br/>Price after shipping costs: ' . esc_html( $this->price )
		);
	}

	private function calc_waps_all_segments() {
		$mark = $this->waps_get_mark_from_segments( $this->dollarPrice, 'whole_mark' );
		if ( ! $mark ) {
			$this->waps_log( true, 'Whole mark calc skipped' );

			return;
		}
		$this->price = $this->price * $mark;
		$this->waps_log( $this->dryRun, 'Add wholesale profit<br/>Wholesale mark: ' . esc_html( $mark
			) . '<br/>Price after wholesale mark: ' . esc_html( $this->price )
		);
	}

	private function calc_waps_num_of_dec() {
		$this->price = $this->waps_round( $this->price );
		$this->waps_log( $this->dryRun, 'Round price<br/>Price after rounded: ' . wc_price( $this->price ) );
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

	private function calc_waps_new_sales_price() {
		$sale_price = $this->product->get_sale_price();
		$reg_price  = $this->product->get_regular_price();
		unset( $this->new_sales_price );

		if ( empty( $sale_price ) || ! $sale_price > 0 ) {
			$this->waps_log( true, 'No current sale on product, skipping sale calc.' );

			return;
		}
		$precent_sale          = $sale_price / $reg_price;
		$this->new_sales_price = $this->waps_round( $this->price * $precent_sale );
		$this->waps_log( $this->dryRun,
			'Set new sales price (if on sale)<br/>Current sale %: ' . esc_html( $precent_sale * 100
			) . '<br/>New sales price: ' . wc_price( $this->new_sales_price )
		);

	}

	/**
	 * @param $price
	 *
	 * @return false|array
	 */
	private function calc_waps_retail_segments( $price ) {
		$mark = $this->waps_get_mark_from_segments( $this->dollarPrice, 'retail_mark' );
		if ( ! $mark ) {
			$this->waps_log( true, 'Retail mark calc skipped' );

			return false;
		}
		$price = $price * $mark;

		return (array) [ 'Price' => $price, 'Mark' => $mark ];
	}

	private function waps_format_retail_price_attribute() {
		if ( $this->product->is_type( 'variation' ) ) {
			$parentId            = $this->product->get_parent_id();
			$this->productParent = wc_get_product( $parentId );
			$prices              = $this->productParent->get_variation_prices( true );
			$min_price           = $this->calc_waps_retail_segments( current( $prices['regular_price'] ) );
			$max_price           = $this->calc_waps_retail_segments( end( $prices['regular_price'] ) );
			$this->retailPrice   = wc_price( $min_price['Price'] ) . ' - ' . wc_price( $max_price['Price'] );
			$mark                = $min_price['Mark'];
		} else {
			$retailPrice       = $this->calc_waps_retail_segments( $this->price );
			$this->retailPrice = wc_price( $retailPrice['Price'] );
			$mark              = $retailPrice['Mark'];
		}
		$this->waps_log( $this->dryRun, 'Set recommended retail price incl tax<br/>Retail mark: ' . esc_html( $mark
			) . '<br/>Price after retail mark and tax: ' . $this->retailPrice
		);
	}

	/**
	 * @param float  $price
	 * @param string $optionName
	 *
	 * @return false|float
	 */
	private function waps_get_mark_from_segments( $price, $optionName ) {
		if ( $price >= $this->options[ $optionName . '_1_from' ] && $price < $this->options[ $optionName . '_1_to' ] ) {
			return $this->options[ $optionName . '_1_mark' ];
		} elseif ( $price >= $this->options[ $optionName . '_2_from' ] && $price < $this->options[ $optionName . '_2_to' ] ) {
			return $this->options[ $optionName . '_2_mark' ];
		} elseif ( $price >= $this->options[ $optionName . '_3_from' ] && $price < $this->options[ $optionName . '_3_to' ] ) {
			return $this->options[ $optionName . '_3_mark' ];
		} else {
			return false;
		}
	}
}
