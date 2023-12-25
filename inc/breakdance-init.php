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


add_action('admin_notices', 'warn_if_breakdance_is_disabled');

function warn_if_breakdance_is_disabled()
{
    if (defined('__BREAKDANCE_DIR__')) {
        return;
    }

    ?>
      <div class="notice notice-error is-dismissible">
        <p>You're using Breakdance's Zero Theme but Breakdance is not enabled. This isn't supported.</p>
      </div>
      <?php
}

// Register form actions
if (defined('__BREAKDANCE_DIR__') && defined('BREAKDANCE_FORM_ACTIONS_DIR')) {
    foreach (glob( BREAKDANCE_FORM_ACTIONS_DIR .'/*.php') as $file) {
        require_once $file;
        $class_name = basename($file, '.php');
        $reflection = new ReflectionClass($class_name);
        \Breakdance\Forms\Actions\registerAction($reflection->newInstance());
    }
}

add_action('breakdance_reusable_dependencies_urls', function ($urls) {
    $urls['gsap'] = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.8.0/gsap.min.js';
    error_log(json_encode($urls));
    return $urls;
 });
?>