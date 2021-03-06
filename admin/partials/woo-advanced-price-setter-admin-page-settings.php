<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/admin/partials
 */
?>
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<p>This plug in sets the standard woocommerce price after going thru some advanced settings.</p>
	<p>The final price can be influenced by foreign currency, your shipping costs, customs fees.</p>
	<?php settings_errors();

	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general_options';

	?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=woo-advanced-price-setter&tab=general_options"
		   class="nav-tab <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>">General Options</a>
		<a href="?page=woo-advanced-price-setter&tab=list_products_waps"
		   class="nav-tab <?php echo $active_tab == 'list_products_waps' ? 'nav-tab-active' : ''; ?>">List products with
			waps</a>
		<a href="?page=woo-advanced-price-setter&tab=recalc_options"
		   class="nav-tab <?php echo $active_tab == 'recalc_options' ? 'nav-tab-active' : ''; ?>">Recalculate</a>
	</h2>
	<?php
	if ( $active_tab == 'general_options' ) {
		echo '<form method="post" action="options.php">';
		settings_fields( $this->plugin_name . '-options' );
		do_settings_sections( $this->plugin_name );
		submit_button( 'Save Settings' );
		echo '</form>';
	} else if ( $active_tab == 'list_products_waps' ) {
		$this->products_with_waps = new Woo_Advanced_Price_Setter_Product_List();

		?>
		<div class="wrap">
			<h2>Products with WAPS</h2>

			<form id="waps-filter" method="GET">
				<input type="hidden" name="page" value="<?php echo urlencode( $_REQUEST['page'] ) ?>"/>
				<input type="hidden" name="tab" value="list_products_waps"/>
				<?php
				$this->products_with_waps->prepare_items( );
				$this->products_with_waps->search_box( 'search', 'search_id' );
				$this->products_with_waps->display();

				?>
			</form>

			<br class="clear">

		</div>
		<?php
	} else {
		?>
		<h2>Recalculate all products</h2>
		<p>You need to recalculate when changing settings on the general options page</p>
		<?php
		$this->waps_options_recalc_button();
	}
	?>

</div>
