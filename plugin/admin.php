<?php

function wps_page_rows( $pages ) {
	global $wps_page_metakey_subdomain, $wps_page_metakey_theme;
	
	$count = 0;
	$rows = '';
	
	foreach ( $pages as $page ) {
		$count ++;
		if ( $count % 2 ) {
			$rows .= '<tr class="alternate">';
		} else {
			$rows .= '<tr>';
		}
		
		$rows .= '<td><b><a href="page.php?action=edit&amp;post=' . $page ['ID'] . '">' . $page ['title'] . '</a></b></td>';
		$rows .= '<td>' . ($page [$wps_page_metakey_subdomain] ? 'Yes' : 'No') . '</td>';
		$rows .= '<td>' . ($page [$wps_page_metakey_theme] ? $page [$wps_page_metakey_theme] : 'None') . '</td>';
		$rows .= '<td>' . ($page ['category'] ? $page ['category'] : 'None') . '</td>';
		$rows .= '</tr>';
	}
	
	return $rows;
}

function wps_category_rows( $cats, $subdomains = 0 ) {
	
	$count = 0;
	$rows = '';
	
	if ( ! empty ( $cats ) ) {
		foreach ( $cats as $cat ) {
			$count ++;
			if ( $count % 2 ) {
				$rows .= '<tr class="alternate">';
			} else {
				$rows .= '<tr>';
			}
			
			//$rows .= '<td><b><a href="categories.php?action=edit&_wp_http_referer=index.php&_wp_original_http_referer=index.php&cat_ID=' . $cat['ID'] . '">' . $cat['name'] . '</a></b></td>';
			$rows .= '<td><b><a href="categories.php?action=edit&cat_ID=' . $cat ['ID'] . '">' . $cat ['name'] . '</a></b></td>';
			$rows .= '<td>' . $cat ['slug'] . '</td>';
			if ( $subdomains ) {
				$rows .= '<td>' . ($cat ['theme'] ? $cat ['theme'] : 'None') . '</td>';
				$rows .= '<td>' . ($cat ['filter_pages'] ? 'On' : 'Off') . '</td>';
			}
			$rows .= '</tr>';
		}
	}
	
	return $rows;
}

function wps_settings_plugin() {
	global $wps_page_metakey_theme, $wps_page_metakey_tie;
	?>
<div class="wrap">
<h2><?php
	_e ( 'Plugin Settings', 'wpro' )?></h2>
	
<?php print(wps_admin_notices()); ?>
	
<form method="post" action="options.php">
<?php
    if (function_exists('settings_fields')){
        settings_fields('wps-settings-group');
    }
    else{
	wp_nonce_field ( 'update-options' );
    }
	?>
	
<h3>Main Settings</h3>

<table class="form-table">
 
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Main Domain' )?></th>
		<td><input type="text" name="wps_domain"
			value="<?php
	echo get_option ( WPS_OPT_DOMAIN )?>"/> <span class="setting-description">If the Main Blog is located on a subdomain (e.g. http://blog.mydomain.com/), enter the Domain here (e.g. mydomain.com).</span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Disable Plugin' )?></th>
		<td><input type="checkbox" name="wps_disabled"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_DISABLED ) );
	?> /> <span class="setting-description">This will disable the plugin's functionality whilst allowing you to continue configuring it.</span></td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Make all Subdomains' )?></th>
		<td><input type="checkbox" name="wps_subdomainall"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_SUBALL ) );
	?> /> <span class="setting-description">This will turn all main Categories into Subdomains.<br />
		You can select to exclude categories from this by <a
			href="admin.php?page=wps_categories">editing them</a>.</span></td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Activate Page Subdomains' )?></th>
		<td><input type="checkbox" name="wps_subpages"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_SUBPAGES ) );
	?> /> <span class="setting-description">Activate the Page Subdomains.</span>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Activate Author Subdomains' )?></th>
		<td><input type="checkbox" name="wps_subauthors"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_SUBAUTHORS ) );
	?> /> <span class="setting-description">Activate the Author Subdomains.</span>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Activate Subdomain Themes' )?></th>
		<td><input type="checkbox" name="wps_themes"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_THEMES ) );
	?> /> <span class="setting-description">Activate the subdomain theme system.<br />
		To set different themes for each category, <a href="admin.php?page=wps_categories">Edit
		them</a>.<br />
		<br />
		You can also set different themes for each static subdomained page.
		Just set the custom field <b><?php
	echo $wps_page_metakey_theme;
	?></b> to the theme that you want to use. These theme names are the
		same ones given in the Edit Categories page.</span></td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Redirect Old Urls' )?></th>
		<td><input type="checkbox" name="wps_redirectold"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_REDIRECTOLD ) );
	?> /> <span class="setting-description">If someone comes to the site on an old category or page url it
		redirects them to the new Subdomain one.</span>
	</tr>

	<!-- 
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'No Category Base' )?></th>
		<td><input type="checkbox" name="wps_nocatbase"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_NOCATBASE ) );
	?> /> Turns
		off the Category base on subdomains. <br />
		<b>Warning:</b> Will cause problems on pages that have the same slug
		as categories and vice versa.</td>
	</tr>
	 -->
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Keep Pages on Subdomain' )?></th>
		<td><input type="checkbox" name="wps_keeppagesub"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_KEEPPAGESUB ) );
	?> /> <span class="setting-description">Activate this to have links to your normal pages, not Subdomain or Category Tied, remain on the subdomain being viewed<br />
		<b>Note:</b> This could be bad for SEO as some search engines will see this as duplicate pages.</span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Subdomain Roots as Indexes' )?></th>
		<td><input type="checkbox" name="wps_subisindex"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_SUBISINDEX ) );
	?> /> <span class="setting-description">The main page of Category and Author Subdomains will be tre <br />
	ated by Wordpress as an Index rather than an archive.<br />
		The difference between how an Index and an Archive is displayed is set by your theme.</span>
		</td>
	</tr>
</table>


<h3>Content Filters</h3>
<p>Configure filters to filter out content not belonging to the Subdomain you're on.</p>
<table class="form-table">

	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Use Archive Filtering' )?></th>
		<td><input type="checkbox" name="wps_arcfilter"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_ARCFILTER ) );
	?> /> <span class="setting-description">Change Archives to just show archive of the Category or Author Subdomain you're on.</span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Use Page Filtering' )?></th>
		<td><input type="checkbox" name="wps_pagefilter"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_PAGEFILTER ) );
	?> /> <span class="setting-description">Activate the Page filtering system. Use this to be able tie pages
		to categories.<br />
		You tie a page by setting custom field <b><?php
	echo $wps_page_metakey_tie;
	?></b> to the ID number of the category.</span></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><?php
	_e ( 'Use Tag Filtering' )?></th>
		<td><input type="checkbox" name="wps_tagfilter"
			value="<?php
	echo WPS_CHK_ON?>"
			<?php
	checked ( WPS_CHK_ON, get_option ( WPS_OPT_TAGFILTER ) );
	?> /> <span class="setting-description">Activate the Tag filtering system. Viewing Tags will show only 
	the posts that belong to the subdomain you are on.</span></td>
	</tr>
</table>

<input type="hidden" name="action" value="update" /> <input
	type="hidden" name="page_options"
	value="wps_domain,wps_disabled,wps_subdomainall,wps_themes,wps_pagefilter,wps_arcfilter,wps_nocatbase,wps_redirectold,wps_subpages,wps_subauthors,wps_keeppagesub,wps_subisindex, wps_tagfilter" />

<p class="submit"><input type="submit" name="Submit"
	value="<?php
	_e ( 'Save Changes' )?>" /></p>

</form>
</div>
<?php
}

function wps_settings_categories() {
	global $wpdb, $wps_subdomains;
	
	$categories = array ();
	
	// Build Cat Subdomain array (link, name, slug, theme, tied)
	foreach ( $wps_subdomains->cats as $catID => $cat ) {
		$categories ['subdomains'] [$catID] ['ID'] = $catID;
		$categories ['subdomains'] [$catID] ['name'] = $cat->name;
		$categories ['subdomains'] [$catID] ['slug'] = $cat->slug;
		$categories ['subdomains'] [$catID] ['theme'] = $cat->theme;
		$categories ['subdomains'] [$catID] ['filter_pages'] = $cat->filter_pages;
	}
	
	$cats_nosub = wps_getNonSubCats();
	
	if ( ! empty ( $cats_nosub ) ) {
		$tmp_cats = get_categories ( 'hide_empty=0&include=' . implode ( ',', $cats_nosub ) );
		
		// Build Excluded Cat array (link, name, slug, theme, tied)
		foreach ( $tmp_cats as $cat ) {
			$categories ['non_subdomains'] [$cat->term_id] ['ID'] = $cat->term_id;
			$categories ['non_subdomains'] [$cat->term_id] ['name'] = $cat->name;
			$categories ['non_subdomains'] [$cat->term_id] ['slug'] = $cat->slug;
		}
	} else {
		$categories ['non_subdomains'] = array ();
	}
	
	// Determine if MakeAllSubdomain is set.
	$suball = (get_option ( WPS_OPT_SUBALL ) != "");
	
	?>
<div class="wrap">
<h2><?php
	_e ( 'Categories', 'wpro' )?></h2>
<p>

	<?php print(wps_admin_notices()); ?>

	<?php
	if ( $suball ) {
		print ( '<b>Make All Subdomains</b> is turned <b>ON</b> so all main categories are turned into subdomains unless specifically excluded.' );
	}
	?>
	</p>

<p>A list of all main Categories currently configured to be Subdomains</p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">Category</th>
			<th scope="col">Subdomain</th>
			<th scope="col">Custom Theme</th>
			<th scope="col">Filter Pages</th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_category_rows ( $categories ['subdomains'], 1 ) );
	?>
	</tbody>
</table>
<p>A list of all main Categories currently configured to <b>not</b> be
Subdomains</p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">Category</th>
			<th scope="col">Subdomain</th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_category_rows ( $categories ['non_subdomains'], 0 ) );
	?>
	</tbody>
</table>
</div>
<?php
}

function wps_settings_pages() {
	global $wpdb, $wps_page_metakey_theme, $wps_page_metakey_subdomain, $wps_page_metakey_tie;
	
	$meta_keys = array ( $wps_page_metakey_theme, $wps_page_metakey_subdomain, $wps_page_metakey_tie );
	
	$sql = "SELECT Post_ID, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key in ('" . implode ( "','", $meta_keys ) . "') and meta_value != ''";
	$metapages = $wpdb->get_results ( $sql );
	
	$pages_root = array ();
	$pages_child = array ();
	$pages = array ();
	
	if ( ! empty ( $metapages ) ) {
		foreach ( $metapages as $metapage ) {
			$pages [$metapage->Post_ID] [$metapage->meta_key] = $metapage->meta_value;
		}
	}
	
	if ( ! empty ( $pages ) ) {
		foreach ( $pages as $pageid => $page ) {
			$pageobj = get_post ( $pageid );
			
			$page ['ID'] = $pageid;
			$page ['title'] = $pageobj->post_title;
			
			if ( $page [$wps_page_metakey_tie] ) {
				$page_cat = get_category ( $page [$wps_page_metakey_tie] );
				$page ['category'] = $page_cat->cat_name;
			}
			
			if ( $pageobj->post_parent == 0 ) {
				$pages_root [$pageid] = $page;
			} else {
				$pages_child [$pageid] = $page;
			}
		
		}
	}
	
	?>
<div class="wrap">
<h2><?php
	_e ( 'Pages', 'wpro' )?></h2>
	
<?php print(wps_admin_notices()); ?>
	
<p>A list of main Pages that are configured to use WP Subdomains
features.</p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">Page</th>
			<th scope="col">Subdomain</th>
			<th scope="col">Custom Theme</th>
			<th scope="col">Category</th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_page_rows ( $pages_root ) );
	?>
	</tbody>
</table>
<p>A list of child pages that are configured to WP Subdomains features.<br />
<b>Note:</b> Subdomain and Theme Settings will not function for child
pages.</p>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">Page</th>
			<th scope="col">Subdomain</th>
			<th scope="col">Custom Theme</th>
			<th scope="col">Category</th>
		</tr>
	</thead>
	<tbody>
<?php
	print ( wps_page_rows ( $pages_child ) );
	?>
	</tbody>
</table>
</div>
<?php
}

function wps_settings_welcome() {
	?>
<div class="wrap">
<h2><?php
	_e ( 'WP Subdomains', 'wpro' )?></h2>

<?php print(wps_admin_notices()); ?>
	
<p>This plugin was developed to make it easy for people to setup
subdomains that point directly to categories or pages on their wordpress
site.</p>
</div>
<div class="wrap">
<h2><?php
	_e ( 'Configuration', 'wpro' )?></h2>
<h4>Categories</h4>
<p>Lists the categories configured as subdomains and categories that
aren't. Shows their current Subdomain settings.</p>
<h4>Pages</h4>
<p>Lists pages that are configured to use WP Subdomains features.</p>
<h4>Settings</h4>
<p>General Plugin settings, you can enable and disable plugin features
here.</p>
</div>
<div class="wrap">
<h2><?php
	_e ( 'History &amp; Credits', 'wpro' )?></h2>
<p>Written by <a href="mailto:alex@casualgenius.com">Alex Stansfield</a>
of <a href="http://casualgenius.com">Casual Genius</a>.</p>
<p>Based on the <a
	href="http://www.biggnuts.com/wordpress-subdomains-plugin/">Subster
Rejunevation</a> wordpress plugin by <a href="http://www.biggnuts.com/">Dax
Herrera</a>. This plugin was subsequently updated by <a
	href="http://demp.se/y/2008/04/11/category-subdomains-plugin-for-wordpress-25/">Adam
Dempsey</a> and <a
	href="http://blog.youontop.com/wordpress/wordpress-category-as-subdomain-plugin-41.html">Gilad
Gafni</a>.</p>
<p>This version of WP Subdomains originally started as a few bug fixes
but as I found more and more things to add I realised only a rewrite
would enable me to make the changes I wanted for my site.</p>
</div>
<div class="wrap">
<h2><?php
	_e ( 'Copyright &amp; Disclaimer', 'wpro' )?></h2>
<p>Use of this application will be at your own risk. No guarantees or
warranties are made, direct or implied. The creators cannot and will not
be liable or held accountable for damages, direct or consequential. By
using this application it implies agreement to these conditions.</p>
</div>
<?php
}

function wps_add_options() {
	$file = 'wp-subdomains/plugin/admin.php';
	//$file = __FILE__;
	add_menu_page ( 'WP Subdomains', 'WP Subdomains', 7, $file, 'wps_settings_welcome' );
	add_submenu_page ( $file, 'Categories', 'Categories', 7, 'wps_categories', 'wps_settings_categories' );
	add_submenu_page ( $file, 'Pages', 'Pages', 7, 'wps_pages', 'wps_settings_pages' );
	add_submenu_page ( $file, 'Settings', 'Settings', 7, 'wps_settings', 'wps_settings_plugin' );
}

function wps_admin_init(){
	if (function_exists('register_setting')){
		// this whitelists form elements on the options page
		register_setting( 'wps-settings-group', 'wps_domain');
		register_setting( 'wps-settings-group', 'wps_disabled', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subdomainall', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subpages', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subauthors', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_themes', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_redirectold', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_keeppagesub', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_subisindex', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_arcfilter', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_pagefilter', 'wps_filter_on_off');
		register_setting( 'wps-settings-group', 'wps_tagfilter', 'wps_filter_on_off');
	}
}

function wps_filter_on_off($data){
	if ($data){
		return WPS_CHK_ON;
	}
	return '';
}



function wps_admin_footer( $content ) {
	// FIXME: Improved the javascript, still prefer to do this another way
	

	global $wpdb, $user_level, $wps_subdomains;
	$table_name = $wpdb->prefix . "category_subdomains";
	
	$cat_theme = "(none)";
	// Are we on the right page?
	if ( preg_match ( '|categories.php|i', $_SERVER ['SCRIPT_NAME'] ) && $_REQUEST ['action'] == 'edit' ) {

		// FIXME: use the classes as they have the information already.
		/*
		if ($catsub = $wps_subdomains->cats[$cat_ID]) {
			$cat_theme = $catsub->theme;
			$checked_exclude = ('1' == $csd_cat_options->not_subdomain) ? ' checked="checked"' : '';
			$checked_include = ('1' == $csd_cat_options->is_subdomain) ? ' checked="checked"' : '';
			$checked_filterpages = ('1' == $catsub->filter_pages) ? ' checked="checked"' : '';
			$link_title = $catsub->link_title;
		}
		*/
		$csd_cat_options = $wpdb->get_row ( "SELECT * FROM {$table_name} WHERE cat_ID = {$_REQUEST['cat_ID']};" );
		$cat_theme = stripslashes ( $csd_cat_options->cat_theme );
		$checked_exclude = ('1' == $csd_cat_options->not_subdomain) ? ' checked="checked"' : '';
		$checked_include = ('1' == $csd_cat_options->is_subdomain) ? ' checked="checked"' : '';
		$checked_filterpages = ('1' == $csd_cat_options->filter_pages) ? ' checked="checked"' : '';
		$link_title = stripslashes ( $csd_cat_options->cat_link_title );
?>
<script language="JavaScript" type="text/javascript"><!--
function addCatEditRow(th, tr) {
	var tblEdit = document.getElementById('editcat').getElementsByTagName('table')[0];
	var numRows = tblEdit.rows.length;
	var newRow = tblEdit.insertRow(numRows);
	newRow.setAttribute("class", "form-field");
	var newTH = document.createElement('th');
	newTH.setAttribute("valign", "top");
	newTH.setAttribute("scope", "row");
	newRow.appendChild(newTH);
	var newCell = newRow.insertCell(1);
	newTH.innerHTML = th;
	newCell.innerHTML = tr;
}

addCatEditRow('Make Subdomain', '<input type="checkbox" name="csd_include" value="true"<?php echo $checked_include; ?> /><br/>Select this to turn the category into a Subdomain.<br/>Category must be a main category.</td>');

addCatEditRow('Exclude from All', '<input type="checkbox" name="csd_exclude" value="true"<?php echo $checked_exclude; ?> /><br/>Select this to exclude the Category from being a subdomain when <b>Make all Subdomains</b> is selected in the plugin settings</td>');

addCatEditRow('Select Category Theme', '<select name="csd_cat_theme" id="wps_cat_theme"/></select><br/>Pick your theme name and activate Subdomain Themes in <a href="/wp-admin/admin.php?page=wps_settings">Plugin Settings</a>');

addCatEditRow('Custom Link Title', '<input type="text" name="csd_link_title" value="<?php	echo $link_title; ?>" /><br/>Pick a custom title to appear in any links to this Subdomain.');

addCatEditRow('Show only tied pages', '<input type="checkbox" name="csd_filterpages" value="true"<?php echo $checked_filterpages; ?> /><br/>Select this to only filter out pages not tied to categories, page lists will only show pages tied to this category');

var wps_themes_dropdown = document.getElementById('wps_cat_theme');

wps_themes_dropdown.options[0] = new Option('(none)','(none)');

<?php
		$themes = get_themes ();
		$count = 1;
		foreach ( $themes as $theme ) {
			if ( $cat_theme == $theme ['Template'] )
				echo "wps_themes_dropdown.options[" . ($count ++) . "] = new Option('{$theme['Template']}', '{$theme['Template']}', true);\n";
			else
				echo "wps_themes_dropdown.options[" . ($count ++) . "] = new Option('{$theme['Template']}', '{$theme['Template']}', false);\n";
		}
?>
//--></script>

<?php
	}
}

?>