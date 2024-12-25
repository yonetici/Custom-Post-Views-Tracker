<?php  
/**  
 * Plugin Name: Custom Post Views Tracker  
 * Plugin URI: https://oneirly.com  
 * Description: Tracks and displays post views with customizable widget options.  
 * Version: 1.0  
 * Author: Oneirly.com  
 * Author URI: https://oneirly.com  
 * License: GPL v3 or later  
 */  
  
// Prevent direct access to this file  
if (!defined('ABSPATH')) {  
   exit;  
}  
  
// Track post views  
function cpvt_track_post_views() {  
   if (!is_single() && !is_page()) {  
      return;  
   }  
  
   $post_id = get_the_ID();  
   if (!$post_id) {  
      return;  
   }  
  
   if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])) {  
      return;  
   }  
  
   $count = (int)get_post_meta($post_id, 'post_views_count', true);  
   update_post_meta($post_id, 'post_views_count', ++$count);  
}  
add_action('wp', 'cpvt_track_post_views');  
  
// Display post views after content  
function cpvt_display_views($content) {  
   if (is_single() || is_page()) {  
      $post_id = get_the_ID();  
      $count = (int)get_post_meta($post_id, 'post_views_count', true);  
      $views = sprintf(  
        '<div class="post-views">%s: %s</div>',  
        esc_html__('Views', 'cpvt'),  
        number_format_i18n($count)  
      );  
      $content .= $views;  
   }  
   return $content;  
}  
add_filter('the_content', 'cpvt_display_views');  
  
function cpvt_add_admin_menu() {  
   add_menu_page(  
      'Post Views Tracker Settings', // Page title  
      'Post Views', // Menu title  
      'manage_options', // Capability  
      'cpvt-settings', // Menu slug  
      'cpvt_settings_page', // Function to display the page  
      'dashicons-chart-bar', // Icon  
      30 // Position  
   );  
}  
add_action('admin_menu', 'cpvt_add_admin_menu');    
  
// Admin Settings Page  
function cpvt_settings_page() {  
   ?>  
   <div class="wrap">  
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>  
      <form method="post" action="options.php">  
        <?php  
        settings_fields('cpvt_options');  
        do_settings_sections('cpvt-settings');  
        submit_button();  
        ?>  
      </form>  
   </div>  
   <?php  
}  
  
// Register Settings  
function cpvt_register_settings() {  
   register_setting('cpvt_options', 'cpvt_settings');  
    
   add_settings_section(  
      'cpvt_general_section',  
      'General Settings',  
      'cpvt_general_section_callback',  
      'cpvt-settings'  
   );  
    
   add_settings_field(  
      'display_location',  
      'Display Location',  
      'cpvt_display_location_callback',  
      'cpvt-settings',  
      'cpvt_general_section'  
   );  
}  
add_action('admin_init', 'cpvt_register_settings');  
  
function cpvt_general_section_callback() {  
   echo '<p>Configure how post views are displayed on your site.</p>';  
}  
  
function cpvt_display_location_callback() {  
   $options = get_option('cpvt_settings');  
   ?>  
   <select name="cpvt_settings[display_location]">  
      <option value="after_content" <?php selected($options['display_location'] ?? 'after_content', 'after_content'); ?>>After Content</option>  
      <option value="before_content" <?php selected($options['display_location'] ?? 'after_content', 'before_content'); ?>>Before Content</option>  
   </select>  
   <?php  
}


class CPVT_Widget extends WP_Widget {  
   public function __construct() {  
      parent::__construct(  
        'cpvt_widget', // Base ID  
        __('Popular Posts Tracker', 'cpvt'), // Widget name in admin  
        array(  
           'description' => __('Display popular posts based on views', 'cpvt'),  
           'classname' => 'cpvt-widget'  
        )  
      );  
   }  
  
  
   public function widget($args, $instance) {  
      echo $args['before_widget'];  
  
      $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';  
      $time_range = !empty($instance['time_range']) ? $instance['time_range'] : 'daily';  
      $category = !empty($instance['category']) ? (int)$instance['category'] : 0;  
      $display_options = !empty($instance['display_options']) ? (array)$instance['display_options'] : array('title');  
      $posts_count = !empty($instance['posts_count']) ? (int)$instance['posts_count'] : 5;  
  
      if ($title) {  
        echo $args['before_title'] . esc_html($title) . $args['after_title'];  
      }  
  
      $date_query = array();  
      switch ($time_range) {  
        case 'daily':  
           $date_query = array('after' => '1 day ago');  
           break;  
        case 'weekly':  
           $date_query = array('after' => '1 week ago');  
           break;  
        case 'monthly':  
           $date_query = array('after' => '1 month ago');  
           break;  
      }  
  
      $query_args = array(  
        'post_type' => 'post',  
        'posts_per_page' => $posts_count,  
        'meta_key' => 'post_views_count',  
        'orderby' => 'meta_value_num',  
        'order' => 'DESC',  
        'date_query' => array($date_query),  
        'no_found_rows' => true,  
        'update_post_term_cache' => false  
      );  
  
      if ($category > 0) {  
        $query_args['cat'] = $category;  
      }  
  
      $popular_posts = new WP_Query($query_args);  
  
      if ($popular_posts->have_posts()) {  
        echo '<ul class="popular-posts-list">';  
        while ($popular_posts->have_posts()) {  
           $popular_posts->the_post();  
           $this->render_post_item($display_options);  
        }  
        echo '</ul>';  
      }  
  
      wp_reset_postdata();  
      echo $args['after_widget'];  
   }  
  
   private function render_post_item($display_options) {  
      echo '<li class="popular-post-item">';  
  
      if (in_array('thumbnail', $display_options) && has_post_thumbnail()) {  
        echo '<div class="post-thumbnail">';  
        echo '<a href="' . esc_url(get_permalink()) . '">';  
        the_post_thumbnail('thumbnail');  
        echo '</a></div>';  
      }  
  
      if (in_array('title', $display_options)) {  
        echo '<h4 class="post-title">';  
        echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';  
        echo '</h4>';  
      }  
  
      if (in_array('date', $display_options)) {  
        echo '<span class="post-date">' . esc_html(get_the_date()) . '</span>';  
      }  
  
      if (in_array('category', $display_options)) {  
        echo '<span class="post-category">' . get_the_category_list(', ') . '</span>';  
      }  
  
      if (in_array('excerpt', $display_options)) {  
        echo '<div class="post-excerpt">' . wp_kses_post(get_the_excerpt()) . '</div>';  
      }  
  
      if (in_array('views', $display_options)) {  
        $views = get_post_meta(get_the_ID(), 'post_views_count', true);  
        echo '<span class="post-views">' . sprintf(  
           __('Views: %s', 'cpvt'),  
           number_format_i18n($views)  
        ) . '</span>';  
      }  
  
      echo '</li>';  
   }  
  
   public function form($instance) {  
      $title = isset($instance['title']) ? $instance['title'] : __('Popular Posts', 'cpvt');  
      $time_range = isset($instance['time_range']) ? $instance['time_range'] : 'daily';  
      $category = isset($instance['category']) ? $instance['category'] : '';  
      $display_options = isset($instance['display_options']) ? $instance['display_options'] : array('title', 'thumbnail');  
      $posts_count = isset($instance['posts_count']) ? absint($instance['posts_count']) : 5;  
  
      ?>  
      <p>  
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cpvt'); ?></label>  
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"  
             name="<?php echo $this->get_field_name('title'); ?>" type="text"  
             value="<?php echo esc_attr($title); ?>">  
      </p>  
      <p>  
        <label for="<?php echo $this->get_field_id('time_range'); ?>"><?php _e('Time Range:', 'cpvt'); ?></label>  
        <select class="widefat" id="<?php echo $this->get_field_id('time_range'); ?>"  
              name="<?php echo $this->get_field_name('time_range'); ?>">  
           <option value="daily" <?php selected($time_range, 'daily'); ?>><?php _e('Daily', 'cpvt'); ?></option>  
           <option value="weekly" <?php selected($time_range, 'weekly'); ?>><?php _e('Weekly', 'cpvt'); ?></option>  
           <option value="monthly" <?php selected($time_range, 'monthly'); ?>><?php _e('Monthly', 'cpvt'); ?></option>  
        </select>  
      </p>  
      <p>  
        <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'cpvt'); ?></label>  
        <?php wp_dropdown_categories(array(  
           'show_option_all' => __('All Categories', 'cpvt'),  
           'name' => $this->get_field_name('category'),  
           'selected' => $category  
        )); ?>  
      </p>  
      <p>  
        <label for="<?php echo $this->get_field_id('posts_count'); ?>"><?php _e('Number of posts to show:', 'cpvt'); ?></label>  
        <input class="tiny-text" id="<?php echo $this->get_field_id('posts_count'); ?>"  
             name="<?php echo $this->get_field_name('posts_count'); ?>" type="number"  
             step="1" min="1" value="<?php echo esc_attr($posts_count); ?>" size="3">  
      </p>  
      <p>  
        <label><?php _e('Display Options:', 'cpvt'); ?></label><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="thumbnail" <?php checked(in_array('thumbnail', $display_options)); ?>> <?php _e('Thumbnail', 'cpvt'); ?><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="title" <?php checked(in_array('title', $display_options)); ?>> <?php _e('Title', 'cpvt'); ?><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="date" <?php checked(in_array('date', $display_options)); ?>> <?php _e('Date', 'cpvt'); ?><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="category" <?php checked(in_array('category', $display_options)); ?>> <?php _e('Category', 'cpvt'); ?><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="excerpt" <?php checked(in_array('excerpt', $display_options)); ?>> <?php _e('Excerpt', 'cpvt'); ?><br>  
        <input type="checkbox" name="<?php echo $this->get_field_name('display_options'); ?>[]"  
             value="views" <?php checked(in_array('views', $display_options)); ?>> <?php _e('Views Count', 'cpvt'); ?>  
      </p>  
      <?php  
   }  
  
   public function update($new_instance, $old_instance) {  
      $instance = array();  
      $instance['title'] = sanitize_text_field($new_instance['title']);  
      $instance['time_range'] = sanitize_text_field($new_instance['time_range']);  
      $instance['category'] = absint($new_instance['category']);  
      $instance['display_options'] = isset($new_instance['display_options']) ?  
        array_map('sanitize_text_field', $new_instance['display_options']) : array();  
      $instance['posts_count'] = absint($new_instance['posts_count']);  
      return $instance;  
   }  
}  
  
// Register Widget  
function register_cpvt_widget() {  
   register_widget('CPVT_Widget');  
}  
add_action('widgets_init', 'register_cpvt_widget');  
  
// Enqueue Styles  
function cpvt_enqueue_styles() {  
   wp_enqueue_style(  
      'cpvt-styles',  
      plugins_url('css/style.css', __FILE__),  
      array(),  
      '1.0'  
   );  
}  
add_action('wp_enqueue_scripts', 'cpvt_enqueue_styles');  
  
// Activation Hook  
function cpvt_activate() {  
   // Activation tasks if needed  
}  
register_activation_hook(__FILE__, 'cpvt_activate');  
  
// Deactivation Hook  
function cpvt_deactivate() {  
   // Cleanup tasks if needed  
}  
register_deactivation_hook(__FILE__, 'cpvt_deactivate');
