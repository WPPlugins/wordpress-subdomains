<?php

class WpsSitelistWidget 
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

    $widget = new WpsSitelistWidget();

    // This registers our widget so it appears with the other available
    // widgets and can be dragged and dropped into any active sidebars.
    register_sidebar_widget('WPS Sitelist', array($widget,'display'));

    // This registers our optional widget control form.
    register_widget_control('WPS Sitelist', array($widget,'control'));
  }

  function control() {
    // Get our options and see if we're handling a form submission.
    $options = get_option('csd_widget_sitelist');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
      		 'main' => '',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );
    
    
    if ( !empty($_POST['csd-sitelist-submit']) ) 
    {
    
			$options['title'] = trim(strip_tags(stripslashes($_POST['csd-sitelist-title'])));
			$options['main'] = trim(strip_tags(stripslashes($_POST['csd-sitelist-main'])));
			$options['count'] = isset($_POST['ace-categories-count']);
			//$options['hierarchical'] = isset($_POST['ace-categories-hierarchical']);
			//$options['dropdown'] = isset($_POST['ace-categories-dropdown']);

		  update_option('csd_widget_sitelist', $options);
     }    

		$title = attribute_escape( $options['title'] );
		$main = attribute_escape( $options['main'] );		
		$count = (bool) $options['count'];
		$hierarchical = (bool) $options['hierarchical'];
		$dropdown = (bool) $options['dropdown'];

?>
			<p>
				<label for="sitelist-title">
					<?php _e( 'Title:' ); ?>
					<input class="widefat" id="sitelist-title" name="csd-sitelist-title" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>
			
			<p>
				<label for="sitelist-main">
					<?php _e( 'Site Home:' ); ?>
					<input class="widefat" id="sitelist-main" name="csd-sitelist-main" type="text" value="<?php echo $main; ?>" />
				</label>
			</p>
			<p>
			<!--
				<label for="categories-dropdown">
					<input type="checkbox" class="checkbox" id="categories-dropdown" name="ace-categories-dropdown" <?php checked( $dropdown, true ); ?> />
					<?php _e( 'Show as dropdown' ); ?>
				</label>
				<br />
			-->
				<label for="categories-count">
					<input type="checkbox" class="checkbox" id="categories-count" name="ace-categories-count" <?php checked( $count, true ); ?> />
					<?php _e( 'Show post counts' ); ?>
				</label>
			<!--
				<br />
				<label for="categories-hierarchical">
					<input type="checkbox" class="checkbox" id="categories-hierarchical" name="ace-categories-hierarchical" <?php checked( $hierarchical, true ); ?> />
					<?php _e( 'Show hierarchy' ); ?>
				</label>
			-->
			</p>
			<input type="hidden" name="csd-sitelist-submit" value="1" />
<?php
  }

  function display($args) {
		global $wps_subdomains;

		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		$options = get_option('csd_widget_sitelist');
      
    if ( !is_array($options) )
      $options = array('title'=>'',
      		 'main' => '',
		       'count' => $this->count,
		       'hierarchical' => $this->hierarchical,
		       'dropdown' => $this->dropdown );

	$c = $options['count'] ? '1' : '0';
	/*
	$h = $options['hierarchical'] ? '1' : '0';
	$d = $options['dropdown'] ? '1' : '0';
	*/

	$title = empty($options['title']) ? __(get_bloginfo('name')) : apply_filters('widget_title', $options['title']);
	$main = empty($options['main']) ? __(get_bloginfo('name')) : $options['main'];

	echo $before_widget;
	echo $before_title . $title . $after_title;

  //$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'include' => $cats_to_exclude);
  $cat_args = array('hide_empty' => 0, 'orderby' => 'name', 'show_count' => $c, 'include' => implode(',', $wps_subdomains->getCatIDs()));

?>
		<ul>
		<?php 
			echo '<li><a href="'.get_bloginfo('url').'">'.$main.'</a></li>';
			$cat_args['title_li'] = '';
			wp_list_categories($cat_args); 
		?>
		</ul>
<?php
	     echo $after_widget;  
    }
}

add_action('widgets_init', array('WpsSitelistWidget','init'));

?>