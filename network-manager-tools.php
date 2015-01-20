<?php

/**
 * @since             1.0.0
 * @package           Network_Manager_Tools
 *
 * @wordpress-plugin
 * Plugin Name:       Network Manager Tools
 * Plugin URI:        http://wearnhardt.com/network-manager-tools/
 * Description:       A multisite plugin that adds a number of helpful tools for network administrators. 
 * Version:           1.0
 * Author:            William Earnhardt (earnjam)
 * Author URI:        http://wearnhardt.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       network-manager-tools
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nmt-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nmt-deactivator.php';

/** This action is documented in includes/class-nmt-activator.php */
register_activation_hook( __FILE__, array( 'NMT_Activator', 'activate' ) );

/** This action is documented in includes/class-nmt-deactivator.php */
register_deactivation_hook( __FILE__, array( 'NMT_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization and
 * dashboard-specific hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-nmt.php';


/**
 * Get the instance of our plugin
 * We would typically want to avoid polluting the global namespace, but this allows other plugins
 * to easily interact with our NMT DB object which might help build other tools
 */
$network_manager_tools_plugin = Network_Manager_Tools::get_instance();

