<?php

class Woo_Advanced_Price_Setter_Admin_Settings {

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
	public function set_options() {
		$this->options = get_option( $this->plugin_name . '-options' );
		$defaults      = [
			'dollar_rate'        => 1,
			'customs_duties'     => 1,
			'shipping_cost'      => 70,
			'whole_mark_1_from'  => 0,
			'whole_mark_1_to'    => 1200,
			'whole_mark_1_mark'  => 1.25,
			'whole_mark_2_from'  => 1200,
			'whole_mark_2_to'    => 2000,
			'whole_mark_2_mark'  => 1.2,
			'whole_mark_3_from'  => 2000,
			'whole_mark_3_to'    => 99999999999999,
			'whole_mark_3_mark'  => 1.18,
			'retail_mark_1_from' => 0,
			'retail_mark_1_to'   => 300,
			'retail_mark_1_mark' => 2.1,
			'retail_mark_2_from' => 300,
			'retail_mark_2_to'   => 1000,
			'retail_mark_2_mark' => 2,
			'retail_mark_3_from' => 1000,
			'retail_mark_3_to'   => 99999999999999,
			'retail_mark_3_mark' => 1.9
		];
		$this->options = wp_parse_args( $this->options, $defaults );
	}

	public function get_options() {
		return $this->options;
	}

	/**
	 * Adds a link to the plugin settings page
	 *
	 * @since 1.0.0
	 *
	 * @param array $links The current array of links.
	 *
	 * @return string[] The modified array of links
	 */
	public function link_settings( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . $this->plugin_name ) ),
			esc_html__( 'Settings', 'woo-advanced-price-setter' )
		);

		return $links;
	}

	/**
	 * Registers plugin settings
	 *
	 * @since        1.0.0
	 * @return        void
	 */
	public function register_settings() {
		register_setting( $this->plugin_name . '-options', $this->plugin_name . '-options',
			[ $this, 'validate_options_input' ]
		);
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	public function validate_options_input( $input ) {
		return str_replace( ',', '.', $input );
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

		add_settings_section( $this->plugin_name . '-retail-mark',
			apply_filters( $this->plugin_name . 'section_retail_mark',
				esc_html__( 'Retail mark', 'woo-advanced-price-setter' )
			), [ $this, 'section_retail_mark' ], $this->plugin_name
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
	public function section_retail_mark() {
		echo '<p>This settings adds retail mark</p>';
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
				'class'       => 'wc_input_price',
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

		add_settings_field( 'option_retail_mark_1_from', apply_filters( $this->plugin_name . 'label_retail_mark_1_from',
			esc_html__( 'Segment 1 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'retail_mark_1_from',
				'value'       => $this->options['retail_mark_1_from'],
			]
		);

		add_settings_field( 'option_retail_mark_1_to', apply_filters( $this->plugin_name . 'label_retail_mark_1_to',
			esc_html__( 'Segment 1 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'retail_mark_1_to',
				'value'       => $this->options['retail_mark_1_to'],
			]
		);

		add_settings_field( 'option_retail_mark_1_mark', apply_filters( $this->plugin_name . 'label_retail_mark_1_mark',
			esc_html__( 'Segment 1 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'then add this mark on price. (2.1 is 110% increase)',
				'id'          => 'retail_mark_1_mark',
				'value'       => $this->options['retail_mark_1_mark'],
			]
		);

		add_settings_field( 'option_retail_mark_2_from', apply_filters( $this->plugin_name . 'label_retail_mark_2_from',
			esc_html__( 'Segment 2 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'retail_mark_2_from',
				'value'       => $this->options['retail_mark_2_from'],
			]
		);

		add_settings_field( 'option_retail_mark_2_to', apply_filters( $this->plugin_name . 'label_retail_mark_2_to',
			esc_html__( 'Segment 2 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'retail_mark_2_to',
				'value'       => $this->options['retail_mark_2_to'],
			]
		);

		add_settings_field( 'option_retail_mark_2_mark', apply_filters( $this->plugin_name . 'label_retail_mark_2_mark',
			esc_html__( 'Segment 2 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'then add this mark on price. (2 is 200% increase)',
				'id'          => 'retail_mark_2_mark',
				'value'       => $this->options['retail_mark_2_mark'],
			]
		);

		add_settings_field( 'option_retail_mark_3_from', apply_filters( $this->plugin_name . 'label_retail_mark_3_from',
			esc_html__( 'Segment 3 From', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'If price is more or equal to this',
				'id'          => 'retail_mark_3_from',
				'value'       => $this->options['retail_mark_3_from'],
			]
		);

		add_settings_field( 'option_retail_mark_3_to', apply_filters( $this->plugin_name . 'label_retail_mark_3_to',
			esc_html__( 'Segment 3 To', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'and if price is less than this',
				'id'          => 'retail_mark_3_to',
				'value'       => $this->options['retail_mark_3_to'],
			]
		);

		add_settings_field( 'option_retail_mark_3_mark', apply_filters( $this->plugin_name . 'label_retail_mark_3_mark',
			esc_html__( 'Segment 3 Mark', 'woo-advanced-price-setter' )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => 'then add this mark on price. (1.9 is 190% increase)',
				'id'          => 'retail_mark_3_mark',
				'value'       => $this->options['retail_mark_3_mark'],
			]
		);
	}

	/**
	 * Creates a text field
	 *
	 * @param    array $args The arguments for the field.
	 */
	public function field_text( $args ) {
		$defaults                = [];
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

	public function waps_options_recalc_button() {
		wp_register_script( 'waps_recalc', plugin_dir_url( __FILE__ ) . 'js/woo-advanced-price-setter-admin-recalc.js',
			[ 'jquery' ], $this->version, true
		);
		wp_enqueue_script( 'waps_recalc' );

		submit_button( esc_html__( 'WAPS Recalcualate', 'woo-advanced-price-setter' ), 'button small', 'waps_recalc',
			false
		);
		echo '<div class="waps_recalc_response">&nbsp;</div>';
	}

	public function waps_recalc() {
		global $wpdb;
		$ids = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_in_price_dollar' AND meta_value > 0"
		);
		echo '<p>Found ' . count( $ids ) . ' products to update.</p>';
		$plugin_admin = new Woo_Advanced_Price_Setter_Admin( $this->plugin_name, $this->version, $this->options
		);

		array_map( function ( $id ) use ( $plugin_admin ) {
			echo '<p>Product id: ' . $id->post_id . ' updating based on price:' . $id->meta_value . '</p>';
			$plugin_admin->waps_update_product( $id->post_id, $id->meta_value );
			echo '<p>Product id: ' . $id->post_id . ' updated</p>';
			echo '<hr>';
		}, $ids
		);
		wp_die();
	}

}
