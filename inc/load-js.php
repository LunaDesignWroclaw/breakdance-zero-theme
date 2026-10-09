<?php

$js_path = THEME_DIR . '/assets/js'; // Pobierz ścieżkę do katalogu motywu
$js_url = THEME_URI . '/assets/js'; // Pobierz adres URL do katalogu motywu

$main_script_path = $js_path . '/main.js';
$main_script_url = $js_url . '/main.js';

// Kod strony może mieć własny main.js i moduły w wp-content/luna-strona/assets/js/ (mają pierwszeństwo przed motywem).
if (defined('LUNA_STRONA_DIR') && is_readable(LUNA_STRONA_DIR . '/assets/js/main.js')) {
    $main_script_path = LUNA_STRONA_DIR . '/assets/js/main.js';
    $main_script_url = LUNA_STRONA_URI . '/assets/js/main.js';
}

function register_my_modules()
{
    global $js_path, $js_url, $main_script_path;

    $katalogi = array();
    if (defined('LUNA_STRONA_DIR')) {
        $katalogi[] = array(LUNA_STRONA_DIR . '/assets/js/modules/', LUNA_STRONA_URI . '/assets/js/modules/');
    }
    $katalogi[] = array($js_path . '/modules/', $js_url . '/modules/');

    foreach ($katalogi as $katalog) {
        list($module_path, $module_url) = $katalog;
        if (!is_dir($module_path)) {
            continue;
        }
        $module_files = array_diff(scandir($module_path), array('..', '.'));

        foreach ($module_files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                $file_name = pathinfo($file, PATHINFO_FILENAME);
                if (wp_script_is($file_name, 'registered')) {
                    continue; // moduł strony ma pierwszeństwo przed modułem motywu
                }
                wp_register_script(
                    $file_name,
                    $module_url . $file,
                    array(), // Zależności
                    filemtime($module_path . $file), // Wersja pliku
                    true // Umieść w stopce
                );
            }
        }
    }
}

function my_theme_assets()
{
    global $js_path, $js_url, $main_script_path, $main_script_url;
    // If the file doesn't exist end the function
    if (!file_exists($main_script_path)) {
        return;
    }

    $main_script_name = 'theme-main-js';
    $main_script_version = filemtime($main_script_path);
    $main_script_dependencies = array('removeListeners', 'Hyphenopoly_Loader');


    wp_enqueue_script($main_script_name, $main_script_url, $main_script_dependencies, $main_script_version, true);
    wp_localize_script(
        $main_script_name,
        'themeData',
        array(
            'site_url' => get_site_url(),
            'home_url' => get_home_url(),
            'theme_url' => get_theme_file_uri(),
            'luna_strona_url' => defined('LUNA_STRONA_URI') ? LUNA_STRONA_URI : '',
            'site_name' => get_bloginfo('name'),
            'site_description' => get_bloginfo('description'),
            'site_icon' => get_site_icon_url(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'is_user_logged_in' => is_user_logged_in(),
            'current_user_id' => get_current_user_id(),
            'post_id' => get_the_ID(),
            'nonce' => wp_create_nonce('wp_rest'),
        )
    );

}
add_action('wp_enqueue_scripts', 'register_my_modules');
// Show registered modules in the footer
add_action('wp_enqueue_scripts', 'my_theme_assets');
