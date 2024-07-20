<?php

// Create constants for directory and URI
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

// Breakdance form actions directory
define('BREAKDANCE_FORM_ACTIONS_DIR', THEME_DIR . '/inc/breakdance-form-actions');

// This is a theme for Breakdance. Check if Breakdance is enabled and if so, load the theme.
require_once THEME_DIR . '/inc/breakdance-init.php';

// Check for updates
require_once THEME_DIR . '/inc/check-updates.php';

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