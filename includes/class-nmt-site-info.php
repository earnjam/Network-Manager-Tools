<?php

/**
 * The Site Info table functionality of the plugin
 *
 * @since      1.0.0
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 */

/**
 * The Site Info Table functionality of the plugin.
 *
 * Handles all the functionality for populating the site info table and displaying that information
 * on a site admin screen.
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/admin
 */

class NMT_Site_Info {

	/**
	 * List of admin pages created by this module
	 *
	 * @var     array   List of admin page slugs
	 */
	protected $admin_pages;

	/**
	 * Object to orchestrate interactions with the site info table
	 *
	 * @var     object   NMT_DB
	 */
	protected $db;

	/**
	 * Initialize the hooks for the Site Info Table functionality
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->register_hooks();

	}

	/**
	 * Pull in any required classes to handle the functionality
	 *
	 * @since   1.0.0
	 * @todo require the NMT_DB class
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating interactions with the site info table
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-nmt-site-info-db.php';

		$this->db = new NMT_DB();

	}

	/**
	 * Registers all the action hooks for the Site Info Table
	 *
	 * @since   1.0.0
	 * @todo register new site hook
	 * @todo register plugin status change hook
	 */
	private function register_hooks() {

		// Register our admin pages
		add_action( 'network_admin_menu', array( $this, 'add_pages' ) );

		// Add our Styles & Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Register the AJAX callback for building the site info table
		add_action( 'wp_ajax_build_site_info', array( $this->db, 'build_site_info' ) );

		// Register our 3 types of update hooks: New site, theme switch, plugin activation/deactivation
		add_action( 'switch_theme', array( $this->db, 'update_theme' ) );
		add_action( 'activated_plugin', array( $this->db, 'plugin_activated' ) );
		add_action( 'deactivated_plugin', array( $this->db, 'plugin_deactivated' ) );
		add_action( 'wpmu_new_blog', array( $this->db, 'add_info'));

	}

	/**
	 * Register all the styles for the Site Info Table functionality
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles( $hook ) {

		if ($hook == 'sites_page_nmt_site_info') {
			wp_enqueue_style( 'nmt-site-info', dirname( plugin_dir_url( __FILE__ ) ) . '/css/nmt-site-info.css' );
		}

	}

	/**
	 * Register all the scripts for the Site Info Table functionality
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if ($hook == 'sites_page_nmt_site_info') {
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'datatables', dirname( plugin_dir_url( __FILE__ ) ) . '/js/jquery.dataTables.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'nmt-site-info', dirname( plugin_dir_url( __FILE__ ) ) . '/js/nmt-site-info.js', array( 'jquery' ) );
			wp_localize_script( 'nmt-site-info', 'wp_addons', $this->get_addons() );
		}

	}


	/**
	 * Returns alphabetically sorted array of installed themes and plugins
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_addons() {

		$plugins = get_plugins();
		$themes = wp_get_themes();

		$terms = array_merge( $plugins, $themes );

		foreach ( $terms as $term ) {
			if ( $term['Name'] != '' ) {
				$addons[] = $term['Name'];
			}
		}

		sort( $addons );

		return $addons;
	}

	/**
	 * Register the Network Admin Pages
	 *
	 * @since	1.0.0
	 * @todo move Admin Message to the admin message class
	 */
	public function add_pages() {

		$this->admin_pages['site_info'] = add_submenu_page( 'sites.php', __('Site Information', 'network-manager-tools' ), __('Site Information', 'network-manager-tools' ), 'manage_network', 'nmt_site_info', array( $this, 'site_information' ) );
		// Future Admin Message functionality
		// $this->admin_pages['admin_message'] = add_menu_page( 'Admin Messages', 'Admin Messages', 'manage_network', 'admin_message', array( $this, 'admin_message' ), 'dashicons-megaphone', 3 );

		// Add action hook to generate the help tab for each admin page
		foreach ( $this->admin_pages as $page ) {
			add_action( 'load-'.$page, array( $this, 'help_tabs' ) );
		}

	}

	/**
	 * Setup the output for the Site Information Page
	 *
	 * @since	1.0.0
	 */
	public function site_information() {

		// Output the Site Info Page
		echo '<div class="wrap"><h2>' . __('Network Site Information', 'network-manager-tools' ) . '</h2>';

		if ( ! $this->db->has_site_data() ) {

			echo '<p>' . __('Thanks for installing Network Manager Tools! This screen will help you easily see which sites on your network are using which theme and plugin.', 'network-manager-tools' ) . '</p>';
			echo '<p>' . __('In order to make displaying the individual site data as efficient as possible, we need to do an initial scan of the database to build the index of active themes and plugins. This may take some time depending on the size of your network.', 'network-manager-tools' ) . '</p>';

			echo '<form action="" method="POST" id="site-info-form">';
			echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . __( 'Build the Index', 'network-manager-tools' ) . '">';
			echo '</form>';
			echo '<div class="progress" style="display: none;"><div class="percent"></div></div>';

		} else {
			$data = $this->db->get_site_data();
			$installed_plugins = get_plugins();
			$installed_themes = wp_get_themes();
			require_once dirname( plugin_dir_path( __FILE__ ) ) . '/partials/nmt-site-info-table-display.php';
		}

		echo '</div>';

	}


	/**
	 * Setup the output for the help tabs for each admin page in this module
	 *
	 * @since	1.0.0
	 */
	public function help_tabs() {

		$screen = get_current_screen();

		switch ( $screen->base ) {

			case 'sites_page_nmt_site_info-network':

				$screen->add_help_tab( array(
					'id'      => 'overview',
					'title'   => __( 'Overview', 'network-manager-tools'  ),
					'content' =>
						'<p>' . __( 'The Site Information Screen is a better way of managing the sites in your Multisite Network.', 'network-manager-tools') . '</p>' .
						'<p>' . __( 'From here you can:','network-manager-tools') .  
						'<ul>' .
						'<li>' . __( 'Search for sites in your network', 'network-manager-tools') . '</li>' .
						'<li>' . __( 'Modify or remove sites from your network', 'network-manager-tools') . '</li>' .
						'<li>' . __( 'Search for a specific theme or plugin to see which sites are using it', 'network-manager-tools') . '</li>' .
						'</ul>' . '</p>'
				) );
				$screen->add_help_tab( array(
					'id'      => 'site-actions',
					'title'   => __( 'Site Actions', 'network-manager-tools' ),
					'content' =>
						'<p>' . __( 'Hovering over each site reveals seven actions you can perform on that site (three for the primary site)', 'network-manager-tools') . ': </p>' .
						'<ul>' .
						'<li>' . __( '<strong>Clicking Dashboard</strong> takes you to the Dashboard screen for that site', 'network-manager-tools') . '</li>' .
						'<li>' . __( '<strong>Clicking Deactivate, Archive, or Spam</strong> performs the specified action with a confirmation screen', 'network-manager-tools') . '</li>' .
						'<li>' . __( '<strong>Clicking Delete</strong> permanently deletes a site from your network, following a confirmation screen', 'network-manager-tools') . '</li>' .
						'<li>' . __( '<strong>Clicking Visit</strong> takes you to the front-end view of the site', 'network-manager-tools') . '</li>' .
						'</ul>'
				) );
				$screen->add_help_tab( array(
					'id'      => 'searching',
					'title'   => __( 'Searching', 'network-manager-tools' ),
					'content' =>
						'<p>' . __( 'By default the table displays 10 sites, but the search box will perform an instant search over the entire network.', 'network-manager-tools') . '</p>' .
						'<p>' . __( 'The search features an autocomplete list pre-filled with all of the installed themes and plugins.', 'network-manager-tools') . '</p>' .
						'<p>' . __( 'The table will be filtered with every letter typed. It finds exact matches based on the entire search field. For instance:', 'network-manager-tools') . '</p>' .
						'<blockquote>' . __('A search for /"Network Manager/" would match /"Network Manager Tools/", however a search for /"Network Tools/" would not find this plugin.', 'network-manager-tools') . '</blockquote>' .
						'<p>' . __('This is so that you can find specific themes, plugins, sites and administrators when you may have a number of similar names across the network.', 'network-manager-tools') . '</p>'

				) );

				$screen->set_help_sidebar(
					'<p><strong>' . __( 'For more information:', 'network-manager-tools' ) . '</strong></p>' .
					'<p><a href="http://codex.wordpress.org/Network_Admin_Sites_Screen" target="_blank">' . __('Documentation on Site Management' , 'network-manager-tools'  ) . '</a></p>' .
					'<p><a href="http://wordpress.org/support/forum/multisite/" target="_blank">' . __( 'Support Forums', 'network-manager-tools'  ) . '</a></p>'
				);

				break;

		}

	}

	/**
	 * Gets array of admin page slugs that are registered
	 *
	 * @return array
	 *
	 * @since   1.0.0
	 */
	public function get_admin_pages() {
		return $this->admin_pages;
	}

	/**
	 * Gets the NMT_DB object to be used in other locations.
	 *
	 * @return object
	 */
	public function get_db() {
		return $this->db;
	}

}

$nmt_site_info = new NMT_Site_Info();