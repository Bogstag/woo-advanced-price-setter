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

	public function __construct() {
		global $status, $page;
		//Set parent defaults
		parent::__construct( [
				'singular' => __( 'Product', 'woo-advanced-price-setter' ),     //singular name of the listed records
				'plural'   => __( 'Products', 'woo-advanced-price-setter' ),    //plural name of the listed records
				'ajax'     => false        //does this table support ajax?
			]
		);

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
		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>', $item['post_title'], $item['ID']
		);
	}

	public function get_columns() {
		$columns = [
			'cb'               => '<input type="checkbox" />', //Render a checkbox instead of text
			'post_title'       => 'Name',
			'_regular_price'   => 'Regular price',
			'_sale_price'      => 'Sales price',
			'_price'           => 'Price',
			'_in_price_dollar' => 'WAPS Price',
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
		//$this->process_bulk_action();
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

	public static function get_products_waps( $per_page = 5, $page_number = 1 ) {

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

		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		if ( ! empty( $search ) ) {
			$sql .= " AND post_name LIKE '%{$search}%'";
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
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

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions.
	 */
	protected function get_bulk_actions() {
		$actions = [
			'delete' => _x( 'Update WAPS Price', 'List table update action', 'woo-advanced-price-setter'
			)
		];

		return $actions;
	}

	/**
	 * Get value for checkbox column.
	 *
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 *
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'],
			$item['ID']                // The value of the checkbox should be the record's ID.
		);
	}

	/**
	 * Handle bulk actions.
	 *
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {
		// Detect when a bulk action is being triggered.
		if ( 'delete' === $this->current_action() ) {
			wp_die( 'Items updated (or they would be if we had items to delete)!' );
		}
	}

	public function no_items() {
		_e( 'No products avaliable.', 'woo-advanced-price-setter' );
	}
}
