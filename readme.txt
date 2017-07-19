=== Plugin Name ===
Contributors: casualgenius
Donate link: http://webdev.casualgenius.com/projects/wordpress-subdomains/donate
Tags: subdomains, categories, pages, themes 
Requires at least: 2.7
Tested up to: 2.9
Stable tag: 0.6.9

WP Subdomains is a plugin for Wordpress that allows you to setup your main categories, pages and authors as subdomains.

== Description ==

WP Subdomains is a plugin for wordpress that allows you to setup your main categories, pages and authors as subdomains. 
Other features include custom themes and site layout widgets.

  * Setup main categories as subdomains
  * Setup main pages as subdomains
  * Setup author archives as subdomains
  * Tie pages to categories so they only appear under that category or category subdomain
  * Pick a different theme for each subdomain than the main site's theme
  * Sitelist widget, a sidebar widget that lists the Category subdomains of the site
  * Categories widget, a sidebar widget that lists the sub categories of that Category subdomain

== Installation ==

This section describes how to install the plugin.

1. Upload and Unarchive
2. Copy the 'wp-subdomains' folder to the '/wp-content/plugins/' directory
3. Activate the plugin in Wordpress

= Upgrading = 

If Upgrading manually follow the above instrutions, but first deactivate the existing plugin.
This is so when you activate the new plugin it'll run any required updates.

= Configuration =

See the other notes.

== Frequently Asked Questions ==

= Where can I see this in action =

My site is based on this plugin: 
http://casualgenius.com

= I don't know how to setup a subdomain =

Please speak to your hosting provider's technical support. They should be able to help you setup the subdomain, just remember to point the document root to the same path as your main wordpress document root.

== Screenshots ==

None

== Changelog ==

= 0.6.9 = 

Minor Fix

= 0.6.8 = 

Bug Fixes

* WP 2.9 Support
* WPMU Support
* Show Empty Subdomain Categories

= 0.6.6 =

Added Features

* Have your main blog on a subdomain (e.g. http://blog.mydomain.com/)
* Tag filters, go to a tag page on a subdomain and see only those posts that are on that subdomain.

Bug Fixes

* Fixed big bug introduced by move to wordpress hosting where the database table the plugin needs isn't created 
* Made some changes to try and reduce memory usage on large sites, more to come


= 0.6.5 = 

* Moved to hosting on Wordpress.org
* Minor code improvements

= 0.6.2 =

Bugs Fixed

* removed use of get_post as it was causing memory and sql problems
* fixed issues with wordpress 2.8

= 0.6.1 =

Added Features

* Add options
  * Keeping Pages on the subdomain, changed to an option for SEO reasons
  * Removing archive status from Subdomain root pages
* Added `wps_on_main_index` custom field to make tied pages still appear on your main blog. Useful if you have the subdomain indexes noindexed for SEO purposes
	
Bugs Fixed

* Now creates links for all subdomain pages not just five
* Fixed a daft problem that only put the first layer of sub pages of a subdomain page on that subdomain. Oops
* Fixed a problem regarding comments on 1st tier posts using /%category%/%postname% permalink
* Fixed a problem with filtering posts by category in the admin section
* Included an option to use a custom SQL query if you run into memory issues from large numbers of posts
* Fixed bug with IE and editing Category options

= 0.6.0 =

Added Features

* Keep links to non-subdomain pages that are not tied to a category on the subdomain we're viewing
* Date Archive filter, let you choose to have archives filtered down to the category of the subdomain you're on
* Added plugin setting option to turn on/off: Plugin Functionality, Date Archive Filter, Page Subdomains, Author Subdomains
* Display warning and disable plugin if no permalinks setup
* Support permalinks with %category% in
* Add author subdomains
* Custom Link title for Category Subdomains

Bugs Fixed

* Fixed a bug that made SEO All in One plugin work incorrectly on Category Subdomains

== Subdomain Setup ==

The plugin uses the Category slug, Page slug, or Author name as the subdomain name.

You'll need to configure your webserver for each subdomain you want to use. It uses the same wordpress install as your main blog.
If you run your own server you should know how todo this already (Apache users can just add ServerAlias to their existing vhost)
If you use managed hosting then add a subdomain and set it's document path to that of your main blog.

Be sure to add a DNS entry to point your subdomains to your server.

Note, some hosting services allow a forward all rule that will forward all subdomains to your server.

For each Category you want to convert, edit the category in wordpress admin and enable the "Make Subdomain" option.

For each Page you want to convert, edit the page in wordpress admin and add the custom field `wps_page_subdomain` with value "true"

For Author subdomains just switch enable the "Activate Author Subdomains" setting. You cannot pick and choose which authors are subdomains. 
It is either all or none.

== Plugin Configuration ==

= Main Domain =
To use the subdomain blog feature (e.g. main page at http://blog.mydomain.com) you’ll need to enter your blog domain (e.g. mydomain.com).

= Disable Plugin =
Allows you to disable the plugin functionality whilst still being able to configure it

= Make all Subdomains =
Makes all main categories into subdomains by default, this can be overridden in the category settings

= Activate Page Subdomains =
Enable this if you want to make use of the Page Subdomains feature

= Activate Author Subdomains =
Enable this if you want authors to be subdomains. Note you can't configure which authors, it enables them all.

= Activate Subdomain Themes =
Turns on the custom themes for subdomains so you can have them display in a different theme to your main site

= Redirect Old Urls =
Will give 301 redirects to people coming to Subdomained Categories and Pages on old URLs. Doesn't work yet for posts. 

= Keep Pages on Subdomain =
Activate this for links to wordpress Pages to stay on the domain being viewed, unless they are category 
tied or subdomains themselves.

= Subdomain Roots as Indexes =
Activate this for subdomains root pages to be treated as an index page rather than an archive page.
(On by default prior to being an option - See NOTES section).

= Use Archive Filtering =
If you're on a Category or Author subdomain then date archives will show only what belongs to that Category/Author

= Use Page Filtering =
Turns on the ability to tie pages to specific subdomain categories

= Use Tag Filtering =
Viewing Tags on a subdomain will show only the posts that belong to the subdomain you are on.

== Category Configuration ==

= Make Subdomain =
Will turn this Category into a subdomain

= Exclude from All =
When using "Make all Subdomains" will exclude this category

= Select Category Theme =
Select the theme for this category to use, needs "Activate Subdomain Themes" turned on to work

= Custom Link Title =
Add a custom title to the links to this subdomain

= Show only tied pages =
When viewing this subdomain will filter out pages not tied to it. 

== Page Configuration ==

= Subdomain Page =
To make static pages act like subdomains, create a custom field variable `wps_page_subdomain` and set it to true.

= Custom Theme = 
To set a theme for a page subdomain, create a custom field variable `wps_page_theme` and set it to the theme that you want to use. The theme name is the same as you'll find it on the edit categories page. Themes only work for pages that are setup as subdomains.

= Tie a Page to a Category =
To tie a static page to a subdomained category, so that it displays this URL category.yourdomain.com/static-page, create a custom field variable `wps_tie_to_category` and set it to the category ID.

= Show Page on all Categories =
To make a page show on all pages even if a category is set to only show tied pages, create a custom field varaible called `wps_showall` and set it to true.

== Notes ==

* If using Subdomains you'll probably want your cookie to span the subdomains and not just your own domain. In order to achieve this you need to add an option to your wp-config.php:

  define('COOKIE_DOMAIN', '.mydomain.com');

  Where mydomain.com is your domain name. Remember to add the preceeding dot (.) as this is what makes it work.

* In 0.6.1 Subdomain Roots as Indexes became an option where before it was just on by default. If you want your subdomain root pages treated like your main blog index then switch this on. The differences you'll see are down to how your theme handles an Index compared with an Archive.
