<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/Bogstag/
 * @since      1.0.0
 *
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Advanced_Price_Setter
 * @subpackage Woo_Advanced_Price_Setter/includes
 * @author     Krister Bogstag <krister@bogstag.se>
 */
class Woo_Advanced_Price_Setter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Advanced_Price_Setter_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'woo-advanced-price-setter';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Advanced_Price_Setter_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Advanced_Price_Setter_i18n. Defines internationalization functionality.
	 * - Woo_Advanced_Price_Setter_Admin. Defines all hooks for the admin area.
	 * - Woo_Advanced_Price_Setter_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-advanced-price-setter-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-advanced-price-setter-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-advanced-price-setter-admin.php';

		/**
		 * The class responsible for defining all settings in admin area.
		 */
<<<<<<< HEAD
		require_once plugin_dir_path( dirname( __FILE__ )
		             ) . 'admin/class-woo-advanced-price-setter-admin-settings.php';
=======
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-advanced-price-setter-admin-settings.php';
>>>>>>> 196cbce30ec60a0ead32bcaebf28ccf8fb95dee1

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woo-advanced-price-setter-public.php';

		$this->loader = new Woo_Advanced_Price_Setter_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Advanced_Price_Setter_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Advanced_Price_Setter_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin_settings = new Woo_Advanced_Price_Setter_Admin_Settings($this->get_plugin_name(),
			$this->get_version()
		);

<<<<<<< HEAD
		$this->loader->add_filter( 'plugin_action_links', $plugin_admin_settings, 'link_settings' );
		$this->loader->add_action( 'admin_menu', $plugin_admin_settings, 'waps_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin_settings, 'register_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin_settings, 'register_sections' );
		$this->loader->add_action( 'admin_init', $plugin_admin_settings, 'register_fields' );
		$this->loader->add_action( 'wp_ajax_waps_recalc', $plugin_admin_settings, 'waps_recalc' );

		$plugin_admin = new Woo_Advanced_Price_Setter_Admin( $this->get_plugin_name(), $this->get_version(),
			$plugin_admin_settings->get_options()
		);
=======
		$this->loader->add_filter('plugin_action_links', $plugin_admin_settings, 'link_settings');
		$this->loader->add_action('admin_menu', $plugin_admin_settings, 'waps_options_page');
		$this->loader->add_action('admin_init', $plugin_admin_settings, 'register_settings');
		$this->loader->add_action('admin_init', $plugin_admin_settings, 'register_sections');
		$this->loader->add_action('admin_init', $plugin_admin_settings, 'register_fields');

		$plugin_admin = new Woo_Advanced_Price_Setter_Admin($this->get_plugin_name(), $this->get_version(), $plugin_admin_settings->get_options());
>>>>>>> 196cbce30ec60a0ead32bcaebf28ccf8fb95dee1

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		//$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notices' );
		//$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_notices_init' );
		$this->loader->add_action('woocommerce_product_options_pricing', $plugin_admin, 'waps_add_in_price_and_button'
		);
		$this->loader->add_action('woocommerce_process_product_meta_simple', $plugin_admin,
			'waps_woocommerce_save_new_waps_price'
		);
		$this->loader->add_action('woocommerce_product_after_variable_attributes', $plugin_admin,
			'waps_variable_add_in_price_and_button', 10, 3
		);
		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin,
			'waps_woocommerce_save_new_waps_price', 10, 1
		);

		$this->loader->add_action('wp_ajax_waps_dryrun', $plugin_admin, 'waps_dryrun');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Advanced_Price_Setter_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Advanced_Price_Setter_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
