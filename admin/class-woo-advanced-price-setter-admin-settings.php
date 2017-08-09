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
		$this->options = get_option( $this->plugin_name . '-options' );
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
			esc_html__( 'Settings', $this->plugin_name )
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
		add_settings_section( $this->plugin_name . '-general-options',
			apply_filters( $this->plugin_name . 'section_general_options',
				esc_html__( 'General options', $this->plugin_name )
			), [ $this, 'section_general_options' ], $this->plugin_name
		);

		add_settings_section( $this->plugin_name . '-calculating-options',
			apply_filters( $this->plugin_name . 'section_calculating_options',
				esc_html__( 'Calculating options', $this->plugin_name )
			), [ $this, 'section_calculating_options' ], $this->plugin_name
		);

		add_settings_section( $this->plugin_name . '-wholesale-mark',
			apply_filters( $this->plugin_name . 'section_wholesale_mark',
				esc_html__( 'Wholesale mark', $this->plugin_name )
			), [ $this, 'section_wholesale_mark' ], $this->plugin_name
		);

		add_settings_section( $this->plugin_name . '-retail-mark',
			apply_filters( $this->plugin_name . 'section_retail_mark', esc_html__( 'Retail mark', $this->plugin_name )
			), [ $this, 'section_retail_mark' ], $this->plugin_name
		);
	}

	/**
	 *
	 */
	public function section_calculating_options() {
		echo '<p>' . __( 'This options affect everything.', $this->plugin_name ) . '</p>';
	}

	/**
	 *
	 */
	public function section_wholesale_mark() {
		echo '<p>' . __( 'This settings adds wholesale profit margin', $this->plugin_name ) . '</p>';
	}

	/**
	 *
	 */
	public function section_retail_mark() {
		echo '<p>' . __( 'This settings adds retailers profit margin. Retail mark is a number that includes of tax and profit margin for the retailer. So if tax is 25% (0.25) and default profit margin of 85% (0.85). Then this markup should be set to 2.1 (1.1 + 1)',
				$this->plugin_name
			) . '</p>';
	}

	/**
	 *
	 */
	public function section_general_options() {
		echo '<p>' . __( 'You need to create a new attribute (Products->Attributes, type: Text) and enter the taxonomy name here . The attribute should be named like Retail price incl tax . The taxonomy name is in the status bar of your browser when hovering on attribute name .',
				$this->plugin_name
			) . '</p>';
	}

	/**
	 *
	 */
	public function register_fields() {
		add_settings_field( 'option_dollar_rate', apply_filters( $this->plugin_name . 'label_dollar_rate',
			esc_html__( 'Dollar exchange rate', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => __( 'Enter the dollar exchange rate here.', $this->plugin_name ),
				'id'          => 'dollar_rate',
				'value'       => $this->options['dollar_rate'],
				'class'       => 'wc_input_price',
			]
		);

		add_settings_field( 'option_customs_duties', apply_filters( $this->plugin_name . 'label_customs_duties',
			esc_html__( 'Customs duties', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => __( 'Enter the customs duties. (1 is default, 1.10 is 10% increase)',
					$this->plugin_name
				),
				'id'          => 'customs_duties',
				'value'       => $this->options['customs_duties'],
				'class'       => 'wc_input_price',
			]
		);

		add_settings_field( 'option_shipping_cost',
			apply_filters( $this->plugin_name . 'label_shipping_cost', esc_html__( 'Shipping cost', $this->plugin_name )
			), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-calculating-options', [
				'description' => sprintf( __( 'Shipping cost in %s per kg', $this->plugin_name
				), get_woocommerce_currency_symbol()
				),
				'id'          => 'shipping_cost',
				'value'       => $this->options['shipping_cost'],
			]
		);

		add_settings_field( 'option_whole_mark_1_from', apply_filters( $this->plugin_name . 'label_whole_mark_1_from',
			esc_html__( 'Segment 1 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'whole_mark_1_from',
				'value'       => $this->options['whole_mark_1_from'],
			]
		);

		add_settings_field( 'option_whole_mark_1_to', apply_filters( $this->plugin_name . 'label_whole_mark_1_to',
			esc_html__( 'Segment 1 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'whole_mark_1_to',
				'value'       => $this->options['whole_mark_1_to'],
			]
		);

		add_settings_field( 'option_whole_mark_1_mark', apply_filters( $this->plugin_name . 'label_whole_mark_1_mark',
			esc_html__( 'Segment 1 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '1.25', '25%'
				),
				'id'          => 'whole_mark_1_mark',
				'value'       => $this->options['whole_mark_1_mark'],
			]
		);

		add_settings_field( 'option_whole_mark_2_from', apply_filters( $this->plugin_name . 'label_whole_mark_2_from',
			esc_html__( 'Segment 2 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'whole_mark_2_from',
				'value'       => $this->options['whole_mark_2_from'],
			]
		);

		add_settings_field( 'option_whole_mark_2_to', apply_filters( $this->plugin_name . 'label_whole_mark_2_to',
			esc_html__( 'Segment 2 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'whole_mark_2_to',
				'value'       => $this->options['whole_mark_2_to'],
			]
		);

		add_settings_field( 'option_whole_mark_2_mark', apply_filters( $this->plugin_name . 'label_whole_mark_2_mark',
			esc_html__( 'Segment 2 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '1.20', '20%'
				),
				'id'          => 'whole_mark_2_mark',
				'value'       => $this->options['whole_mark_2_mark'],
			]
		);

		add_settings_field( 'option_whole_mark_3_from', apply_filters( $this->plugin_name . 'label_whole_mark_3_from',
			esc_html__( 'Segment 3 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'whole_mark_3_from',
				'value'       => $this->options['whole_mark_3_from'],
			]
		);

		add_settings_field( 'option_whole_mark_3_to', apply_filters( $this->plugin_name . 'label_whole_mark_3_to',
			esc_html__( 'Segment 3 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'whole_mark_3_to',
				'value'       => $this->options['whole_mark_3_to'],
			]
		);

		add_settings_field( 'option_whole_mark_3_mark', apply_filters( $this->plugin_name . 'label_whole_mark_3_mark',
			esc_html__( 'Segment 3 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-wholesale-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '1.18', '18%'
				),
				'id'          => 'whole_mark_3_mark',
				'value'       => $this->options['whole_mark_3_mark'],
			]
		);

		add_settings_field( 'option_retail_mark_1_from', apply_filters( $this->plugin_name . 'label_retail_mark_1_from',
			esc_html__( 'Segment 1 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'retail_mark_1_from',
				'value'       => $this->options['retail_mark_1_from'],
			]
		);

		add_settings_field( 'option_retail_mark_1_to', apply_filters( $this->plugin_name . 'label_retail_mark_1_to',
			esc_html__( 'Segment 1 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'retail_mark_1_to',
				'value'       => $this->options['retail_mark_1_to'],
			]
		);

		add_settings_field( 'option_retail_mark_1_mark', apply_filters( $this->plugin_name . 'label_retail_mark_1_mark',
			esc_html__( 'Segment 1 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '2.1', '110%'
				),
				'id'          => 'retail_mark_1_mark',
				'value'       => $this->options['retail_mark_1_mark'],
			]
		);

		add_settings_field( 'option_retail_mark_2_from', apply_filters( $this->plugin_name . 'label_retail_mark_2_from',
			esc_html__( 'Segment 2 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'retail_mark_2_from',
				'value'       => $this->options['retail_mark_2_from'],
			]
		);

		add_settings_field( 'option_retail_mark_2_to', apply_filters( $this->plugin_name . 'label_retail_mark_2_to',
			esc_html__( 'Segment 2 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'retail_mark_2_to',
				'value'       => $this->options['retail_mark_2_to'],
			]
		);

		add_settings_field( 'option_retail_mark_2_mark', apply_filters( $this->plugin_name . 'label_retail_mark_2_mark',
			esc_html__( 'Segment 2 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '2', '100%'
				),
				'id'          => 'retail_mark_2_mark',
				'value'       => $this->options['retail_mark_2_mark'],
			]
		);

		add_settings_field( 'option_retail_mark_3_from', apply_filters( $this->plugin_name . 'label_retail_mark_3_from',
			esc_html__( 'Segment 3 From', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'If price is more or equal to this', $this->plugin_name ),
				'id'          => 'retail_mark_3_from',
				'value'       => $this->options['retail_mark_3_from'],
			]
		);

		add_settings_field( 'option_retail_mark_3_to', apply_filters( $this->plugin_name . 'label_retail_mark_3_to',
			esc_html__( 'Segment 3 To', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => __( 'and if price is less than this', $this->plugin_name ),
				'id'          => 'retail_mark_3_to',
				'value'       => $this->options['retail_mark_3_to'],
			]
		);

		add_settings_field( 'option_retail_mark_3_mark', apply_filters( $this->plugin_name . 'label_retail_mark_3_mark',
			esc_html__( 'Segment 3 margin', $this->plugin_name )
		), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-retail-mark', [
				'description' => sprintf( __( 'then add this mark on price. (%1$s is %2$s increase)', $this->plugin_name
				), '1.9', '90%'
				),
				'id'          => 'retail_mark_3_mark',
				'value'       => $this->options['retail_mark_3_mark'],
			]
		);

		add_settings_field( 'option_retail_price_attribute',
			apply_filters( $this->plugin_name . 'label_retail_price_attribute',
				esc_html__( 'Taxonomy of retail price attribute', $this->plugin_name )
			), [ $this, 'field_text' ], $this->plugin_name, $this->plugin_name . '-general-options', [
				'description' => __( 'The name is starting with pa_ and then slug name.', $this->plugin_name ),
				'id'          => 'retail_price_attribute',
				'value'       => $this->options['retail_price_attribute'],
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

		submit_button( esc_html__( 'WAPS Recalcualate', $this->plugin_name ), 'button small', 'waps_recalc', false
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
