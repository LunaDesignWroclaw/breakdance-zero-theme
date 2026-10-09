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

// Register form actions: z motywu (inc/breakdance-form-actions) i z kodu strony (wp-content/luna-strona/breakdance-form-actions).
// Nazwa pliku = nazwa klasy akcji.
if (defined('__BREAKDANCE_DIR__') && defined('BREAKDANCE_FORM_ACTIONS_DIR')) {
    $luna_katalogi_akcji = array(BREAKDANCE_FORM_ACTIONS_DIR);
    if (defined('LUNA_STRONA_DIR')) {
        $luna_katalogi_akcji[] = LUNA_STRONA_DIR . '/breakdance-form-actions';
    }
    foreach ($luna_katalogi_akcji as $luna_katalog) {
        foreach ((array) glob($luna_katalog . '/*.php') as $file) {
            $class_name = basename($file, '.php');
            if (class_exists($class_name, false)) {
                continue; // ta sama akcja już wczytana (np. z motywu)
            }
            try {
                require_once $file;
                if (!class_exists($class_name, false)) {
                    continue;
                }
                $reflection = new ReflectionClass($class_name);
                \Breakdance\Forms\Actions\registerAction($reflection->newInstance());
            } catch (Throwable $e) {
                error_log('[Zero Theme] Nie udało się wczytać akcji formularza ' . $class_name . ': ' . $e->getMessage());
            }
        }
    }
    unset($luna_katalogi_akcji, $luna_katalog);
}

add_action('breakdance_reusable_dependencies_urls', function ($urls) {
    $urls['gsap'] = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.8.0/gsap.min.js';
    return $urls;
});
