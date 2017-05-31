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
?><h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
<p>This plug in sets the standard woocommerce price after going thru some advanced settings.</p><p>The final price can be influenced by foreign currency, your shipping costs, customs fees.</p>
<form method="post" action="options.php"><?php
	settings_fields( $this->plugin_name . '-options' );
	do_settings_sections( $this->plugin_name );
	submit_button( 'Save Settings' );
	?></form>
