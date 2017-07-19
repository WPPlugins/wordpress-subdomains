<?php

class WpsCategoriesWidget 
{
  /**
   * Default values
   */
  
  var $title = '';
  var $main = '';

  // static init callback
  function init() 
  {
    // Check for the required plugin functions. This will prevent fatal
    // errors occurring when you deactivate the dynamic-sidebar plugin.
    if ( !function_exists('register_sidebar_widget') )
      return;

    $widget = new WpsCategoriesWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('WPS Categories', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('WPS Categories', array($widget,'control'));
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('csd_widget_categories');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );
    
    
    if ( !empty($_POST['csd-categories-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['csd-categories-title'])));
			$options['count'] = isset($_POST['csd-categories-count']);
			$options['hierarchical'] = isset($_POST['csd-categories-hierarchical']);
			$options['dropdown'] = isset($_POST['csd-categories-dropdown']);

		  update_option('csd_widget_categories', $options);
     }    

		$title = attribute_escape( $options['title'] );	
		$count = (bool) $options['count'];
		$hierarchical = (bool) $options['hierarchical'];
		$dropdown = (bool) $options['dropdown'];

?>
			<p>
				<label for="categories-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="categories-title" name="csd-categories-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
			<p>
				<label for="categories-dropdown">
					<input type="checkbox" class="checkbox" id="categories-dropdown" name="csd-categories-dropdown" <?php checked( $dropdown, true ); ?> />
					<?php _e( 'Show as dropdown' ); ?>
				</label>
				<br />
				<label for="categories-count">
					<input type="checkbox" class="checkbox" id="categories-count" name="csd-categories-count" <?php checked( $count, true ); ?> />
					<?php _e( 'Show post counts' ); ?>
				</label>
				<br />
				<label for="categories-hierarchical">
					<input type="checkbox" class="checkbox" id="categories-hierarchical" name="csd-categories-hierarchical" <?php checked( $hierarchical, true ); ?> />
					<?php _e( 'Show hierarchy' ); ?>
				</label>
			</p>
			<input type="hidden" name="csd-categories-submit" value="1" />
<?php
  }

  function display($args) {
		global $wps_this_subdomain;
		
    // $args is an array of strings that help widgets to conform to
    // the active theme: before_widget, before_title, after_widget,
    // and after_title are the array keys. Default tags: li and h2.
		extract($args);

		$options = get_option('csd_widget_categories');

    if ( !is_array($options) )
      $options = array('title'=>'',
      		 'main' => '',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );

		$c = $options['count'] ? '1' : '0';
		$h = $options['hierarchical'] ? '1' : '0';
		$d = $options['dropdown'] ? '1' : '0';

		$titledefault = ($wps_this_subdomain?$wps_this_subdomain->name:'Categories');
		$title = empty($options['title']) ? __($titledefault) : apply_filters('widget_title', $options['title']);
		echo $before_widget;
		echo $before_title . $title . $after_title;


  $cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);
 // $cat_args = array('hide_empty' => 0, 'orderby' => 'name', 'show_count' => $c, 'include' => implode(',', $cats_as_subdomains));

	if ($wps_this_subdomain) : ?>
		<ul>
		<?php 
			$cat_args['title_li'] = '';
			$cat_args['child_of'] = $wps_this_subdomain->id;
			wp_list_categories($cat_args); 
		?>
		</ul>
<?php	else : ?>		<ul>
		<?php 
			$cat_args['title_li'] = ''; wp_list_categories($cat_args); ?>	
		</ul>
<?php	endif;
	     echo $after_widget;  
    }
}

add_action('widgets_init', array('WpsCategoriesWidget','init'));

?>