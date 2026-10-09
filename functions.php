<?php
require __DIR__ . '/vendor/autoload.php';

// Create constants for directory and URI
define('THEME_DIR', get_template_directory());
define('THEME_URI', get_template_directory_uri());

// Kod konkretnej strony trzymamy POZA motywem, w wp-content/luna-strona/ (aktualizacje motywu go nie ruszają).
// Nie dopisuj kodu strony do plików motywu: zobacz readme.md.
if (!defined('LUNA_STRONA_DIR')) {
  define('LUNA_STRONA_DIR', WP_CONTENT_DIR . '/luna-strona');
}
if (!defined('LUNA_STRONA_URI')) {
  define('LUNA_STRONA_URI', content_url('/luna-strona'));
}

function set_database_encoding()
{
  global $wpdb;
  $wpdb->query("SET NAMES 'utf8mb4'");
  $wpdb->query("SET CHARACTER SET utf8mb4");
}
add_action('init', 'set_database_encoding');


// Breakdance form actions directory
define('BREAKDANCE_FORM_ACTIONS_DIR', THEME_DIR . '/inc/breakdance-form-actions');


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_lunadesign_zero_theme()
{

  if (!class_exists('Appsero\Client')) {
    require_once __DIR__ . '/appsero/src/Client.php';
  }

  $client = new Appsero\Client('5b83a102-67ce-48ed-ac5f-3cf447f0c370', 'Breakdance Zero Theme', __FILE__);

  // Active insights
  $client->insights()->init();

  // Active automatic updater
  try {
      $client->updater();
  } catch (Exception $e) {
      // Handle the exception here, e.g. log an error message or display a friendly error to the user
  }
}

appsero_init_tracker_lunadesign_zero_theme();

// Strażnik aktualizacji: blokuje aktualizację motywu, gdy na stronie zmieniono jego pliki (kod strony zostałby skasowany).
require_once THEME_DIR . '/inc/straznik-aktualizacji.php';

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

// Wspólne ustawienia stron Luna Design (limit zdjęć, WooCommerce)
require_once THEME_DIR . '/inc/wspolne.php';

// Kod konkretnej strony: wp-content/luna-strona/functions.php (ładowany na końcu, jak dawniej kod dopisany na końcu tego pliku)
if (is_readable(LUNA_STRONA_DIR . '/functions.php')) {
  require_once LUNA_STRONA_DIR . '/functions.php';
}
