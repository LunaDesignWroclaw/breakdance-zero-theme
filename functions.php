<?php
// Path: 12qqe3

// Create constants for directory and URI
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

// Function to safely require files
function require_file_if_exists($file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Hook the setup functions into 'after_setup_theme'
add_action('after_setup_theme', 'theme_setup');
function theme_setup() {
    // Breakdance form actions directory
    define('BREAKDANCE_FORM_ACTIONS_DIR', THEME_DIR . '/inc/breakdance-form-actions');

    // This is a theme for Breakdance. Check if Breakdance is enabled and if so, load the theme.
    require_file_if_exists(THEME_DIR . '/inc/breakdance-init.php');

    // Safe redirect after logout
    require_file_if_exists(THEME_DIR . '/inc/safe-logout.php');

    // Load main javascript file and pass 
    require_file_if_exists(THEME_DIR . '/inc/load-js.php');

    // Load dividers
    require_file_if_exists(THEME_DIR . '/inc/dividers.php');

    // Load reusable dependencies
    require_file_if_exists(THEME_DIR . '/inc/reusable-dependencies.php');

    // Load design library
    require_file_if_exists(THEME_DIR . '/inc/design-library.php');
}

// Load scripts and styles
add_action('wp_enqueue_scripts', 'enqueue_theme_scripts');
function enqueue_theme_scripts() {
    wp_enqueue_script('main-js', THEME_URI . '/js/main.js', [], false, true);
}

// Check for theme updates
add_filter('site_transient_update_themes', 'breakdance_zero_theme_update_themes');

function breakdance_zero_theme_update_themes($transient) {
    // Ensure $transient is an object
    if (!is_object($transient)) {
        $transient = new stdClass();
        $transient->response = array();
        $transient->no_update = array();
    }

    // Let's get the theme directory name
    $stylesheet = get_stylesheet(); // Use get_stylesheet() instead of get_template() to ensure child themes are correctly handled

    // Now let's get the theme version
    $theme = wp_get_theme($stylesheet);
    $version = $theme->get('Version');

    // Check if we have a cached response or if force-check is triggered
    $force_check = (defined('DOING_CRON') && DOING_CRON) || (isset($_GET['force-check']) && $_GET['force-check'] == 1);
    $remote = get_transient('breakdance-zero-theme-update-' . $stylesheet);

    if ($force_check || !$remote || !is_object($remote)) {
        $response = wp_remote_get('https://breakdance-zero-theme.cytr.us/info.php', array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));

        if (
            is_wp_error($response)
            || 200 !== wp_remote_retrieve_response_code($response)
            || empty(wp_remote_retrieve_body($response))
        ) {
            return $transient;
        }

        $remote = json_decode(wp_remote_retrieve_body($response));

        if (!$remote) {
            return $transient; // Who knows, maybe JSON is not valid
        }

        set_transient('breakdance-zero-theme-update-' . $stylesheet, $remote, HOUR_IN_SECONDS);
    }

    if (is_object($remote)) {
        $data = array(
            'theme' => $remote->slug ?? $stylesheet, // Use slug from remote if available
            'url' => $remote->details_url ?? '',
            'requires' => $remote->requires ?? '',
            'requires_php' => $remote->requires_php ?? '',
            'new_version' => $remote->version ?? '',
            'package' => $remote->download_url ?? '',
        );

        if (
            version_compare($version, $remote->version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<=')
            && version_compare($remote->requires_php, PHP_VERSION, '<=')
        ) {
            $transient->response[$stylesheet] = $data;
        } else {
            $transient->no_update[$stylesheet] = $data;
        }
    }

    return $transient;
}
