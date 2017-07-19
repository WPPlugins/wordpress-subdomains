<?php

//--- Initial setup
function wps_init () {	
	if (!is_admin()) {
		// Stuff changed in WP 2.8
		if (function_exists('set_transient')) {
			set_transient('rewrite_rules', "");
			update_option('rewrite_rules', "");
		} else {
			update_option('rewrite_rules', "");
		}
	}
}


//--- Check if we need to do any page redirection
function wps_redirect () {
	global $wp_query, $wps_this_subdomain, $wps_subdomains;
	
	// Check if Redirecting is turned on
	if (get_option(WPS_OPT_REDIRECTOLD) != "") {
		$redirect = false;
		
		if (!$wps_this_subdomain) {
			// Check if it's a category
			if ($wp_query->is_category) {
				$catID = $wp_query->query_vars['cat'];
				
				if ($subdomain = $wps_subdomains->getCategorySubdomain($catID)) {
					$redirect = $wps_subdomains->cats[$subdomain]->changeCategoryLink($catID, '');
				}
			}
			
			// Check if it's a page
			if ($wp_query->is_page) {
				$pageID = $wp_query->post->ID;
				
				// Check if it's a subdomain page or a tied page
				if ($subdomain = $wps_subdomains->getPageSubdomain($pageID)) {
					$redirect = $wps_subdomains->pages[$subdomain]->changePageLink($pageID, '');
				} else if ($catID = $wps_subdomains->findTiedPage($pageID)) {
					$redirect = $wps_subdomains->cats[$catID]->changeCategoryLink($catID).$wp_query->query['pagename'];
				}
			}
			
		}
		
		// If a redirect is found then do it
		if ($redirect) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".$redirect);
			exit();
		}
	}
}


//--- Save Category settings
function wps_edit_category() {
	if (!isset($_REQUEST['cat_ID']))
		return;
	
	global $wpdb;
	
	$table_name = $wpdb->prefix . "category_subdomains";
	
	$is_subdomain = ('true' == $_REQUEST['csd_include']) ? '1' : '0';
	
	$not_subdomain = ('true' == $_REQUEST['csd_exclude']) ? '1' : '0';
	
	$cat_theme = addslashes($_REQUEST['csd_cat_theme']);
	if ($cat_theme == "(none)") {
		$cat_theme = "";
	}
	
	$link_title = addslashes(trim($_REQUEST['csd_link_title']));
	
	$filter_pages = ('true' == $_REQUEST['csd_filterpages']) ? '1' : '0';
	
	if ($wpdb->get_var("SELECT cat_ID FROM {$table_name} WHERE cat_ID = '{$_REQUEST['cat_ID']}'")) {
		$querystr = "UPDATE {$table_name} SET is_subdomain={$is_subdomain}, not_subdomain={$not_subdomain}, cat_theme='{$cat_theme}', filter_pages={$filter_pages}, cat_link_title='{$link_title}' WHERE cat_ID = '{$_REQUEST['cat_ID']}'"; 
	} else {
		$querystr = "INSERT INTO {$table_name} (cat_ID, is_subdomain, not_subdomain, cat_theme, filter_pages, cat_link_title) VALUES ('{$_REQUEST['cat_ID']}', '{$is_subdomain}', '{$not_subdomain}', '{$cat_theme}', '{$filter_pages}', '{$link_title}')";
	}
	
	$wpdb->query($querystr);
}


function wps_action_parse_query ($query) {
	global $wps_this_subdomain, $wps_archive_subdomains;

	//--- If user wants root of subdomain to be an index
	if (get_option( WPS_OPT_SUBISINDEX ) != '') {
		// Check if we're on the root of a subdomain.
		// If so then tell WP_Query it's index not archive
		if ($wps_this_subdomain && $wps_this_subdomain->archive && ($_SERVER["REQUEST_URI"] == '/')) {		
			$query->is_archive = false;
		}
	}
}

function wps_action_page_meta ($type, $place, $post) {
	add_meta_box('subdomainsdiv', __('WP Subdomains'), 'wps_page_meta_box', 'page', 'normal', 'core');
}


function wps_page_meta_box($post) {
	
	//FIXME: change to checkbox for subdomain and add other values
?>
<p>
<label for="wps_page_subdomain">
Make the Page a subdomain?
</label>
<select name="wps_page_subdomain" id="wps_page_subdomain">
<option value="0">No</option>
<option value="1">Yes</option>
</select>
</p>
<p>
<?php wp_dropdown_categories('hide_empty=0&hierarchical=true'); ?>

</p>
<?php 
}

?>