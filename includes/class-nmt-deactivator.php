<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 * @author     William Earnhardt <earnjam@gmail.com>
 */
class NMT_Deactivator {

	/**
	 * Deletes the tables installed by the plugin.
	 *
	 * Drops the active themes & plugins table
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}nmt_site_info;" );

	}

}
