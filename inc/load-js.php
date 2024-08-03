<?php

// Define THEME_DIR and THEME_URI if not already defined
if (!defined('THEME_DIR')) {
    define('THEME_DIR', get_template_directory());
}

if (!defined('THEME_URI')) {
    define('THEME_URI', get_template_directory_uri());
}

function register_my_modules()
{
    $js_path = THEME_DIR . '/assets/js';
    $js_url = THEME_URI . '/assets/js';

    $module_path = $js_path . '/modules/';
    $module_url = $js_url . '/modules/';

    if (file_exists($module_path)) {
        $module_files = array_diff(scandir($module_path), array('..', '.'));

        foreach ($module_files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                $file_name = pathinfo($file, PATHINFO_FILENAME);
                wp_register_script(
                    $file_name,
                    $module_url . $file,
                    array(), // Dependencies
                    filemtime($module_path . $file), // File version
                    true // Load in footer
                );
            }
        }
    }
}

function my_theme_assets()
{
    $js_path = THEME_DIR . '/assets/js';
    $js_url = THEME_URI . '/assets/js';

    $main_script_path = $js_path . '/main.js';
    $main_script_url = $js_url . '/main.js';

    // Debugging output to check paths
    error_log('$main_script_path: ' . var_export($main_script_path, true));
    error_log('$main_script_url: ' . var_export($main_script_url, true));

    // If the file doesn't exist, end the function
    if (!file_exists($main_script_path)) {
        error_log('File does not exist: ' . $main_script_path);
        return;
    }

    $main_script_name = 'theme-main-js';
    $main_script_version = filemtime($main_script_path);
    $main_script_dependencies = array('removeListeners');

    wp_enqueue_script($main_script_name, $main_script_url, $main_script_dependencies, $main_script_version, true);
    wp_localize_script($main_script_name, 'themeData', array(
        'site_url' => get_site_url(),
        'home_url' => get_home_url(),
        'theme_url' => get_theme_file_uri(),
        'site_name' => get_bloginfo('name'),
        'site_description' => get_bloginfo('description'),
        'site_icon' => get_site_icon_url(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'is_user_logged_in' => is_user_logged_in(),
        'current_user_id' => get_current_user_id(),
        'post_id' => get_the_ID(),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}

add_action('wp_enqueue_scripts', 'register_my_modules');
add_action('wp_enqueue_scripts', 'my_theme_assets');
