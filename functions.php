<?php

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
    }

    // Let's get the theme directory name
    $stylesheet = get_template();

    // Now let's get the theme version
    $theme = wp_get_theme();
    $version = $theme->get('Version');

    // Check if we have a cached response
    if (false == $remote = get_transient('breakdance-zero-theme-update-' . $version)) {
        // Connect to a remote server where the update information is stored
        $remote = wp_remote_get(
            'https://breakdance-zero-theme.cytr.us/info.php',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );

        // Do nothing if errors
        if (
            is_wp_error($remote)
            || 200 !== wp_remote_retrieve_response_code($remote)
            || empty(wp_remote_retrieve_body($remote))
        ) {
            return $transient;
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        if (!$remote) {
            return $transient; // Who knows, maybe JSON is not valid
        }

        // Cache the response for 1 hour
        set_transient('breakdance-zero-theme-update-' . $version, $remote, HOUR_IN_SECONDS);
    }

    $data = array(
        'theme' => $stylesheet,
        'url' => $remote->details_url,
        'requires' => $remote->requires,
        'requires_php' => $remote->requires_php,
        'new_version' => $remote->version,
        'package' => $remote->download_url,
    );

    // Check all the versions now
    if (
        $remote
        && version_compare($version, $remote->version, '<')
        && version_compare($remote->requires, get_bloginfo('version'), '<')
        && version_compare($remote->requires_php, PHP_VERSION, '<')
    ) {
        $transient->response[$stylesheet] = $data;
    } else {
        $transient->no_update[$stylesheet] = $data;
    }

    return $transient;
}
?>
