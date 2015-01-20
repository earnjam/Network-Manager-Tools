<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across the dashboard.
 *
 * @since      1.0.0
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization and dashboard-specific hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 * @author     William Earnhardt <earnjam@gmail.com>
 */
class Network_Manager_Tools {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Site info database object for orchestrating interactions with the site info table
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     object      $nmt_db     Orchestrates interactions with site info table
	 */
	protected $nmt_db;

	/**
	 * Private boolean to be sure our plugin is only instantiated once
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     bool    $instance   Plugin instantiation status
	 */
	private static $instance = false;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, Load the modules and define the locale
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'network-manager-tools';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->load_modules();
		$this->set_locale();

	}


	/**
	 * Instantiates our plugin only if one does not exist
	 *
	 * @return bool | Network_Manager_Tools
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - NMT_i18n. Defines internationalization functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nmt-i18n.php';

	}

	/**
	 * Loads the individual NMT Modules
	 *
	 * @since   1.0.0
	 * @access  private
	 * @todo    Include the various existing modules
	 */
	private function load_modules() {

		/**
		 * The class responsible for the site info table
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nmt-site-info.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nmt-plugin-manager.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new NMT_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

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
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * The reference to the class that orchestrates interaction with the site info table.
	 *
	 * @since     1.0.0
	 * @return    NMT_Loader    Orchestrates interaction with the site info table.
	 */
	public function get_nmt_db() {
		return $this->$nmt_db;
	}

}
