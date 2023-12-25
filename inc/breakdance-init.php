<?php 
if (!function_exists('breakdance_zero_theme_setup')) {
    function breakdance_zero_theme_setup()
    {
      add_theme_support('title-tag');
      add_theme_support('post-thumbnails');
  
    }
  
    function breakdance_zero_theme_enqueue_scripts()
    {
      if (defined('__BREAKDANCE_DIR__')) {
        wp_enqueue_style('breakdance-normalize', plugins_url() . '/breakdance/plugin/themeless/normalize.min.css');
      }
    }
  
    add_action('wp_enqueue_scripts', 'breakdance_zero_theme_enqueue_scripts');
  }
  
  add_action('after_setup_theme', 'breakdance_zero_theme_setup');
  
  
  add_action( 'admin_notices', 'warn_if_breakdance_is_disabled' );
  
  function warn_if_breakdance_is_disabled() {
    if (defined('__BREAKDANCE_DIR__')){
      return;
    }
  
  ?>
  <div class="notice notice-error is-dismissible">
    <p>You're using Breakdance's Zero Theme but Breakdance is not enabled. This isn't supported.</p>
  </div>
  <?php
  }
?>