<?php

require __DIR__ . '/vendor/autoload.php';

// Create constants for directory and URI
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

// Breakdance form actions directory
define('BREAKDANCE_FORM_ACTIONS_DIR', THEME_DIR . '/inc/breakdance-form-actions');


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_lunadesign_zero_theme() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( '5b83a102-67ce-48ed-ac5f-3cf447f0c370', 'Breakdance Zero Theme', __FILE__ );

    // Active insights
    $client->insights()->init();

    $client->updater();

}

appsero_init_tracker_lunadesign_zero_theme();

// This is a theme for Breakdance. Check if Breakdance is enabled and if so, load the theme.
require_once THEME_DIR . '/inc/breakdance-init.php';

// Safe redirect after logout
require_once THEME_DIR . '/inc/safe-logout.php';

// Load main javascript file and pass data to it
require_once THEME_DIR . '/inc/load-js.php';

// Load dividers
require_once THEME_DIR . '/inc/dividers.php';

// Load reusable dependencies
require_once THEME_DIR . '/inc/reusable-dependencies.php';

// Load design library
require_once THEME_DIR . '/inc/design-library.php';