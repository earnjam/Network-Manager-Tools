<?php
/**
 * Site Info Table View
 *
 * @since   1.0.0
 */
?>
<table class="wp-list-table widefat" id="network-data">
	<thead>
		<tr>
			<th id="blogID" class="manage-column column-id">
				<a>
					<span>ID</span><span class="sorting-indicator"></span>
				</a>
			</th>
			<th id="site" class="manage-column column-site">
				<a>
					<span>Site</span><span class="sorting-indicator"></span>
				</a>
			</th>
			<th id="registered" class="manage-column column-registered">
				<a>
					<span>Registered</span><span class="sorting-indicator"></span>
				</a>
			</th>
			<th id="last-updated" class="manage-column column-last-updated">
				<a>
					<span>Last Updated</span><span class="sorting-indicator"></span>
				</a>
			</th>
			<th id="theme" class="manage-column column-theme">
				<a>
					<span>Theme</span><span class="sorting-indicator"></span>
				</a>
			</th>
			<th id="plugins" class="manage-column column-plugins">Active Plugins</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$n = 0;
		// Loop through sites and print each as a row
		foreach ( $data as $site ) :
			// Setup the row classes and site status
			$class = '';
			$status = '';
			if ( $n % 2 == 0 ) {
				$class = 'alternate';
			}
			if ( $site->deleted == 1 ) {
				$class .= ' site-deleted';
				$status = 'Deleted';
			}
			elseif ( $site->archived == 1 ) {
				$class .= ' site-archived';
				$status .= 'Archived';
			}
			elseif ( $site->spam == 1 ) {
				$class .= ' site-spammed';
				$status = 'Spam';
			}
			// Increment the counter
			$n++;
			?>

			<tr class='<?php echo $class; ?>'>
				<td class="cell-id"><?php echo $site->blog_id; ?></td>
				<td class="cell-site">
					<a href='/wp-admin/network/site-info.php?id=<?php echo $site->blog_id; ?>'><?php echo untrailingslashit( $site->domain . $site->path ); ?></a>
					<?php if ( $status ) {
						echo " - $status";
					}; ?>
					<div class="row-actions">
						<span class="dashboard"><a href="//<?php echo $site->domain . $site->path; ?>wp-admin/">Dashboard</a></span>
						<?php
						$sep = ' | ';
						echo $sep;
						if ($site->blog_id != 1) {
							if ( $site->deleted == 1 )
								echo '<span class="activate"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=activateblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to activate the site %s' ), $site->domain . $site->path ) ) ), 'confirm' ) ) . '">' . __( 'Activate' ) . '</a></span>';
							else
								echo '<span class="activate"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=deactivateblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to deactivate the site %s' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . __( 'Deactivate' ) . '</a></span>';
							echo $sep;
							if ( $site->archived == 1 )
								echo '<span class="archive"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=unarchiveblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to unarchive the site %s.' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . __( 'Unarchive' ) . '</a></span>';
							else
								echo '<span class="archive"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=archiveblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to archive the site %s.' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . _x( 'Archive', 'verb; site' ) . '</a></span>';
							echo $sep;
							if ( $site->spam == 1 )
								echo '<span class="spam"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=unspamblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to unspam the site %s.' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . _x( 'Not Spam', 'site' ) . '</a></span>';
							else
								echo '<span class="spam"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=spamblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to mark the site %s as spam.' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . _x( 'Spam', 'site' ) . '</a></span>';
							echo $sep;
							if ( current_user_can( 'delete_site', $site->blog_id ) )
								echo '<span class="delete"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=deleteblog&amp;id=' . $site->blog_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to delete the site %s.' ), $site->domain . $site->path ) ) ), 'confirm') ) . '">' . __( 'Delete' ) . '</a></span>';
							echo $sep;
						}
						?>
						<span class="visit"><a href="http://<?php echo $site->domain . $site->path ?>">Visit</a></span>
					</div>
				</td>
				<td class="cell-registered"><?php echo date("Y/m/d", strtotime( $site->registered ) ); ?></td>
				<td class="cell-last-updated"><?php echo date("Y/m/d", strtotime( $site->last_updated ) ); ?></td>
				<td class="cell-theme">
					<?php
					$theme = isset( $installed_themes[ $site->active_theme] ) ? $installed_themes[ $site->active_theme ]->Name : $site->active_theme;
					echo $theme;
					?>
				</td>
				<td class="cell-plugins">
					<?php
					$active_plugins = maybe_unserialize( $site->active_plugins );
					$plugins = array();
					if ( ! empty( $active_plugins ) ) : ?>
						<ul>
							<?php foreach ( $active_plugins as $plugin ) {
								if ( isset( $installed_plugins[ $plugin ] ) ) { ?>
								<li><?php echo $installed_plugins[ $plugin ]['Name']; ?></li>
								<?php }
							} ?>
						</ul>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>