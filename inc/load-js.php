<?php

function my_theme_assets()
{

    $main_script_path = THEME_DIR . '/assets/js/main.js';
    // If the file doesn't exist end the function
    if (!file_exists($main_script_path)) {
        return;
    }
    $file_version = filemtime($main_script_path);

    wp_enqueue_script('theme-main-js', get_theme_file_uri('/assets/js/main.js'), array(), $file_version, true);
    wp_localize_script('theme-main-js', 'themeData', array(
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

add_action('wp_enqueue_scripts', 'my_theme_assets');