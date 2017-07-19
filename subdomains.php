<?php
/*

* Wordpress Info

Plugin Name: WP Subdomains
Plugin URI: http://webdev.casualgenius.com/projects/wordpress-subdomains/
Description: Setup your main categories, pages, and authors as subdomains and give them custom themes. Originally based on <a href="http://www.biggnuts.com/wordpress-subdomains-plugin/">Subster Rejunevation</a>.
Version: 0.6.9
Author: Alex Stansfield
Author URI: http://www.casualgenius.com

* LICENSE

    Copyright 2009  Alex Stansfield  (email : alex@casualgenius.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Include required php files
require_once ('plugin/install.php');
require_once ('plugin/classes.php');
require_once ('plugin/functions.php');
require_once ('plugin/actions.php');
require_once ('plugin/filters.php');
require_once ('plugin/rewrite.php');
require_once ('plugin/admin.php');


// Include the Widgets
require_once ('widgets/sitelist.php');
require_once ('widgets/categories.php');

//--- Global Variables
global $wps_subdomains, $wps_this_subdomain, $wps_showall_pages;

// User Settings
//$csd_redir_wildcards = TRUE;
//$csd_timeofchange = 1099179646;
$wps_page_metakey_theme = 'wps_page_theme';
$wps_page_metakey_subdomain = 'wps_page_subdomain';
$wps_page_metakey_tie = 'wps_tie_to_category';
$wps_page_metakey_showall = 'wps_showall';
$wps_page_on_main_index = 'wps_on_main_index';

// Plugin Stuff
//$csd_category_rules = array();
//$csd_post_rules = array();
$wps_permalink_set = false;
$wps_subdomains = false;
$wps_this_subdomain = false;
$wps_showall_pages = array();

// Defines
define( 'WPS_VERSION', '0.6.9' );
define( 'WPS_TYPE_CAT', 1 );
define( 'WPS_TYPE_PAGE', 2 );
define( 'WPS_TYPE_AUTHOR', 3 );
define( 'WPS_CHK_ON', 'on' );
define( 'WPS_OPT_DOMAIN', 'wps_domain' );
define( 'WPS_OPT_SUBPAGES', 'wps_subpages' );
define( 'WPS_OPT_SUBAUTHORS', 'wps_subauthors' );
define( 'WPS_OPT_THEMES', 'wps_themes' );
define( 'WPS_OPT_ARCFILTER', 'wps_arcfilter' );
define( 'WPS_OPT_TAGFILTER', 'wps_tagfilter' );
define( 'WPS_OPT_PAGEFILTER', 'wps_pagefilter' );
define( 'WPS_OPT_SUBALL', 'wps_subdomainall' );
define( 'WPS_OPT_NOCATBASE', 'wps_nocatbase' );
define( 'WPS_OPT_REDIRECTOLD', 'wps_redirectold' );
define( 'WPS_OPT_DISABLED', 'wps_disabled' );
define( 'WPS_OPT_KEEPPAGESUB', 'wps_keeppagesub' );
define( 'WPS_OPT_SUBISINDEX', 'wps_subisindex' );

class WpsPlugin {
	
	function WpsPlugin() {
		global $wps_subdomains, $wps_this_subdomain, $wps_category_base, $wps_showall_pages, $wps_permalink_set;
		
		// Stuff changed in WP 2.8
		if (function_exists('create_initial_taxonomies')) {
			create_initial_taxonomies();
		}
		
		//--- Create the SubDomains Object
		$wps_subdomains = new WpsSubDomains( );
		
		//--- Grab This Subdomain object (if we're on one)
		$wps_this_subdomain = $wps_subdomains->getThisSubdomain();
		
		//--- Grab all the Pages with the Show On All override
		$wps_showall_pages = wps_showall_pages();
		
		//--- Set the category base global
		if ( get_option( 'category_base' ) ) {
			$wps_category_base = get_option( 'category_base' ) . '/';
		} else {
			$wps_category_base = 'category/';
		}
		
		//--- Check permalinks are setup
		if ( get_option( 'permalink_structure' ) ) {
			$wps_permalink_set = true;
		}
		
		//--- Add Admin Menu Pages
		add_action( 'admin_menu', 'wps_add_options' );
		
		// If the permalink is configured then we can setup everything else
		if ( $wps_permalink_set && (get_option( WPS_OPT_DISABLED ) == '') ) {
			//--- Add the Actions
			$this->addActions();
			
			//--- Add the Filters
			$this->addFilters();
		}
		
		// this action can't be in addActions because the admin interface doesn't work without it.
        add_action( 'admin_init', 'wps_admin_init' );
		
	}
	
	function addActions() {
		add_action( 'init', 'wps_init', 2 );
		
		// Only redirect pages when not in admin section
		if (!is_admin()) {
			add_action( 'wp', 'wps_redirect' );
		}
		
		add_action( 'edit_category', 'wps_edit_category' );
		
		add_action( 'parse_query', 'wps_action_parse_query' );
		
		//add_action( 'do_meta_boxes', 'wps_action_page_meta', 10, 3);
	}
	
	function addFilters() {
		//add_filter ( 'posts_where', 'sd_posts_where' );
		

		add_filter( 'rewrite_rules_array', 'wps_rewrite_rules' );
		add_filter( 'root_rewrite_rules', 'wps_root_rewrite_rules' );
		add_filter( 'post_rewrite_rules', 'wps_post_rewrite_rules' );
		add_filter( 'page_rewrite_rules', 'wps_page_rewrite_rules' );
		add_filter( 'date_rewrite_rules', 'wps_date_rewrite_rules' );
		add_filter( 'tag_rewrite_rules', 'wps_tag_rewrite_rules' );
		add_filter( 'category_rewrite_rules', 'wps_category_rewrite_rules' );
		add_filter( 'author_rewrite_rules', 'wps_author_rewrite_rules' );
		
		add_filter( 'admin_footer', 'wps_admin_footer' );
		
		// Filters for Adjacent Posts
		// FIXME: Check args getting through
		add_filter( 'get_previous_post_join', 'wps_filter_adjacent_join' );
		add_filter( 'get_next_post_join', 'wps_filter_adjacent_join' );
		add_filter( 'get_previous_post_where', 'wps_filter_adjacent_where' );
		add_filter( 'get_next_post_where', 'wps_filter_adjacent_where' );
		
		// Filters for Archives
		add_filter( 'getarchives_where', 'wps_filter_archive_where', 10, 2 );
		add_filter( 'getarchives_join', 'wps_filter_archive_join', 10, 2 );
		
		add_filter( 'pre_option_template', 'wps_change_template' );
		add_filter( 'pre_option_stylesheet', 'wps_change_template' );
		
		//add_filter('wp_login', 'csd_wp_login');
		//add_filter('wp_logout', 'csd_wp_logout');
		
		// Not yet needed
		//add_filter( 'redirect_canonical', 'wps_redirect_canonical', 10, 2 );
		
		add_filter( 'get_pages', 'wps_filter_pages', 10 );
		
		/* URL Filters */
		add_filter( 'bloginfo_url', 'wps_filter_bloginfo_url', 10, 2 );
		add_filter( 'bloginfo', 'wps_filter_bloginfo', 10, 2 );
		add_filter( 'category_link', 'wps_category_link', 10, 2 );
		add_filter( 'post_link', 'wps_post_link', 10, 2 );
		add_filter( 'page_link', 'wps_page_link', 10, 2 );
		add_filter( 'author_link', 'wps_author_link', 10, 2 );
		add_filter( 'tag_link', 'wps_tag_link', 10 );
		add_filter( 'month_link', 'wps_month_link' );
		add_filter( 'get_pagenum_link', 'wps_filter_general_url' );
		add_filter( 'list_cats', 'wps_list_cats', 10, 2 );
	}
}

//--- Register the Activation Hook
register_activation_hook( 'wordpress-subdomains/subdomains.php', 'wps_install' );

//--- Run the Plugin
global $WpsPlugin;
$WpsPlugin = new WpsPlugin( );

?>