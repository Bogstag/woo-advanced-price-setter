<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/product list
 * @author     Krister Bogstag <krister@bogstag.se>
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Woo_Advanced_Price_Setter_Product_List extends WP_List_Table {

	private $search;

	private $orderby;

	private $order;

	private $product;

	public function __construct() {
		global $status, $page;
		//Set parent defaults
		parent::__construct( [
				'singular' => __( 'Product', 'woo-advanced-price-setter' ),     //singular name of the listed records
				'plural'   => __( 'Products', 'woo-advanced-price-setter' ),    //plural name of the listed records
				'ajax'     => false        //does this table support ajax?
			]
		);

		$this->search  = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		$this->orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : false;
		$this->order   = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : false;
		$this->product = ( isset( $_REQUEST['product'] ) ) ? $_REQUEST['product'] : false;

	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case '_regular_price':
			case '_sale_price':
			case '_price':
			case '_in_price_dollar':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	public function column_post_title( $item ) {
		//Return the title contents
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>', $item['post_title'], $item['id']
		);
	}

	public function get_columns() {
		$columns = [
			'post_title'       => __( 'Name', 'woo-advanced-price-setter' ),
			'_regular_price'   => __( 'Regular price', 'woo-advanced-price-setter' ),
			'_sale_price'      => __( 'Sales price', 'woo-advanced-price-setter' ),
			'_price'           => __( 'Price', 'woo-advanced-price-setter' ),
			'_in_price_dollar' => __( 'WAPS Price', 'woo-advanced-price-setter' ),
		];

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = [
			'post_title'       => [ 'post_title', false ],
			'_regular_price'   => [ '_regular_price', false ],
			'_sale_price'      => [ '_sale_price', false ],
			'_price'           => [ '_price', false ],
			'_in_price_dollar' => [ '_in_price_dollar', false ],//true means it's already sorted
		];

		return $sortable_columns;
	}

	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 20;

		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->process_bulk_action();
		$current_page = $this->get_pagenum();
		$data         = $this->get_products_waps( $per_page, $current_page );
		$total_items  = self::record_count();
		//$data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items = $data;
		$this->set_pagination_args( [
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,   //WE have to calculate the total number of pages
			]
		);
	}

	/**
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return array|null|object
	 */
	public function get_products_waps( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT id, post_title, _regular_price, _sale_price, _price, _in_price_dollar FROM {$wpdb->prefix}postmeta P left join {$wpdb->prefix}posts on post_id = ID LEFT JOIN
			(SELECT
			  post_id,
			  SUM(CASE
			      WHEN meta_key = '_regular_price'
			        THEN meta_value
			      ELSE NULL
			      END
			  ) AS '_regular_price',
			  SUM(CASE
			      WHEN meta_key = '_sale_price'
			        THEN meta_value
			      ELSE NULL
			      END
			  ) AS '_sale_price',
			  SUM(CASE
			      WHEN meta_key = '_price'
			        THEN meta_value
			      ELSE NULL
			      END
			  ) AS '_price',
			  SUM(CASE
			      WHEN meta_key = '_in_price_dollar'
			        THEN meta_value
			      ELSE NULL
			      END
			  ) AS '_in_price_dollar'
			FROM {$wpdb->prefix}postmeta
			GROUP BY post_id) M
			  on P.post_id = M.post_id
			WHERE meta_key = '_in_price_dollar' AND meta_value > 0";

		if ( ! empty( $this->search ) ) {
			$sql .= " AND post_name LIKE '%{$this->search}%'";
		}

		if ( ! empty( $this->orderby ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $this->orderby );
			$sql .= ! empty( $this->order ) ? ' ' . esc_sql( $this->order ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";

		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_in_price_dollar' AND meta_value > 0";

		return $wpdb->get_var( $sql );
	}

	public function no_items() {
		_e( 'No products avaliable.', 'woo-advanced-price-setter' );
	}
}
