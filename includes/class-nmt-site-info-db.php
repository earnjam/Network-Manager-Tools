<?php

/**
 * Class for handling the database ineractions
 *
 * This class defines all code necessary to handle interactions with the plugin specific tables
 *
 * @since      1.0.0
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 * @author     William Earnhardt <earnjam@gmail.com>
 */
class NMT_DB {

	private $db;

	private $info_table;

	private $prefix;

	private $installed_themes;

	/**
	 *
	 */
	public function __construct() {

		// Map $wpdb object
		global $wpdb;
		$this->db = $wpdb;
		$this->prefix = $wpdb->base_prefix;

		// Store the name of our Site Information table for later use
		$this->info_table = $this->prefix . 'nmt_site_info';

	}

	/**
	 * Adds data for an individual site to the Site Information table
	 * 
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function add_info( $site_id, $theme_name, $active_plugins ) {

		$data = array(
			'blog_id' => $site_id,
			'active_theme' => $theme_name,
			'active_plugins' => $active_plugins
			);

		$this->db->insert( $this->info_table, $data );

	}

	/**
	 * Updates the active theme value for a specific site.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 *
	 * @param $theme_name
	 */
	public function update_theme( $theme_name, $site_id = 0 ) {

		if ( 0 == $site_id ) {
			$site_id = $this->db->blogid;
		}

		$data = array(
			'active_theme' => $theme_name
			);
		
		$this->update_data( $site_id, $data );

	}

	/**
	 * Updates the active plugins list for a specific site.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function update_plugins( $active_plugins, $site_id = 0 ) {

		if ( 0 == $site_id ) {
			$site_id = $this->db->blogid;
		}

		$data = array(
			'active_plugins' => maybe_serialize($active_plugins)
			);

		$this->update_data( $site_id, $data );

	}

	/**
	 * Generic function for updating data in the site information table
	 *
	 * @since    1.0.0
	 */
	public function update_data( $site_id, $data ) {

		$where = array(
			'blog_id' => $site_id
			);

		$result = $this->db->update( $this->info_table, $data, $where );

	}

	/**
	 * Function that runs on deactivated_plugin hook
	 *
	 * @since 1.0.0
	 *
	 * @param $plugin string Dir/filename of the plugin that was deactivated
	 */
	public function plugin_deactivated( $plugin ) {

		$current = get_option( 'active_plugins', $this->db->blogid );
		$key = array_search( $plugin, $current );
		if ( false !== $key ) {
			unset( $current[ $key ] );
		}

		$this->update_plugins( $current, $this->db->blogid );

	}

	/**
	 * Function that runs on activated_plugin hook
	 *
	 * @since 1.0.0
	 *
	 * @param $plugin string Dir/filename of the plugin that was deactivated
	 */
	public function plugin_activated( $plugin ) {

		$current = get_option( 'active_plugins', $this->db->blogid );

		$this->update_plugins( $current, $this->db->blogid );

	}

	/**
	 * Checks to see if the site information table has had data populated
	 *
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function has_site_data() {

		$data = $this->db->get_results( "SELECT * FROM $this->info_table WHERE blog_id = 1" );

		if ( $data ) {
			return true;
		}

		return false;

	}

	/**
	 * Builds out the data for the site information table
	 *
	 * @since   1.0.0
	 */
	public function build_site_info() {

		$n = ( isset( $_POST['n'] ) ) ? intval( $_POST['n'] ) : 0;
		$total = ( isset( $_POST['total'] ) ) ? intval( $_POST['total'] ) : $this->get_last_blog_id();

		$blogs_table = $this->db->blogs;

		$sql = $this->db->prepare( "SELECT * FROM $blogs_table WHERE site_id = %d ORDER BY registered ASC LIMIT %d, 100", $this->db->siteid, $n );

		$blogs = $this->db->get_results( $sql , ARRAY_A );

		if ( ! empty( $blogs ) ) {
			foreach ( $blogs as $blog ) {
				$options = $this->get_options_data( $blog['blog_id'] );
				$this->add_info( $blog['blog_id'], $options[1]['option_value'], $options[0]['option_value'] );
				$n++;
			}
		} else {
			$n = $total;
		}

		$response = array(
			'n' => $n,
			'total' => $total
		);

		wp_send_json( $response );

	}

	/**
	 * Builds the information index
	 *
	 * @since   1.0.0
	 */
	public function build_index() {

		// Get a list of all installed themes
		$this->installed_themes = wp_get_themes();

		// Get a list of all sites.
		$sites = wp_get_sites();

		foreach ( $sites as $site ) {

			$options = $this->get_options_data( $site['blog_id'] );

			// build our data array to pass to add_info
			foreach ( $options as $option ) {

				// Change the array key to active_theme and make it the theme name instead of stylesheet directory
				if ( 'stylesheet' == $option->option_name ) {
					$option->option_name = 'active_theme';
					$theme = wp_get_theme( $option->option_value );
					$option->option_value = $theme->Name;
				}

				$data[ $option->option_name ] = $option->option_value;

			}
			
			$this->add_info( $site['blog_id'], $data['active_theme'], $data['active_plugins'] );

		}

	}

	/**
	 * Get the active theme and plugins for an individual site directly from the source
	 *
	 * @since   1.0.0
	 *
	 * @param   $blog_id
	 *
	 * @return  mixed
	 */
	public function get_options_data( $blog_id ) {

		$table = $this->_get_options_table_name( $blog_id );

		$sql = "SELECT option_name, option_value FROM $table WHERE option_name IN ('stylesheet','active_plugins')";

		$result = $this->db->get_results( $sql, ARRAY_A );

		return $result;


	}

	/**
	 * Get the options table name for an individual site.
	 *
	 * @since   1.0.0
	 *
	 * @param   $blog_id
	 *
	 * @return  string
	 */
	private function _get_options_table_name( $blog_id ) {

		$table_name = $this->prefix . $blog_id . '_options';

		// Before returning, if this is the first site, we need to check to see if this 
		// was an old WPMU install and uses the $prefix_1_options table format instead of $prefix_options
		if ( 1 == $blog_id ) {

			$result = $this->db->get_results( "SELECT option_value FROM $table_name WHERE option_name='siteurl'" );

			if ( ! $result ) {
				$table_name = $this->prefix . 'options';
			}

		}

		return $table_name;

	}

	/**
	 * Returns all the data from the site information table
	 *
	 * @since   1.0.0
	 *
	 * @return  mixed
	 */
	public function get_site_data() {

		$data = $this->db->get_results( "SELECT * FROM " . $this->info_table . " INNER JOIN " . $this->db->blogs ." USING (blog_id)" );

		return $data;

	}

	/**
	 * Gets the blog ID for the last site created on the network
	 *
	 * @since   1.0.0
	 *
	 * @return  integer
	 */
	public function get_last_blog_id() {

		$table = $this->db->blogs;

		$id = $this->db->get_results( "SELECT blog_id FROM $table ORDER BY blog_id DESC LIMIT 1" );

		return $id[0]->blog_id;

	}

}
