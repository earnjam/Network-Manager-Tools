# Network Manager Tools

A multisite plugin that adds a number of helpful tools for network administrators. Many of these features are built primarily for open-style networks, but can be helpful for closed networks as well.

## Current Features
1. Site Information
	* Table of all sites, their ID, active theme, active plugins, created and last updated date
	* Sortable columns, pagination
	* Incremental search field searches entire table with auto-suggest for installed themes & plugins
	* Site (deactivate, delete, archive, spam, etc.) actions directly from this screen
1. Plugin Manager
	* New "network-enabled" status limits plugin availablity to single-site admins on the network in the same manner as themes
	* Limit plugin availability on a per-site basis through new Plugins tab on site-info.php screen
	* Activate/deactivate plugins on individual sites directly from aforementioned plugins screen on the network dashboard

## Planned Features
1. Admin Message
	* Post messages to the dashboard of individual sites on a network
	* Post to all sites, or subset of sites based on active theme, plugins, etc.
	* Add publish/expiration date for automatic posting/removal of messages
1. Support Admin
	* Some users (especially support staff) need access to all sites, but not network-level settings, this would allow you to set multiple levels of super admins.
1. Site Deprovisioner
	* Automatically deactivate and/or remove unused sites based on last updated date, amount of content, etc.