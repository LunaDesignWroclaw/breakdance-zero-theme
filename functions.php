<?php

// Create constants for directory and URI
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

// This is a theme for Breakdance. Check if Breakdance is enabled and if so, load the theme.
require_once THEME_DIR . '/inc/breakdance-init.php';

