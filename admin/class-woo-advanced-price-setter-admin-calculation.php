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
	 * @var false|float $retailPrice
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
	 * Variable to hold log messages.
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
	public function getPrice(): float {
		return $this->price;
	}

	/**
	 * @return false|float
	 */
	public function getNewSalesPrice() {
		return $this->new_sales_price;
	}

	/**
	 * @return false|float
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
	public function getProduct(): wc_product {
		return $this->product;
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
		if ( empty( $this->options['dollar_rate'] ) || ! $this->options['dollar_rate'] > 0 ) {
			$this->waps_log( true, 'Dollar rate calc skipped, missing setting or less then zero.' );

			return;
		}
		$this->price = $this->price * $this->options['dollar_rate'];

		$this->waps_log( $this->dryRun,
			'Convert currency<br/>Current dollar rate: ' . wc_price( $this->options['dollar_rate']
			) . ' per $<br/>New price after dollar rate calc: ' . esc_html( $this->price )
		);
	}

	private function calc_waps_customs_duties() {
		if ( empty( $this->options['customs_duties'] ) || ! $this->options['customs_duties'] > 0 ) {
			$this->waps_log( true, 'Customs duties calc skipped, missing setting or less then zero.' );

			return;
		}
		$this->price = $this->price * $this->options['customs_duties'];

		$this->waps_log( $this->dryRun,
			'Add custom duties<br/>Current customs duties: ' . esc_html( $this->options['customs_duties']
			) . '<br/>Price after customs duties: ' . esc_html( $this->price )
		);
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

		if ( $this->price >= $this->options['whole_mark_1_from'] && $this->price < $this->options['whole_mark_1_to'] ) {
			$mark = $this->options['whole_mark_1_mark'];
		} elseif ( $this->price >= $this->options['whole_mark_2_from'] && $this->price < $this->options['whole_mark_2_to'] ) {
			$mark = $this->options['whole_mark_2_mark'];
		} elseif ( $this->price >= $this->options['whole_mark_3_from'] && $this->price < $this->options['whole_mark_3_to'] ) {
			$mark = $this->options['whole_mark_3_mark'];
		} else {
			echo '<p>Whole mark calc skipped</p>';

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

	private function calc_waps_retail_segments( $price ) {
		if ( $price >= $this->options['retail_mark_1_from'] && $price < $this->options['retail_mark_1_to'] ) {
			$mark = $this->options['retail_mark_1_mark'];
		} elseif ( $price >= $this->options['retail_mark_2_from'] && $price < $this->options['retail_mark_2_to'] ) {
			$mark = $this->options['retail_mark_2_mark'];
		} elseif ( $price >= $this->options['retail_mark_3_from'] && $price < $this->options['retail_mark_3_to'] ) {
			$mark = $this->options['retail_mark_3_mark'];
		} else {
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
}
