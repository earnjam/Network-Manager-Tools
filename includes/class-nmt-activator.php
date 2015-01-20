<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/includes
 * @author     William Earnhardt <earnjam@gmail.com>
 */
class NMT_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Create our table
		global $wpdb;

		$table_name = $wpdb->base_prefix . "nmt_site_info";

		/*
		 * We'll set the default character set and collation for this table.
		 * If we don't do this, some characters could end up being converted 
		 * to just ?'s when saved in our table.
		 */
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

$sql = "CREATE TABLE $table_name (
  blog_id bigint(20) NOT NULL AUTO_INCREMENT,
  active_theme varchar(100) NOT NULL,
  active_plugins longtext NOT NULL,
  PRIMARY KEY  (blog_id),
  KEY active_theme (active_theme)
) $charset_collate;";


		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


	}

}
