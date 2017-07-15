<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/Bogstag/
 * @since             1.0.0
 * @package           Woo_Advanced_Price_Setter
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Advanced Price Setter
 * Plugin URI:        https://github.com/Bogstag/woo-advanced-price-setter
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Krister Bogstag
 * Author URI:        https://github.com/Bogstag/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-advanced-price-setter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-advanced-price-setter-activator.php
 */
function activate_woo_advanced_price_setter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-advanced-price-setter-activator.php';
	Woo_Advanced_Price_Setter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-advanced-price-setter-deactivator.php
 */
function deactivate_woo_advanced_price_setter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-advanced-price-setter-deactivator.php';
	Woo_Advanced_Price_Setter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_advanced_price_setter' );
register_deactivation_hook( __FILE__, 'deactivate_woo_advanced_price_setter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-advanced-price-setter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_advanced_price_setter() {

	$plugin = new Woo_Advanced_Price_Setter();
	$plugin->run();

}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	run_woo_advanced_price_setter();
}

