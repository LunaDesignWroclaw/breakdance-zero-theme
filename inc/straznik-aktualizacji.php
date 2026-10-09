<?php
/**
 * Strażnik aktualizacji Breakdance Zero Theme (Luna Design).
 *
 * 1. Paczka z Appsero ma katalog główny „lunadesign-zero-theme”, a motyw na stronach leży w innym katalogu
 *    (breakdance-zero-theme, -main...). WordPress wgrałby aktualizację OBOK aktywnego motywu. Tu zmieniamy nazwę
 *    katalogu z paczki na katalog aktywnego motywu, więc aktualizacja trafia tam, gdzie trzeba.
 * 2. manifest.json zawiera sumy md5 plików wydania. Jeśli na stronie ktoś zmienił albo dodał pliki w katalogu motywu,
 *    aktualizacja by je skasowała, więc ją blokujemy i pokazujemy komunikat w panelu.
 *    Kod strony trzymaj w wp-content/luna-strona/ (patrz readme.md motywu).
 */

if (!function_exists('luna_zero_zmiany_lokalne')) {
    /**
     * Lista plików motywu zmienionych albo dodanych względem manifest.json (pusta = motyw czysty).
     * @param bool $odswiez pomija pamięć podręczną
     * @return string[]|null null = brak manifestu (nie wiadomo)
     */
    function luna_zero_zmiany_lokalne($odswiez = false)
    {
        $katalog = get_template_directory();
        $manifest = $katalog . '/manifest.json';
        if (!is_readable($manifest)) {
            return null;
        }
        $klucz = 'luna_zero_zmiany_' . md5($katalog);
        if (!$odswiez) {
            $zapis = get_site_transient($klucz);
            if (is_array($zapis)) {
                return $zapis;
            }
        }
        $dane = json_decode((string) file_get_contents($manifest), true);
        $pliki = isset($dane['pliki']) && is_array($dane['pliki']) ? $dane['pliki'] : array();
        $pomijane = array('manifest.json', 'error_log', '.DS_Store', 'Thumbs.db');
        $zmiany = array();
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($katalog, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $plik) {
            if (!$plik->isFile()) {
                continue;
            }
            $wzgledna = ltrim(str_replace('\\', '/', substr($plik->getPathname(), strlen($katalog))), '/');
            if (in_array(basename($wzgledna), $pomijane, true)) {
                continue;
            }
            if (!isset($pliki[$wzgledna])) {
                $zmiany[] = 'dodany: ' . $wzgledna;
            } elseif (md5_file($plik->getPathname()) !== $pliki[$wzgledna]) {
                $zmiany[] = 'zmieniony: ' . $wzgledna;
            }
        }
        sort($zmiany);
        set_site_transient($klucz, $zmiany, HOUR_IN_SECONDS);
        return $zmiany;
    }
}

// 1. Paczka z Appsero rozpakuje się do katalogu aktywnego motywu, nie obok niego.
add_filter('upgrader_source_selection', function ($zrodlo, $zrodlo_zdalne, $upgrader, $extra = array()) {
    if (is_wp_error($zrodlo) || empty($extra['theme']) || $extra['theme'] !== get_template()) {
        return $zrodlo;
    }
    global $wp_filesystem;
    // Paczka musi być pełnym motywem (WordPress przy aktualizacji tego nie sprawdza i pusta paczka skasowałaby motyw).
    $styl = trailingslashit($zrodlo) . 'style.css';
    $naglowek = file_exists($styl) ? get_file_data($styl, array('nazwa' => 'Theme Name')) : array('nazwa' => '');
    if ($naglowek['nazwa'] !== 'Breakdance Zero Theme' || !file_exists(trailingslashit($zrodlo) . 'functions.php')) {
        return new WP_Error('luna_zero_paczka', 'Paczka aktualizacji nie wygląda na Breakdance Zero Theme (brak style.css albo functions.php). Aktualizacja przerwana.');
    }
    $cel = trailingslashit(dirname(untrailingslashit($zrodlo))) . get_template() . '/';
    if (untrailingslashit($zrodlo) === untrailingslashit($cel) || !$wp_filesystem) {
        return $zrodlo;
    }
    if ($wp_filesystem->move(untrailingslashit($zrodlo), untrailingslashit($cel), true)) {
        return $cel;
    }
    return new WP_Error('luna_zero_nazwa', 'Nie udało się przygotować paczki Breakdance Zero Theme (zmiana nazwy katalogu).');
}, 10, 4);

// 2a. Nie pokazuj aktualizacji, gdy motyw ma lokalne zmiany.
add_filter('site_transient_update_themes', function ($dane) {
    if (!is_object($dane) || empty($dane->response) || !is_array($dane->response)) {
        return $dane;
    }
    $szablon = get_template();
    if (!isset($dane->response[$szablon])) {
        return $dane;
    }
    $zmiany = luna_zero_zmiany_lokalne(true); // aktualizacja dostępna: licz na świeżo, nie z pamięci podręcznej
    if (!empty($zmiany)) {
        $wersja = is_array($dane->response[$szablon]) ? ($dane->response[$szablon]['new_version'] ?? '') : '';
        update_site_option('luna_zero_aktualizacja_zablokowana', array('wersja' => $wersja, 'zmiany' => array_slice($zmiany, 0, 30), 'czas' => time()));
        $dane->no_update[$szablon] = $dane->response[$szablon];
        unset($dane->response[$szablon]);
    }
    return $dane;
}, PHP_INT_MAX);

// 2b. Na wszelki wypadek blokada w samym instalatorze (np. aktualizacja wymuszona z MainWP albo WP-CLI).
// Priorytet PHP_INT_MAX: inne wtyczki potrafią zwracać tu null i zgubić wcześniejszy błąd.
add_filter('upgrader_pre_install', function ($wynik, $extra = array()) {
    if (is_wp_error($wynik) || empty($extra['theme']) || $extra['theme'] !== get_template()) {
        return $wynik;
    }
    $zmiany = luna_zero_zmiany_lokalne(true);
    if (!empty($zmiany)) {
        return new WP_Error('luna_zero_zmiany', 'Aktualizacja Breakdance Zero Theme zablokowana: w motywie są lokalne zmiany (' .
            implode(', ', array_slice($zmiany, 0, 5)) . (count($zmiany) > 5 ? ' ...' : '') .
            '). Przenieś kod strony do wp-content/luna-strona/.');
    }
    return $wynik;
}, PHP_INT_MAX, 2);

// 2c. Bez automatycznych aktualizacji motywu, gdy ma lokalne zmiany.
add_filter('auto_update_theme', function ($aktualizuj, $element = null) {
    if (is_object($element) && isset($element->theme) && $element->theme === get_template() && !empty(luna_zero_zmiany_lokalne())) {
        return false;
    }
    return $aktualizuj;
}, 999, 2);

// 3. Komunikat w panelu dla administratorów.
add_action('admin_notices', function () {
    if (!current_user_can('update_themes')) {
        return;
    }
    $ekran = function_exists('get_current_screen') ? get_current_screen() : null;
    if ($ekran && !in_array($ekran->id, array('themes', 'update-core', 'dashboard'), true)) {
        return;
    }
    $blokada = get_site_option('luna_zero_aktualizacja_zablokowana');
    $zmiany = luna_zero_zmiany_lokalne();
    if (empty($blokada) || empty($zmiany)) {
        return;
    }
    echo '<div class="notice notice-warning"><p><strong>Breakdance Zero Theme:</strong> aktualizacja do wersji ' .
        esc_html($blokada['wersja']) . ' jest wstrzymana, bo w plikach motywu są zmiany zrobione na tej stronie ' .
        '(aktualizacja by je skasowała). Kod strony przenieś do <code>wp-content/luna-strona/</code>.</p><p><small>' .
        esc_html(implode(', ', array_slice($zmiany, 0, 10))) . (count($zmiany) > 10 ? ' ...' : '') . '</small></p></div>';
});
