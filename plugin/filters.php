<?php

/* Filters */

function wps_author_link($link, $id) {
	global $wps_subdomains;
	
	if ( get_option( WPS_OPT_SUBAUTHORS ) != "" ) {
		global $wps_subdomains;
		if ($wps_subdomains->authors[$id]) {
			$link = $wps_subdomains->authors[$id]->getSubdomainLink();
		}
	}
	
	return $link;
}

function wps_tag_link($link) {
	global $wps_this_subdomain;
	
	if ( (get_option( WPS_OPT_TAGFILTER ) != "") && $wps_this_subdomain->archive) {
		$link = $wps_this_subdomain->changeGeneralLink($link); 
	}
	
	return $link;
}

function wps_page_link( $link, $id ) {
	global $wps_subdomains, $wps_this_subdomain;
	
	// get the post
	$post = get_post( $id );
	
	if ( $subdomain = $wps_subdomains->getPageSubdomain( $post->ID ) ) {
		// It's a Subdomain page (or child of one) so grab the correct link
		$link = $wps_subdomains->pages[$subdomain]->changePageLink( $post->ID, $link );
	} else {
		// Cycle through until you find the ultimate parent page of this page.
		while ( $post->post_parent != 0 ) {
			$post = get_post( $post->post_parent );
		}
		
		// Check if this page is tied to a category, if so change the link appropriately
		if ( $catID = $wps_subdomains->findTiedPage( $post->ID ) ) {
			$link = $wps_subdomains->cats[$catID]->changePostLink( $link );
		} else if ( $wps_this_subdomain && $wps_this_subdomain->archive ) {
			//--- If the user wants to keep pages on subdomain being viewed
			// If it's not a Subdomain page or tied to a category 
			if (get_option( WPS_OPT_KEEPPAGESUB ) != '') {
				$link = $wps_this_subdomain->changePostLink( $link );
			}
		}
	}
	
	// return the link
	return $link;
}

function wps_post_link( $link, $id ) {
	global $csd_timeofchange, $wps_this_subdomain, $wps_subdomains;

	// Get the post
	if ( is_array( $id ) ) {
		$post = $id;
	} else {
		$post = get_post( $id );
	}
	/*
	if ( $post->post_status != "draft" && strtotime ( $post->post_date ) < $csd_timeofchange ) {
		return get_bloginfo ( "url" ) . "/archives/" . strftime ( "%Y/%m/", strtotime ( $post->post_date ) ) . $post->post_name . ".html";
	}
	*/
	// Check first if this post belongs to a subdomain we're on
	// if so then create the link for that subdomain
	// otherwise check if it belongs to any other subdomain category
	if ( $wps_this_subdomain && $wps_this_subdomain->archive && $wps_this_subdomain->isPostMember( $post->ID ) ) {
		// Post belongs to subdomain category we're currently on
		$link = $wps_this_subdomain->changePostLink( $link, $post->ID );
	} elseif ( $subdomain = $wps_subdomains->getPostSubdomain( $post->ID ) ) {
		// Post belongs to another subdomain category
		$link = $wps_subdomains->cats[$subdomain]->changePostLink( $link, $post->ID );
	}
	
	// return the link
	return $link;
}

function wps_category_link( $link, $term_id ) {
	global $wps_subdomains;
	
	// Check if the category is a Subdomain Category
	if ( $subdomain = $wps_subdomains->getCategorySubdomain( $term_id ) ) {
		// It is so create the correct link
		$link = $wps_subdomains->cats[$subdomain]->changeCategoryLink( $term_id, $link );
	}
	
	// return the link
	return $link;
}

function wps_filter_pages( $pages ) {
	// If we're not on an admin page then filter the get_pages results
	if ( ! is_admin() ) {
		global $wps_this_subdomain, $wps_subdomains, $wps_showall_pages;
		
		// Check config to see if we should filter
		if ( get_option( WPS_OPT_PAGEFILTER ) ) {
			// Now check if we we're on a subdomain page
			if ( $wps_this_subdomain && $wps_this_subdomain->type == WPS_TYPE_CAT ) {
				//--- Sub Domain
				

				// Check if we should show just pages tied to us or non-tied pages too
				if ( $wps_this_subdomain->filter_pages ) {
					// Just show pages tied to this subdomain
					foreach ( $pages as $key => $page ) {
						if ( ! in_array( $page->ID, $wps_this_subdomain->getTiedPages() ) && ! in_array( $page->ID, $wps_showall_pages ) ) {
							unset( $pages[$key] );
						}
					}
				} else {
					// Show pages tied to this subdomain and page not tied to any subdomain
					foreach ( $pages as $key => $page ) {
						if ( in_array( $page->ID, array_diff( $wps_subdomains->getTiedPages(), $wps_this_subdomain->getTiedPages() ) ) ) {
							unset( $pages[$key] );
						}
					}
				}
			} else {
				//--- Not a Subdomain

				// Check each page, if the ID matches a tied page unset it
				foreach ( $pages as $key => $page ) {
					if (!$wps_subdomains->isPageOnIndex($page->ID)) {
						unset( $pages[$key] );
					}
				}
			}
		}
	}
	
	// Return pages
	return $pages;
}

//--- Check if we should be using a custom template and change to it
function wps_change_template( $template_name ) {
	global $wps_this_subdomain;
	
	// Check the Custom Theme setting, if it's off then just return current theme
	if ( get_option( WPS_OPT_THEMES ) == "" ) {
		return $template_name;
	}
	
	// Check if this subdomain requires a Custom theme, if so return it.
	if ( $wps_this_subdomain && ($newtheme = $wps_this_subdomain->getTheme()) ) {
		return $newtheme;
	} else {
		return $template_name;
	}
}

function wps_month_link( $link ) {
	global $wps_this_subdomain;
	
	if ( get_option( WPS_OPT_ARCFILTER ) != '' ) {
		if ( $wps_this_subdomain && $wps_this_subdomain->archive ) {
			$link = $wps_this_subdomain->changePostLink( $link );
		}
	}
	
	return $link;
}

function wps_filter_archive_join( $join, $args ) {
	global $wpdb, $wps_this_subdomain;
	
	// Add join sql to limit the posts to those in the subdomain
	if ( $wps_this_subdomain && (get_option( WPS_OPT_ARCFILTER ) != '') ) {
		switch ( $wps_this_subdomain->type ) {
			case WPS_TYPE_CAT :
				$join = " INNER JOIN " . $wpdb->term_relationships . " AS tr ON " . $wpdb->posts . ".ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				break;
		}
	}
	
	// return the join sql
	return $join;
}

function wps_filter_archive_where( $where, $args ) {
	global $wps_this_subdomain;
	
	// Add where sql to limit the posts to those in the subdomain
	if ( $wps_this_subdomain && (get_option( WPS_OPT_ARCFILTER ) != '') ) {
		switch ( $wps_this_subdomain->type ) {
			case WPS_TYPE_CAT :
				$where .= " AND tt.taxonomy = 'category' AND tt.term_id IN (" . implode( $wps_this_subdomain->getAllIDs(), ',' ) . ')';
				break;
			case WPS_TYPE_AUTHOR :
				$where .= " AND post_author = '" . $wps_this_subdomain->id . "'";
				break;
		}
	}
	
	// return the where sql
	return $where;
}

function wps_filter_adjacent_join( $join, $in_same_cat = '', $excluded_categories = '' ) {
	global $wpdb, $wps_this_subdomain;
	
	// Add join sql to limit the posts to those in the subdomain category
	if ( $wps_this_subdomain && ! $in_same_cat && ! $excluded_categories ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
	}
	
	// return the join sql
	return $join;
}

function wps_filter_adjacent_where( $where, $in_same_cat = '', $excluded_categories = '' ) {
	global $wps_this_subdomain;
	
	// Add where sql to limit the posts to those in the subdomain category
	if ( $wps_this_subdomain && ! $in_same_cat && ! $excluded_categories ) {
		$where .= " AND tt.taxonomy = 'category' AND tt.term_id IN (" . implode( $wps_this_subdomain->getAllIDs(), ',' ) . ')';
	}
	
	// return the where sql
	return $where;
}

function wps_filter_bloginfo_url( $url, $show ) {
	global $wps_this_subdomain;
	
	// If we're on a category subdomain change bloginfo url calls to change the url 
	if ( $wps_this_subdomain && ($wps_this_subdomain->type == WPS_TYPE_CAT) ) {
		$url = $wps_this_subdomain->changeGeneralLink( $url );
	}
	
	// return the blog url 
	return $url;
}

function wps_filter_bloginfo( $value, $show ) {
	global $wps_this_subdomain;
	
	// If we're on a subdomain change bloginfo('name') calls to add ' - <subdomain name>' on end
	if ( $wps_this_subdomain ) {
		switch ( $show ) {
			case 'name' :
				$value .= ' - ' . $wps_this_subdomain->name;
				break;
		}
	}
	
	// return the bloginfo value
	return $value;
}

function wps_filter_general_url( $url ) {
	global $wps_this_subdomain;
	
	// If we're on a category subdomain change bloginfo url calls to change the url 
	if ( $wps_this_subdomain ) {
		$url = $wps_this_subdomain->changeGeneralLink( $url );
	}
	
	return $url;
}

// Filter the Category names, checking for a Custom link title. If it exists, return it
function wps_list_cats ($cat_name, $cat='') {
	global $wps_subdomains;

	if (is_object($cat) && in_array($cat->term_id, array_keys($wps_subdomains->cats))) {
		$link_title = $wps_subdomains->cats[$cat->term_id]->link_title;
		$cat_name = ($link_title?$link_title:$cat_name);
	}
	
	return $cat_name;
}

?>