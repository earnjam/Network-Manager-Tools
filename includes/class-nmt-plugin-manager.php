<?php
/**
 * Plugin Manager Class
 *
 * @since      1.0.0
 *
 * @package    Network_Manager_Tools
 * @subpackage Network_Manager_Tools/admin
 */

class NMT_Plugin_Manager {

	private $network_enabled_plugins;

	/**
	 *
	 */
	public function __construct() {

		// Add page to manage the enabled plugins
		add_action( 'network_admin_menu', array( $this, 'add_pages' ) );

		// Add styles & scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Prep our network plugins screen
		add_action( 'pre_current_active_plugins', array( $this, 'process_plugins' ) );

		// Add our Views filters to the top of the network plugins screen
		add_filter( 'views_plugins-network', array( $this, 'get_views' ) );

		// Add the new plugin action links on the Network Plugins screen
		add_filter( 'network_admin_plugin_action_links', array( $this, 'network_plugin_action_links' ), 10, 4 );
		
		// Add the new plugin action links on the single plugin activation confirmation screen
		add_filter( 'install_plugin_complete_actions', array( $this, 'plugin_installed_options_link' ), 10, 3 );
		
		// Enabled Plugins filter for single site admins
		add_filter( 'all_plugins', array( $this, 'remove_plugins' ) );

	}

	/**
	 *
	 */
	public function add_pages() {

		// Adds our Individual Site Plugin management page - Only accesible via the Edit Site pages tabs
		add_submenu_page( null, 'Edit Site: Plugins', 'Edit Site: Plugins', 'manage_network', 'nmt_site_plugins', array( $this, 'site_plugins_tab' ) );

	}

	/**
	 * @param $hook
	 */
	public function enqueue_styles( $hook ) {

		if ( $hook == 'plugins.php' || $hook == 'sites_page_nmt_site_plugins' ) {
			wp_enqueue_style( 'nmt-plugin-manager', dirname( plugin_dir_url( __FILE__ ) ) . '/css/nmt-plugin-manager.css' );
		}

	}

	/**
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {

		switch ( $hook ) {
			case 'plugins.php':
				wp_enqueue_script( 'nmt-site-plugins-tab', dirname( plugin_dir_url( __FILE__ ) ) . '/js/nmt-plugins-screen.js', array( 'jquery' ) );
			case 'site-info.php':
			case 'site-users.php':
			case 'site-themes.php':
			case 'site-settings.php':
				wp_enqueue_script( 'nmt-site-plugins-tab', dirname( plugin_dir_url( __FILE__ ) ) . '/js/nmt-site-plugins-tab.js', array( 'jquery' ) );
				break;
		}


	}

	/**
	 * @param $actions
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $context
	 *
	 * @return array
	 */
	public function network_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		$new_actions = array();

		if ( ! is_plugin_active( $plugin_file ) && current_user_can( 'manage_network_plugins' ) ) {
			if ( ! $this->is_network_enabled( $plugin_file ) ) {
				$new_actions['enable'] = '<a href="' . wp_nonce_url('plugins.php?action=enable&amp;plugin=' . $plugin_file . '&plugin_status=' . $context, 'enable-plugin_' . $plugin_file ) . '" title="' . esc_attr__('Enable this plugin for all sites in this network', 'network-manager-tools' ) . '" class="edit">' . __('Network Enable', 'network-manager-tools' ) . '</a>';
			} else {
				$new_actions['disable'] = '<a href="' . wp_nonce_url('plugins.php?action=disable&amp;plugin=' . $plugin_file . '&plugin_status=' . $context, 'disable-plugin_' . $plugin_file ) . '" title="' . esc_attr__('Disable this plugin for all sites in this network', 'network-manager-tools' ) . '" class="edit">' . __('Network Disable', 'network-manager-tools' ) . '</a>';
			}
		}

		return array_merge( $new_actions, $actions );

	}
	
	/** 
	 * @param $install_actions
	 * @param $api
	 * @param $plugin_file
	 *
	 * @return array
	 * @author Ben Meredith <ben.meredith@gmail.com>
	 */
	 
	public function plugin_installed_options_link( $install_actions, $api, $plugin_file ) { 

		$new_link = '<a href="' . wp_nonce_url( 'plugins.php?action=enable&amp;plugin=' . $plugin_file . '&plugin_status=all' , 'enable-plugin_' . $plugin_file ) . '" title="' . esc_attr__( 'Enable this plugin for all sites in this network', 'network-manager-tools' ) . '" class="edit">' . __( 'Network Enable', 'network-manager-tools' ) . '</a>';
		array_unshift( $install_actions, $new_link );

		return $install_actions;

	}
  			
	/**
	 * @return array|mixed
	 */
	public function get_network_enabled_plugins() {

		if ( ! isset( $this->network_enabled_plugins ) ) {
			$this->network_enabled_plugins = ( get_site_option( 'nmt_allowedplugins' ) ) ? get_site_option( 'nmt_allowedplugins' ) : array();
		}

		return $this->network_enabled_plugins;

	}

	/**
	 * @param $plugin_file
	 *
	 * @return bool
	 */
	public function is_network_enabled( $plugin_file ) {

		$enabled = $this->get_network_enabled_plugins();

		if ( isset ( $enabled[ $plugin_file ] ) ) {
			return true;
		}
		return false;

	}

	/**
	 * @param array $enabled_plugins
	 */
	public function set_network_enabled_plugins( $enabled_plugins = array() ) {

		update_site_option( 'nmt_allowedplugins', $enabled_plugins );
		$this->network_enabled_plugins = $enabled_plugins;

	}

	public function process_plugins( $plugins ) {

		global $action, $plugins, $plugin, $status, $wp_list_table;

		if ( isset( $_REQUEST['plugin_status'] ) && in_array( $_REQUEST['plugin_status'], array( 'active', 'inactive', 'enabled', 'disabled', 'recently_activated', 'upgrade', 'mustuse', 'dropins', 'search' ) ) )
			$status = $_REQUEST['plugin_status'];

		switch( $action ) {
			case 'enable' :

				if ( ! is_multisite() || ! is_network_admin() || is_network_only_plugin( $plugin ) ) {
					wp_redirect( self_admin_url("plugins.php?plugin_status=$status") );
					exit;
				}

				check_admin_referer('enable-plugin_' . $plugin);

				$enabled = $this->get_network_enabled_plugins();
				if ( ! $this->is_network_enabled( $plugin ) ) {
					$enabled[ $plugin ] = time();
				}
				ksort( $enabled );
				$this->set_network_enabled_plugins( $enabled );

				break;

			case 'disable' :

				if ( ! is_multisite() || ! is_network_admin() || is_network_only_plugin( $plugin ) ) {
					wp_redirect( self_admin_url("plugins.php?plugin_status=$status") );
					exit;
				}

				check_admin_referer('disable-plugin_' . $plugin);

				$enabled = $this->get_network_enabled_plugins();
				if ( $this->is_network_enabled( $plugin ) ) {
					unset ( $enabled[ $plugin ] );
				}
				$this->set_network_enabled_plugins( $enabled );

				break;

			case 'enable-selected' :

				check_admin_referer('bulk-plugins');

				$enable_plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();

				$enabled = $this->get_network_enabled_plugins();

				foreach ( $enable_plugins as $plugin_file ) {
					// Only enable if it is not network activated and not network enabled
					if ( ! is_plugin_active_for_network( $plugin_file ) && ! $this->is_network_enabled( $plugin_file ) ) {
						$enabled[ $plugin_file ] = time();
					}
				}

				ksort($enabled);
				$this->set_network_enabled_plugins( $enabled );

				break;

			case 'disable-selected' :

				check_admin_referer('bulk-plugins');

				$disable_plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();

				$enabled = $this->get_network_enabled_plugins();

				foreach ( $disable_plugins as $plugin_file ) {
					// Only disable if it is is network enabled and not network activated
					if ( ! is_plugin_active_for_network( $plugin_file ) && $this->is_network_enabled( $plugin_file ) ) {
						unset( $enabled[ $plugin_file ] );
					}
				}

				$this->set_network_enabled_plugins( $enabled );

				break;

		}

		$plugins['enabled'] = array();
		$plugins['disabled'] = array();

		foreach ( (array) $plugins['all'] as $plugin_file => $plugin_data ) {

			if ( $this->is_network_enabled( $plugin_file )  && ! is_plugin_active_for_network( $plugin_file ) ) {
				$plugins['enabled'][ $plugin_file ] = $plugin_data;
			} elseif ( ! is_plugin_active_for_network( $plugin_file ) ) {
				$plugins['disabled'][ $plugin_file ] = $plugin_data;
			}

		}

		// Only show the correct plugins if the filter view is set to enabled or disabled
		if ( 'enabled' == $status ) {
			$wp_list_table->items = $plugins['enabled'];
		} elseif ( 'disabled' == $status ) {
			$wp_list_table->items = $plugins['disabled'];
		}

	}


	/**
	 * Extends the network plugins screen's views to add enabled and disabled
	 *
	 * @since   1.0.0
	 * @param   $views
	 *
	 * @return  mixed
	 */
	public function get_views ( $views ) {

		global $totals, $plugins, $status;

		$totals['enabled'] = count( $plugins['enabled'] );
		$totals['disabled'] = count( $plugins['disabled'] );

		// We want to insert the new links into the 3rd and 4th position, but maintain the array keys
		// so the proper class gets applied to the <li>
		foreach ( $views as $key => $link ) {
			if ( 'upgrade' == $key ) {
				$current = ( 'enabled' == $status ) ? ' class=current' : '';
				$new_views['enabled'] = '<a href="plugins.php?plugin_status=enabled"' . $current .'>' . __( 'Enabled', 'network-manager-tools' ) . '<span class="count">(' . $totals['enabled'] . ')</span></a>';
				$current = ( 'disabled' == $status ) ? ' class=current' : '';
				$new_views['disabled'] = '<a href="plugins.php?plugin_status=disabled"' . $current .'>' . __( 'Disabled', 'network-manager-tools' ) . '<span class="count">(' . $totals['disabled'] . ')</span></a>';
			}
			$new_views[ $key ] = $link;
		}

		return $new_views;

	}

	/**
	 *
	 */
	public function site_plugins_tab () {
		global $pagenow;

		$id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

		if ( ! $id )
			wp_die( __( 'Invalid site ID.', 'network-manager-tools' ) );

		$details = get_blog_details( $id );
		if ( ! can_edit_network( $details->site_id ) )
			wp_die( __( 'You do not have permission to access this page.', 'network-manager-tools'  ) );

		switch_to_blog($id);

		$message = false;
		$plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins' );
		$site_allowed_plugins = ( get_option( 'nmt_allowedplugins' ) ) ? get_option ( 'nmt_allowedplugins' ) : array();
		$filter = ( isset ( $_GET['plugin_status'] ) && in_array( $_GET['plugin_status'], array( 'all', 'active', 'enabled' ) ) ) ? $_GET['plugin_status'] : 'all';

		if ( isset( $_GET['action'] ) ) {
			$action_plugin = $_GET['plugin_name'];
			if ( array_key_exists( $action_plugin, $plugins ) ) {
				switch ( $_GET['action'] ) {
					case 'activate_plugin':
						activate_plugin( $action_plugin );
						$message = __( 'Plugin activated', 'network-manager-tools' );
						break;
					case 'deactivate_plugin':
						deactivate_plugins( $action_plugin, false, false );
						$message = __( 'Plugin deactivated', 'network-manager-tools' );
						break;
					case 'enable_plugin':
						$site_allowed_plugins[$action_plugin] = time();
						ksort( $site_allowed_plugins );
						update_option( 'nmt_allowedplugins', $site_allowed_plugins );
						$message = __( 'Plugin enabled', 'network-manager-tools' );
						break;
					case 'disable_plugin':
						unset( $site_allowed_plugins[$action_plugin] );
						update_option( 'nmt_allowedplugins', $site_allowed_plugins );
						$message = __( 'Plugin disabled', 'network-manager-tools' );
						break;
					default:
						$message = __( 'Error: Invalid action.', 'network-manager-tools' );
						break;
				}
			} else {
				$message = __( 'Error: Invalid plugin name.', 'network-manager-tools' );
			}
		}

		$enabled_plugins = $this->get_network_enabled_plugins() + $site_allowed_plugins;

		$site_url_no_http = preg_replace( '#^http(s)?://#', '', get_blogaddress_by_id( $id ) );
		$title_site_url_linked = sprintf( __( 'Edit Site', 'network-manager-tools') . ': <a href="%1$s">%2$s</a>', get_blogaddress_by_id( $id ), $site_url_no_http );

		?>
		<div class="wrap">
		<h2 id="edit-site"><?php echo $title_site_url_linked ?></h2>
		<?php if ( $message ) {
			echo '<div id="message" class="updated below-h2"><p>'.$message.'</p></div>';
		} ?>
		<h3 class="nav-tab-wrapper">
			<?php
			$tabs = array(
				'site-info'     => array( 'label' => __( 'Info', 'network-manager-tools' ),     'url' => 'site-info.php'     ),
				'site-users'    => array( 'label' => __( 'Users', 'network-manager-tools' ),    'url' => 'site-users.php'    ),
				'site-themes'   => array( 'label' => __( 'Themes', 'network-manager-tools' ),   'url' => 'site-themes.php'   ),
				'site-plugins'  => array( 'label' => __( 'Plugins', 'network-manager-tools' ),  'url' => 'sites.php?page=nmt_site_plugins'),
				'site-settings' => array( 'label' => __( 'Settings', 'network-manager-tools' ), 'url' => 'site-settings.php' ),
			);
			foreach ( $tabs as $tab_id => $tab ) {
				$class = ( $tab['url'] == $pagenow || $tab['url'] == $pagenow . '?page=nmt_site_plugins' ) ? ' nav-tab-active' : '';
				$tab['url'] = add_query_arg( array( 'id' => $id ), $tab['url']);
				echo '<a href="' . $tab['url'] .'" class="nav-tab' . $class . '">' . esc_html( $tab['label'] ) . '</a>';
			}
			?>
		</h3>
			<ul class="subsubsub">
				<li class="all"><a href="sites.php?page=nmt_site_plugins&amp;id=<?php echo $id; ?>" <?php if($filter == 'all') echo 'class="current"'; ?>><?php _e( 'All', 'network-manager-tools' ); ?><span class="count">(<?php echo count($plugins); ?>)</span></a> | </li>
				<li class="active"><a href="sites.php?page=nmt_site_plugins&amp;id=<?php echo $id; ?>&amp;plugin_status=active" <?php if($filter == 'active') echo 'class="current"'; ?>><?php _e( 'Active', 'network-manager-tools' ); ?><span class="count">(<?php echo count($active_plugins); ?>)</span></a> | </li>
				<li class="enabled"><a href="sites.php?page=nmt_site_plugins&amp;id=<?php echo $id; ?>&amp;plugin_status=enabled" <?php if($filter == 'enabled') echo 'class="current"'; ?>><?php _e( 'Enabled', 'network-manager-tools' ); ?><span class="count">(<?php echo count($enabled_plugins); ?>)</span></a></li>
			</ul>

			<table class="wp-list-table widefat plugins">
				<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'network-manager-tools' ); ?></label>
						<input id="cb-select-all-1" type="checkbox">
					</th>
					<th scope="col" id="name" class="manage-column column-name" style=""><?php _e( 'Plugin', 'network-manager-tools' ); ?></th>
					<th scope="col" id="description" class="manage-column column-description" style=""><?php _e( 'Description', 'network-manager-tools' ); ?></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'network-manager-tools' ); ?></label>
						<input id="cb-select-all-1" type="checkbox">
					</th>
					<th scope="col" id="name" class="manage-column column-name" style=""><?php _e( 'Plugin', 'network-manager-tools' ); ?></th>
					<th scope="col" id="description" class="manage-column column-description" style=""><?php _e( 'Description', 'network-manager-tools' ); ?></th>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ( $plugins as $plugin => $plugin_data ) {

					$enabled = false;
					$active = false;
					$row_class = 'inactive';

					if ( isset( $enabled_plugins[ $plugin ] ) ) {
						$enabled = true;
						$row_class = 'enabled';
					}
					if ( is_plugin_active( $plugin ) ) {
						$active = true;
						$row_class = 'active';
					}

					if ( $filter == 'enabled' && ! $enabled ) {
						continue;
					}

					if ( $filter == 'active' && ! $active ) {
						continue;
					}

					$network = is_plugin_active_for_network( $plugin );

					$checkbox_id =  "checkbox_" . md5( $plugin_data['Name']);
					$checkbox = "<label class='screen-reader-text' for='" . $checkbox_id . "' >" . sprintf( __( 'Select %s', 'network-manager-tools' ), $plugin_data['Name'] ) . "</label>"
					            . "<input type='checkbox' name='checked[]' value='" . esc_attr( $plugin ) . "' id='" . $checkbox_id . "' />";


					?>
					<tr class="<?php echo $row_class; ?>">
						<th scope="row" class="check-column"><?php echo $checkbox; ?></th>
						<td class='plugin-title'>
							<strong><?php echo $plugin_data['Name']; ?></strong>
							<div class="row-actions visible">
								<?php
								if ( $network ) {
									echo __( 'Network Activated', 'network-manager-tools' );
								} elseif ( $active ) {
									$actions = array( 'action' => 'deactivate_plugin', 'plugin_name' => urlencode( $plugin ) );
									echo '<a href="'.add_query_arg( $actions ).'">' . __('Deactivate', 'network-manager-tools' ) . '</a>';
								} else {
									$actions = array( 'action' => 'activate_plugin', 'plugin_name' => urlencode( $plugin ) );
									echo '<a href="'.add_query_arg( $actions ).'">' . __('Activate', 'network-manager-tools' ) . '</a>';
									if ( $this->is_network_enabled( $plugin ) ) {
										echo ' | ' . __( 'Network Enabled', 'network-manager-tools' );
									} elseif ( isset( $enabled_plugins[$plugin] ) ) {
										$actions = array( 'action' => 'disable_plugin', 'plugin_name' => urlencode( $plugin ) );
										echo ' | <a href="'.add_query_arg( $actions ).'">' . __( 'Disable', 'network-manager-tools' ) . '</a>';
									} else {
										$actions = array( 'action' => 'enable_plugin', 'plugin_name' => urlencode( $plugin ) );
										echo ' | <a href="'.add_query_arg( $actions ).'">' . __( 'Enable', 'network-manager-tools' ) . '</a>';
									}

								} ?>
							</div>
						</td>
						<td class='column-description desc'>
							<div class='plugin-description'><p><?php echo $plugin_data['Description']; ?></p></div>
							<div class='second plugin-version-author-uri'>
								<?php $plugin_meta = array();
								if ( ! empty( $plugin_data['Version'] ) )
									$plugin_meta[] = sprintf( __( 'Version %s', 'network-manager-tools' ), $plugin_data['Version'] );
								if ( ! empty( $plugin_data['Author'] ) ) {
									$author = $plugin_data['Author'];
									if ( ! empty( $plugin_data['AuthorURI'] ) )
										$author = '<a href="' . $plugin_data['AuthorURI'] . '" title="' . esc_attr__( 'Visit author homepage', 'network-manager-tools' ) . '">' . $plugin_data['Author'] . '</a>';
									$plugin_meta[] = sprintf( __( 'By %s', 'network-manager-tools' ), $author );
								}
								if ( ! empty( $plugin_data['PluginURI'] ) )
									$plugin_meta[] = '<a href="' . $plugin_data['PluginURI'] . '" title="' . esc_attr__( 'Visit plugin site', 'network-manager-tools' ) . '">' . __( 'Visit plugin site', 'network-manager-tools' ) . '</a>';
								echo implode( ' | ', $plugin_meta );
								?>
							</div>
						</td>
					</tr>
				<?php } ?>
			</table>

		</div>

		<?php restore_current_blog();

	}



	/**
	 * Removes plugins from the Plugins screen that are network disabled
	 *
	 * @param $all_plugins array
	 *
	 * @return array
	 */
	public function remove_plugins( $all_plugins ) {

		// Super Admins can see all of the plugins
		if ( is_super_admin() ) {
			return $all_plugins;
		}

		$network_allowed = ( get_site_option('nmt_allowedplugins') ) ? get_site_option('nmt_allowedplugins') : array();
		$site_allowed = ( get_option('nmt_allowedplugins') ) ? get_option('nmt_allowedplugins') : array();

		foreach ( $all_plugins as $plugin => $plugin_data) {

			if ( isset ( $network_allowed[ $plugin ] ) || isset ( $site_allowed[ $plugin ] ) ) {
				// Do nothing because the plugin has been enabled on a site or network level
			} else {
				// The plugin is disabled, so unset it from the list of all plugins
				unset( $all_plugins[ $plugin ] );
			}

		}

		return $all_plugins;
	}

}

$nmt_plugin_manager = new NMT_Plugin_Manager();
