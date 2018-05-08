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
class Woo_Advanced_Price_Setter_Admin
{

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
	 * Options of the plugin
	 *
	 * @since    1.0.0
	 *
	 * @var array Array with all options and defaults.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version     The version of this plugin.
	 * @param      array  $options     Options of this plugin.
	 */
	public function __construct($plugin_name, $version, $options)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = $options;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url(__FILE__).'css/woo-advanced-price-setter-admin.css',
			[],
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

	}

	/**
	 * Format numbers to match woo specs.
	 *
	 * @param float $number Run numbers thru woo validator.
	 *
	 * @return string
	 */
	public function format_number($number)
	{
		return wc_format_decimal($number, false, false);
	}

	/**
	 * Some simple validation.
	 *
	 * @param array $options Get options and validate them.
	 *
	 * @return array
	 */
	public function validate_options($options)
	{
		$options['dollar_rate'] = $this->format_number($options['dollar_rate']);

		return $options;
	}

	/**
	 * Add the dryrun button to simple product page.
	 */
	public function waps_add_in_price_and_button()
	{
		wp_register_script(
			'waps_dryrun',
			plugin_dir_url(__FILE__).'js/woo-advanced-price-setter-admin.js',
			['jquery'],
			$this->version,
			true
		);
		wp_enqueue_script('waps_dryrun');
		global $product;
		if (! is_object($product)) {
			$product = wc_get_product(get_the_ID());
		}
		wp_localize_script(
			'waps_dryrun',
			'waps_dryrun_vars',
			[
				'postid' => $product->get_id(),
			]
		);
		woocommerce_wp_text_input(
			[
				'id'        => '_in_price_dollar',
				'label'     => esc_html__('WAPS product price', $this->plugin_name).' ($)',
				'data_type' => 'price',
			]
		);
		submit_button(
			esc_html__('WAPS Dry Run', $this->plugin_name),
			'button small',
			'waps_dryrun',
			false
		);
		echo '<div class="waps_dryrun_response">&nbsp;</div>';
	}

	/**
	 * Adds WAPS price input field to variation product page.
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 */
	public function waps_variable_add_in_price_and_button($loop, $variation_data, $variation)
	{
		woocommerce_wp_text_input(
			[
				'id'        => '_in_price_dollar_'.$variation->ID,
				'label'     => esc_html__('WAPS product price', $this->plugin_name).' ($)',
				'data_type' => 'price',
				'value'     => esc_attr($variation_data['_in_price_dollar'][0]),
			]
		);
	}

	/**
	 * Ajax calls this function to display drurun results.
	 */
	public function waps_dryrun()
	{
		$price = $_POST['current_in_price_dollar'];
		$product_id = intval($_POST['post_id']);
		$calc = new Woo_Advanced_Price_Setter_Admin_Calculation(
			$this->options, $price, $product_id, true
		);
		echo (string)$calc->getLog();
		wp_die();
	}

	/**
	 * @param $product_id
	 */
	public function waps_woocommerce_save_new_waps_price($product_id)
	{
		$product = wc_get_product($product_id);
		if ($product->is_type('variation')) {
			$waps_price = $_POST['_in_price_dollar_'.$product_id];
		} else {
			$waps_price = $_POST['_in_price_dollar'];
		}
		$waps_price = wc_format_decimal($waps_price, false, false);
		if (isset($waps_price) && $waps_price > 0) {
			$this->waps_update_product($product_id, $waps_price);
		} elseif ('' === $_POST['_in_price_dollar']) {
			delete_post_meta($product_id, '_in_price_dollar');
		}
	}

	/**
	 * @param integer $product_id
	 * @param float   $waps_price
	 */
	public function waps_update_product($product_id, $waps_price)
	{
		update_post_meta($product_id, '_in_price_dollar', $waps_price);
		$calc = new Woo_Advanced_Price_Setter_Admin_Calculation($this->options, $waps_price, $product_id);

		if (! $calc->getPrice()) {
			print_r(new WP_Error('price', 'No WAPS price found'));
			wp_die();
		}
		$this->waps_update_price($calc->getProduct()->get_id(), $calc->getPrice(), $calc->getNewSalesPrice());
		WC_Product_Variable::sync($product_id, 0);

		if ($calc->getRetailPrice()) {
			$this->waps_save_retail_price_attribute(
				$calc->getProduct()->get_id(),
				$calc->getRetailPrice(),
				$calc->getProductParent() ? $calc->getProductParent()->get_id() : false
			);

		}
		echo (string)$calc->getLog();

	}

	/**
	 * Actually changes the price.
	 *
	 * @param integer     $product_id
	 * @param float       $price
	 * @param false|float $new_sales_price
	 */
	private function waps_update_price($product_id, $price, $new_sales_price)
	{
		update_post_meta($product_id, '_regular_price', $price);
		if ($new_sales_price) {
			update_post_meta($product_id, '_sale_price', $new_sales_price);
			update_post_meta($product_id, '_price', $new_sales_price);
		} else {
			update_post_meta($product_id, '_price', $price);
		}
		delete_transient('wc_var_prices_'.$product_id);
	}

	/**
	 * @param      integer       $product_id
	 * @param      float         $retailPrice
	 * @param      integer|false $parentProduct_id
	 */
	private function waps_save_retail_price_attribute($product_id, $retailPrice, $parentProduct_id)
	{
		if ($parentProduct_id !== false) {
			$product_id = $parentProduct_id;
		}
		wp_set_object_terms($product_id, $retailPrice, $this->options['retail_price_attribute']);

		$attributes = get_post_meta($product_id, '_product_attributes');
		$attributes = $attributes[0];
		if (! array_key_exists($this->options['retail_price_attribute'], $attributes)) {
			$attributes[sanitize_title($this->options['retail_price_attribute'])] = [
				'name'         => $this->options['retail_price_attribute'],
				'value'        => $retailPrice,
				'is_visible'   => '1',
				'is_variation' => '0',
				'is_taxonomy'  => '1'
			];
			update_post_meta($product_id, '_product_attributes', $attributes);

		}
	}

}
