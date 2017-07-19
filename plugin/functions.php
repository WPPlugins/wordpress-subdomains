<?php

function wps_blogurl() {
	/*if (get_option ( WPS_OPT_DOMAIN )) {
		$url = get_option ( WPS_OPT_DOMAIN );
	} else {*/
		$url = get_option ( 'home' );
		$url = substr ( $url, 7 );
		$url = str_replace ( "www.", "", $url );
	/*}*/
	
	return $url;
}

function wps_domain() {
	if (get_option ( WPS_OPT_DOMAIN )) {
		$domain = get_option ( WPS_OPT_DOMAIN );
	} else {
		$domain = get_option ( 'home' );
		$domain = substr ( $domain, 7 );
		$domain = str_replace ( "www.", "", $domain );
	}
	
	return $domain;
}

/*
function wps_subdomain() {
	$url = $_SERVER ['HTTP_HOST'];
	$urlparts = split ( '\.', $url );
	
	$subdomain = $urlparts [0];
	
	return ($subdomain == 'www' ? '' : $subdomain);
}
*/
//--- Find all the Pages marked with the showall meta key
function wps_showall_pages() {
	global $wpdb, $wps_page_metakey_showall;
	
	$pages = $wpdb->get_col ( "SELECT Post_ID FROM {$wpdb->postmeta} WHERE meta_key = '{$wps_page_metakey_showall}' and meta_value = 'true'" );
	
	return $pages;
}

//--- Get the details of the authors for use with Author Subdomains
function wps_get_authors( $exclude_admin = false ) {
	global $wpdb;
	
	$authors = $wpdb->get_results ( "SELECT ID, user_nicename, display_name from $wpdb->users " . ($exclude_admin ? "WHERE user_login <> 'admin' " : '') . "ORDER BY display_name" );
	
	return $authors;
}


// FIXME: old function, determine it's use and if it's needed
/*
function sd_posts_where( $where ) {
	global $wp_query, $wpdb, $csd_timeofchange, $cat;
	$append = getenv ( "REQUEST_URI" );
	if ( strpos ( $append, "archives/" ) == 1 ) {
		$matches = array ();
		
		preg_match ( "/archives\/(.*)\/(.*)\/(.*)\.html/", $append, $matches );
		$post = $wpdb->get_row ( "SELECT * FROM $wpdb->posts WHERE post_name='{$matches[3]}'" );
		$postID = $post->ID;
		$post_date = $post->post_date;
		
		if ( intval ( $postID ) && strtotime ( $post_date ) < $csd_timeofchange ) {
			$where = " AND ID=$postID ";
			
			$wp_query->is_single = true;
			$wp_query->is_404 = false;
			unset ( $wp_query->query_vars ["error"] );
			
			$wp_query->query = "name=" . $matches [3];
			$wp_query->query_vars ["name"] = $matches [3];
		}
	}
	
	return $where;
}
*/

// Wordpress's Canonical Redirect doesn't really care much about subdomains so this not really needed
function wps_redirect_canonical( $redirect_url, $requested_url ) {
	/*
	// Grab the hostname of the requested url
	$parsed_url = parse_url($requested_url);
	$requested_host = $parsed_url['host'];
	
	$subdomains = explode ( ".", $requested_host );
	$subdomain = $subdomains[0];
	
	*/
	/*
	$blogurl = wps_blogurl ();
	
	$url = str_replace ( "http://", "", $requested_url );
	
	//the stuff after the host
	$append = substr ( $url, strpos ( $url, $blogurl ) + strlen ( $blogurl ) + 1 );
	
	//grab the potentional subdomains;
	$url = substr ( $url, 0, strpos ( $url, $blogurl ) );
	if ( strlen ( $url ) )
		$url = substr ( $url, 0, strlen ( $url ) - 1 );
	
	//$subdomains = split ( "\.", $url );
	$subdomains = explode ( ".", $url );
	
	$subdomain = $subdomains [sizeof ( $subdomains ) - 1];
	
	if ( $subdomain != "www" ) {
		return $requested_url;
	} else {
		return "";
	}
	
	global $wps_this_subdomain;

	if ($wps_this_subdomain) {
		return $requested_url;
	} else {
		return $redirect_url;
	}
*/
	return $redirect_url;
}

function wps_admin_notices() {
	global $wps_permalink_set;

	$notices = '';
	
	if (get_option(WPS_OPT_DISABLED) != '') {
		$notices .= '<h3>Note: you currently have the plugin set as DISABLED.</h3>';
	}
	
	if (!$wps_permalink_set) {
		$notices .= '<h3>Warning: you do not have permalinks configured so this plugin cannot operate.</h3>';	
	}
	
	return $notices;
}

function getPageChildren($pageID) {
	$childrenARY = array();
	
	$children = & get_children( 'post_status=publish&post_type=page&post_parent=' . $pageID );

	if ( $children ) {
		foreach (array_keys( $children ) as $child) {
			$childrenARY[] = $child;
			$childrenARY = array_merge($childrenARY, getPageChildren($child));	
		}
	}
	
	return $childrenARY;
}

function wps_getUrlPath($url) {
	$parsed_url = parse_url($url);
	
	if(isset($parsed_url['path'])) {
  	$path = ( (substr($parsed_url['path'], 0, 1) == '/') ? substr($parsed_url['path'], 1) : $parsed_url['path'] );
	} else {
		$path = '';
	}
	
	$path .= ( isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '' );
	$path .= ( isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '' );

	return $path;	
}

function wps_getNonSubCats() {
	global $wps_subdomains;
	
	$cats_root = get_terms( 'category', 'hide_empty=0&parent=0&fields=ids' );
		
	return array_diff( $cats_root, array_keys($wps_subdomains->cats) );
}

?>