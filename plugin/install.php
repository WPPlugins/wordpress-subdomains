<?php

//--- Install/Upgrade Plugin
function wps_install() {
	global $wpdb;
	
	// Grab the Current version, if it's really old it might be in 'csd_version'
	$current_version = (get_option('wps_version')?get_option('wps_version'):get_option('csd_version'));
	
	if ($current_version != WPS_VERSION) {
		//--- Grabs existing tables
		// we'll use this to see if this is a brand new install or upgrade
		$tables = $wpdb->get_col('SHOW TABLES;');
		
		$table_name = $wpdb->prefix . "category_subdomains";
		
		//--- Create the table for the plugin
		$sql = "CREATE TABLE " . $table_name . " (
	           cat_ID bigint(20) NOT NULL,
	           is_subdomain tinyint(1) NOT NULL DEFAULT 0,
	           not_subdomain tinyint(1) NOT NULL DEFAULT 0,
	           cat_theme TEXT NOT NULL,
	           filter_pages tinyint(1) NOT NULL DEFAULT 0,
	           cat_link_title varchar(255) NULL,
	           UNIQUE KEY id (cat_ID)
	           );";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		//--- Version checking
		// If we're upgrading then run SQL changes
		if (in_array($table_name, $tables)) {
			wps_upgrade($current_version);
		}
		// Set the new version number
		update_option('wps_version', WPS_VERSION);	
	}
	
}

//--- Run upgrades from given version up to the latest
function wps_upgrade($current_version) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "category_subdomains";
	
	//--- Run upgrades based on current version
	switch ($current_version) {
		case '' :
			$wpdb->query("UPDATE ".$table_name." SET is_subdomain = 1 WHERE not_subdomain = 0");
			$wpdb->query("UPDATE ".$table_name." SET not_subdomain = 0");
			// Change options from old SR ones to CSD ones.
			update_option('csd_sub_pages', (get_option('sr_sub_pages')?get_option('sr_sub_pages'):''));
			update_option('csd_themes_on', (get_option('sr_themes_on')?get_option('sr_themes_on'):''));
			update_option('csd_catarchives_on', (get_option('sr_catarchives_on')?get_option('sr_catarchives_on'):''));
			update_option('csd_pagefilter_on', (get_option('sr_pagefilter_on')?get_option('sr_pagefilter_on'):''));
		case '0.5.0' :
			// Change options to new names and settings
			update_option('wps_sub_pages', (get_option('csd_sub_pages')?WPS_CHK_ON:''));
			update_option('wps_themes', (get_option('csd_themes_on')?WPS_CHK_ON:''));
			update_option('wps_catarchives', (get_option('csd_catarchives_on')?WPS_CHK_ON:''));
			update_option('wps_pagefilter', (get_option('csd_pagefilter_on')?WPS_CHK_ON:''));
			update_option('wps_subdomainall', (get_option('csd_subdomainall_on')?WPS_CHK_ON:''));
			// Delete Old CSD options, leave the SR options so they can go back to old plugin
			delete_option('csd_themes_on');
			delete_option('csd_catarchives_on');
			delete_option('csd_pagefilter_on');
			delete_option('csd_subdomainall_on');
			// Change page meta keys to new ones
			$wpdb->query("UPDATE ".$wpdb->postmeta." SET meta_key = 'wps_page_theme' WHERE meta_key = 'csd_page_theme'");
			$wpdb->query("UPDATE ".$wpdb->postmeta." SET meta_key = 'wps_page_subdomain' WHERE meta_key = 'csd_page_subdomain'");
			$wpdb->query("UPDATE ".$wpdb->postmeta." SET meta_key = 'wps_tie_to_category' WHERE meta_key = 'csd_tie_to_category'");
		case '0.5.1' :
			// Change Widgets Names to the New Names
			$widgets = wp_get_sidebars_widgets();
			foreach (array_keys($widgets) as $sidebar) {
				if ($key = array_search('csd-site-list', $widgets[$sidebar])) {
					$widgets[$sidebar][$key] = 'wps-sitelist';
				}
				if ($key = array_search('csd-categories', $widgets[$sidebar])) {
					$widgets[$sidebar][$key] = 'wps-categories';
				}
			}
			wp_set_sidebars_widgets($widgets);
			break;
	}
	
}

?>