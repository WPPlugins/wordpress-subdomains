<?php

class WpsSubDomains {
	
	var $cats = array();
	var $pages = array();
	var $authors = array();
	//var $cats_root = array();
	//var $cats_nosub = array();
	var $pages_on_index = false;
	
	function WpsSubDomains() {
		global $wpdb, $wps_page_metakey_subdomain;
		
		$table_name = $wpdb->prefix . "category_subdomains";
		
		//--- Get Root Categories
		// get_categories version
		/*
		$cats_root = array();
		foreach ( get_categories( 'hide_empty=false' ) as $cat ) {
			if ( $cat->parent == 0 ) {
				$cats_root[] = $cat->term_id;
			}
		}
		*/
		$cats_root = get_terms( 'category', 'hide_empty=0&parent=0&fields=ids' );
		
		/* SQL Version
		$sql_cats = "select term_id from {$wpdb->term_taxonomy} where parent = 0 and taxonomy = 'category'";
		$cats_root = $wpdb->get_col( $sql_cats );
		*/
		
		//--- Work out the Categories to subdomain
		if ( get_option( WPS_OPT_SUBALL ) != "" ) {
			$cats_exclude = $wpdb->get_col( "SELECT cat_ID FROM {$table_name} WHERE not_subdomain = 1" );
			$cats = array_diff( $cats_root, $cats_exclude );
		} else {
			$cats_include = $wpdb->get_col( "SELECT cat_ID FROM {$table_name} WHERE is_subdomain = 1" );
			$notcats = array_diff( $cats_include, $cats_root );
			$cats = array_diff( $cats_include, $notcats );
		}
		
		// Set the array of root categories that aren't being turned into Subdomains
		//$this->cats_nosub = array_diff( $cats_root, $cats );
		
		//--- Create Category Subdomains
		foreach ( $cats as $cat ) {
			$this->cats[$cat] = new WpsSubDomainCat( $cat );
		}
		
		//--- Subdomain Pages if option is turned on 
		if ( get_option( WPS_OPT_SUBPAGES ) != "" ) {
			//--- Get Pages that are to be Subdomains
			//$pages = get_posts( 'numberposts=-1&post_type=page&meta_key=' . $wps_page_metakey_subdomain . '&meta_value=true' );
			//$pages = get_pages( 'meta_key=' . $wps_page_metakey_subdomain . '&meta_value=true' );
			$pages = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '".$wps_page_metakey_subdomain."' and meta_value = 'true'");
			
			//--- Create Page Subdomains
			foreach ( $pages as $page ) {
				//$this->pages[$page->ID] = new WpsSubDomainPage( $page );
				$this->pages[$page] = new WpsSubDomainPage( $page );
			}
		}
		
		//--- Subdomain Authors if option is turned on 
		if ( get_option( WPS_OPT_SUBAUTHORS ) != "" ) {
			//--- Get Authors
			$authors = wps_get_authors();
			
			//--- Create Author Subdomains
			foreach ( $authors as $author ) {
				$this->authors[$author->ID] = new WpsSubDomainAuthor( $author );
			}
		}
	
	}
	
	function getPostSubdomain( $postID ) {
		foreach ( $this->cats as $id => $cat ) {
			if ( $cat->isPostMember( $postID ) ) {
				return $id;
			}
		}
		
		return false;
	}
	
	function getCategorySubdomain( $catID ) {
		foreach ( $this->cats as $id => $cat ) {
			if ( $cat->isCatMember( $catID ) ) {
				return $id;
			}
		}
		
		return false;
	}
	
	function getPageSubdomain( $pageID ) {
		foreach ( $this->pages as $id => $page ) {
			if ( $page->isPageMember( $pageID ) ) {
				return $id;
			}
		}
		
		return false;
	}
	
	function getCatIDs( $sort = '' ) {
		$sort_terms = array( 'name', 'slug' );
		
		if ( $sort && in_array( $sort, $sort_terms ) ) {
			$sd_sort = array();
			
			foreach ( $this->cats as $id => $cat ) {
				$sd_sort[$id] = $cat->{$sort};
			}
			
			asort( $sd_sort );
			
			return array_keys( $sd_sort );
		} else {
			return array_keys( $this->cats );
		}
	}
	
	function getPageIDs( $sort = '' ) {
		$sort_terms = array( 'name', 'slug' );
		
		if ( $sort && in_array( $sort, $sort_terms ) ) {
			$sd_sort = array();
			
			foreach ( $this->pages as $id => $pages ) {
				$sd_sort[$id] = $pages->{$sort};
			}
			
			asort( $sd_sort );
			
			return array_keys( $sd_sort );
		} else {
			return array_keys( $this->pages );
		}
	}
	
	function getThisSubdomain() {
		$url = getenv( 'HTTP_HOST' ) . getenv( 'REQUEST_URI' );
		//$subdomains = split( "\.", $url );
		$subdomains = explode( ".", $url );
		$subdomain = $subdomains[0];
		
		foreach ( $this->cats as $cat ) {
			if ( $cat->slug == $subdomain ) {
				return $cat;
			}
		}
		
		foreach ( $this->pages as $page ) {
			if ( $page->slug == $subdomain ) {
				return $page;
			}
		}
		
		foreach ( $this->authors as $author ) {
			if ( $author->slug == $subdomain ) {
				return $author;
			}
		}
		
		return false;
	}
	
	function findTiedPage( $pageID ) {
		
		foreach ( $this->cats as $catID => $cat ) {
			if ( in_array( $pageID, $cat->getTiedPages() ) ) {
				return $catID;
			}
		}
		
		return false;
	
	}
	
	function getTiedPages() {
		$tied_pages = array();
		
		foreach ( $this->cats as $cat ) {
			$tied_pages = array_merge( $tied_pages, $cat->getTiedPages() );
		}
		
		return array_unique( $tied_pages );
	}

	function getPagesOnIndex() {
		if ( ! $this->pages_on_index ) {
			global $wpdb, $wps_page_on_main_index;
			
			$this->pages_on_index = array();
			
			//$pages = get_posts( 'numberposts=-1&post_type=page&meta_key=' . $wps_page_on_main_index . '&meta_value=true' );
			//$pages = get_pages( 'meta_key=' . $wps_page_on_main_index . '&meta_value=true' );
			$pages = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '".$wps_page_on_main_index."' and meta_value = 'true'");
			
			foreach ($pages as $page) {
				/*
				//$this->pages_on_index[] = $page->ID;
				
				//$this->pages_on_index = array_merge( $this->pages_on_index, getPageChildren($page->ID));
				*/
				$this->pages_on_index[] = $page;
				
				$this->pages_on_index = array_merge( $this->pages_on_index, getPageChildren($page));
				/*
				$children = & get_children( 'post_status=publish&post_type=page&post_parent=' . $page->ID );
				
				if ( $children ) {
					$this->pages_on_index = array_merge( $this->pages_on_index, array_keys( $children ) );
				}
				*/
			}
			
			// FIXME: I forgot why do we do this
			$this->pages_on_index = array_unique( $this->pages_on_index );
		}
		
		return $this->pages_on_index;
	}
	
	// Is this the right place for this? Perhaps it should be a stand alone function
	function isPageOnIndex($pageID) {		
		if (!in_array($pageID, $this->getTiedPages()) || in_array($pageID, $this->getPagesOnIndex())) {
			return true;
		} else {
			return false;
		}
	}
	
}

class WpsSubDomain {
	
	var $id;
	var $name;
	var $type;
	var $slug;
	var $theme;
	var $archive;
	var $children = false;
	var $posts = false;
	var $archive_subdomains = array( WPS_TYPE_CAT, WPS_TYPE_AUTHOR );
	
	function WpsSubDomain( $id, $type ) {
		$this->id = $id;
		$this->type = $type;
		$this->archive = in_array( $this->type, $this->archive_subdomains );
	}
	
	function getPosts () {
		// Fetch the subdomain's posts
		if ( $this->archive ) {
			global $wpdb;
			
			// Use custom SQL or wordpress's get_posts function
			$where = '';
			$join = '';
			
			switch ( $this->type ) {
				case WPS_TYPE_AUTHOR :
					$where = 'posts.post_author=' . $this->id;
					break;
				case WPS_TYPE_CAT :
					$join =  "JOIN {$wpdb->term_relationships} tr ON posts.ID = tr.object_id JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id";
					$where = "tt.taxonomy = 'category' AND tt.term_id in (". implode(',', $this->getAllIDs()) .")";
					break;
				default :
					break;
			}
			
			if ($where) {
				// Fetch just the posts, not pages
				$where .= " AND posts.post_type != 'page'";
				
				// If we're in the admin section, grab posts that don't appear on the site yet
				if ( is_admin() ) {
					$where .= " AND posts.post_status in ('publish', 'future' , 'draft' , 'pending')";
				} else {
					$where .= " AND posts.post_status = 'publish'";						
				}
				
				// Go get the IDs
				$this->posts = $wpdb->get_col( "SELECT DISTINCT posts.ID FROM {$wpdb->posts} posts ".$join." WHERE ".$where );
			} else {
				$this->posts = array();
			}
		}
	}
	
	function isPostMember( $postID ) {
		if ($this->posts === false) {
			$this->getPosts();
		}
		
		return in_array( $postID, $this->posts );
	}
	
	function changePostLink( $link, $postid = 0 ) {
		//$blogurl = wps_blogurl();
		//$path = substr( $link, strpos( $link, $blogurl ) + strlen( $blogurl ) + 1 );
		
		$path = wps_getUrlPath($link);
		
		$link = $this->getSubdomainLink();
		
		switch ( $this->type ) {
			case WPS_TYPE_CAT :
				$link .= $this->changePostPath( $path, $postid );
				break;
			/*
			case WPS_TYPE_PAGE :
				$link = $sublink;
				break;
			*/
			case WPS_TYPE_AUTHOR :
				$link .= $path;
				break;
		}
		
		return $link;
	}
	
	function getSubdomainLink() {
		//$blogurl = wps_blogurl();
		//$link = "http://" . $this->slug . "." . $blogurl . "/";
		
		$link = "http://" . $this->slug . "." . wps_domain() . "/";
		
		return $link;
	}
	
	function changeGeneralLink( $link ) {
		$path = wps_getUrlPath($link);
		$link = $this->getSubdomainLink() . $path;
		
		//$link = substr( $link, 7 );
		//$link = str_replace( "www.", "", $link );
		//$link = 'http://' . $this->slug . '.' . $link;
		
		return $link;
	}
	
	function getTheme() {
		if ( ! $this->theme || $this->theme == '(none)' ) {
			return false;
		} else {
			return $this->theme;
		}
	}
	
	function getAllIDs() {
		$id_array = $this->getChildren();
		
		array_unshift( $id_array, $this->id );
		
		return $id_array;
	}
	
	function getRewriteRules() {
		
		switch ( $this->type ) {
			case WPS_TYPE_CAT :
				$field = 'category_name';
				break;
			case WPS_TYPE_PAGE :
				return false;
				break;
			case WPS_TYPE_AUTHOR :
				$field = 'author_name';
				break;
		}
		
		$rules = array();
		$rules["feed/(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $field . "=" . $this->slug . "&feed=\$matches[1]";
		$rules["(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $field . "=" . $this->slug . "&feed=\$matches[1]";
		$rules["page/?([0-9]{1,})/?$"] = "index.php?" . $field . "=" . $this->slug . "&paged=\$matches[1]";
		$rules["/?$"] = "index.php?" . $field . "=" . $this->slug;
		
		return $rules;
	}
	
	function addRewriteFilter( $rules ) {
		if ( $this->archive && ! empty( $rules ) ) {
			// Filter by Author or Category
			switch ( $this->type ) {
				case WPS_TYPE_CAT :
					$field = 'category_name';
					break;
				case WPS_TYPE_AUTHOR :
					$field = 'author_name';
					break;
				default :
					$field = false;
					break;
			}
			
			if ( $field ) {
				// Add the filter to each rule
				foreach ( $rules as $regexp => $rule ) {
					$rules[$regexp] = $rule . '&' . $field . '=' . $this->slug;
				}
			}
		}
		
		return $rules;
	}
}

class WpsSubDomainCat extends WpsSubDomain {
	
	var $filter_pages;
	var $tied_pages = false;
	var $link_title = false;
	
	function WpsSubDomainCat( $id ) {
		global $wpdb;
		
		$this->WpsSubDomain( $id, WPS_TYPE_CAT );
		
		$cat = get_category( $this->id );
		
		// Get Category details
		$this->name = $cat->name;
		$this->slug = $cat->slug;
		
		// Get Sub Domain options
		$table_name = $wpdb->prefix . "category_subdomains";
		$sd_options = $wpdb->get_row( "SELECT * FROM {$table_name} WHERE cat_ID = {$this->id}" );
		$this->filter_pages = $sd_options->filter_pages;
		$this->theme = $sd_options->cat_theme;
		if ($sd_options->cat_link_title) {
			$this->link_title = $sd_options->cat_link_title;
		}
	}
	
	function changePostPath( $path, $postid ) {
		$permalink = get_option( 'permalink_structure' );
		
		if ( strpos( $permalink, '%category%' ) != false ) {
			$cats = get_the_category( $postid );
			
			if ( $cats ) {
				usort( $cats, '_usort_terms_by_ID' ); // order by ID
				$original_path = $this->getCategoryPath( $cats[0], false );
			}
			
			$common_cats = array();
			
			foreach ( $cats as $cat ) {
				if ( in_array( $cat->term_id, $this->getAllIDs() ) ) {
					$common_cats[] = $cat->term_id;
				}
			}
			
			reset( $common_cats );
			$catid = current( $common_cats );
			
			$new_path = $this->getCategoryPath( get_category( $catid ) );
			
			$path = str_replace( $original_path, $new_path, $path );
		}
		
		return $path;
	}
	
	function getCategoryPath( $cat, $hide_subdomain = true ) {
		$category_path = $cat->slug;
		
		if ( $parent = $cat->parent ) {
			$category_path = get_category_parents( $parent, false, '/', true ) . $category_path;
		} else {
			$category_path .= '/';
		}
		
		if ( $hide_subdomain && in_array( $cat->term_id, $this->getAllIDs() ) ) {
			if ( $parent ) {
				$slug_length = strlen( $this->slug );
				if ( substr( $category_path, 0, $slug_length ) == $this->slug ) {
					$category_path = substr( $category_path, $slug_length + 1 );
				}
			} else {
				$category_path = '';
			}
		}
		
		return ($category_path);
	}
	
	function changeCategoryLink( $catID, $link = '' ) {
		global $wps_category_base;
		
		//$blogurl = wps_blogurl();
		
		if ( $catID == $this->id ) {
			//$link = "http://" . $this->slug . "." . $blogurl . "/";
			$link = $this->getSubdomainLink();
		} else {
			$this_category = get_category( $catID );
			
			$kid_string = '';
			
			while ( $this_category->term_id != $this->id ) {
				$kid_string = $this_category->slug . "/" . $kid_string;
				$this_category = get_category( $this_category->category_parent );
			}
			
			if ( get_option( WPS_OPT_NOCATBASE ) ) {
				//$link = "http://" . $this->slug . "." . $blogurl . "/" . $kid_string;
				$link = $this->getSubdomainLink() . $kid_string;
			} else {
				//$link = "http://" . $this->slug . "." . $blogurl . "/" . $wps_category_base . $kid_string;
				$link = $this->getSubdomainLink() . $wps_category_base . $kid_string;
			}
		}
		
		return $link;
	}
	
	function getTiedPages() {
		global $wpdb, $wps_page_metakey_tie;
		
		if ( get_option( WPS_OPT_PAGEFILTER ) ) {
			if ( ! $this->tied_pages ) {
				// FIXME: URGENT!!! Can use the get_posts function for this, this causes a bug
				$this->tied_pages = $wpdb->get_col( "SELECT Post_ID FROM {$wpdb->postmeta} WHERE meta_key = '{$wps_page_metakey_tie}' and meta_value = '" . $this->id ."'" );
				
				foreach ( $this->tied_pages as $pageID ) {
					$this->tied_pages = array_merge( $this->tied_pages, getPageChildren( $pageID ) );
					/*
					$children = & get_children( 'post_type=page&post_parent=' . $pageID );
					if ( $children ) {
						$this->tied_pages = array_merge( $this->tied_pages, array_keys( $children ) );
					}
					*/
				}
				
				$this->tied_pages = array_unique( $this->tied_pages );
			}
		} else {
			$this->tied_pages = array();
		}
		
		return $this->tied_pages;
	}
	
	function getChildren() {
		if ( ! $this->children ) {
			// Get Subdomain Children
			$this->children = array();
			foreach ( get_categories( 'child_of=' . $this->id ) as $child ) {
				$this->children[] = $child->term_id;
			}
		}
		
		return $this->children;
	}
	
	function isCatMember( $catID ) {
		if ( in_array( $catID, $this->getAllIDs() ) ) {
			return true;
		} else {
			return false;
		}
	}

}

class WpsSubDomainPage extends WpsSubDomain {

	function WpsSubDomainPage( $page ) {
		global $wpdb, $wps_page_metakey_theme;
		
		$meta_data = array('theme' => $wps_page_metakey_theme);
		
		// Check if we've got the page object, if not it may be the ID so go get the row
		if ( ! is_object( $page ) ) {
			$page = get_post( $page );
		}
		
		$this->WpsSubDomain( $page->ID, WPS_TYPE_PAGE );
		
		// Get Category details
		$this->name = $page->post_title;
		$this->slug = $page->post_name;
		
		foreach ($meta_data as $var => $field) {
			$this->{$var} = get_post_meta($this->id, $field, true);
		}
	}
	
	function isPageMember( $pageID ) {
		foreach ( $this->getAllIDs() as $id ) {
			if ( $id == $pageID ) {
				return true;
			}
		}
		
		return false;
	}
	
	function changePageLink( $pageID, $link ) {
		
		//$blogurl = wps_blogurl();
		
		if ( in_array( $pageID, $this->getAllIDs() ) ) {
			if ( $pageID == $this->id ) {
				//$link = "http://" . $this->slug . "." . $blogurl . "/";
				$link = $this->getSubdomainLink();
			} else {
				$this_page = get_post( $pageID );
				
				$kid_string = '';
				
				while ( $this_page->ID != $this->id ) {
					$kid_string = $this_page->post_name . "/" . $kid_string;
					$this_page = get_post( $this_page->post_parent );
				}
				
				//$link = "http://" . $this->slug . "." . $blogurl . "/" . $kid_string;
				$link = $this->getSubdomainLink() . $kid_string;
			
			}
		}
		
		return $link;
	}
	
	function getChildren() {
		if ( ! $this->children ) {
			// Get Page Children
			$this->children = getPageChildren($this->id);
			/*
			$this->children = array();
			
			$children = & get_children( 'post_status=publish&post_type=page&post_parent=' . $this->id );
			
			if ( $children ) {
				$this->children = array_keys( $children );
			}
			*/
		}
				
		return $this->children;
	}

}

class WpsSubDomainAuthor extends WpsSubDomain {
	
	var $posts = array();
	
	function WpsSubDomainAuthor( $author ) {
		global $wpdb;
		
		// Check if we've got the author array, if not it may be the ID so go get the row
		if ( ! is_object( $author ) ) {
			$author = $wpdb->get_row( "SELECT ID, user_nicename, display_name from $wpdb->users WHERE ID = $author" );
		}
		
		$this->WpsSubDomain( $author->ID, WPS_TYPE_AUTHOR );
		
		// Get Category details
		$this->name = $author->display_name;
		$this->slug = $author->user_nicename;
	}
	
	function isPageMember( $pageID ) {
		foreach ( $this->getAllIDs() as $id ) {
			if ( $id == $pageID ) {
				return true;
			}
		}
		
		return false;
	}
	
	function getChildren() {
		return array();
	}

}

?>