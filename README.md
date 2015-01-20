=== Plugin Name ===
Contributors: earnjam
Tags: multisite, network, manage
Requires at least: 3.7
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A multisite plugin that adds a number of helpful tools for network administrators. 

== Description ==

# Current Features
1. ### Site Information
	* Table of all sites, their ID, active theme, active plugins, created and last updated date
	* Sortable columns, pagination
	* Incremental search field searches entire table with auto-suggest for installed themes & plugins
	* Site (deactivate, delete, archive, spam, etc.) actions directly from this screen
1. ### Plugin Manager
	* New "network-enabled" status limits plugin availablity to single-site admins on the network in the same manner as themes
	* Limit plugin availability on a per-site basis through new Plugins tab on site-info.php screen
	* Activate/deactivate plugins on individual sites directly from aforementioned plugins screen on the network dashboard

== Installation ==

1. Upload `network-manager-tools` to the `/wp-content/plugins/` directory
1. Network Activate the plugin through the 'Plugins' menu in the WordPress multisite network admin

== Changelog ==

= 1.0 =
* Initial release