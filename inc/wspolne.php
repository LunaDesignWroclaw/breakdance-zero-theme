<?php
/**
 * Wspólne ustawienia stron Luna Design, wcześniej dopisywane ręcznie do functions.php na wielu stronach.
 * Nazwy funkcji z przedrostkiem luna_zero_, żeby nie zderzały się ze starymi kopiami tego kodu w wp-content/luna-strona/.
 */

// Limit wielkości przesyłanych zdjęć (jpg, jpeg, png, gif): 12 MB.
if (!function_exists('luna_zero_limit_zdjec')) {
  function luna_zero_limit_zdjec($file)
  {
    $formaty = array('jpg', 'jpeg', 'png', 'gif');
    $rozszerzenie = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $limit = (int) apply_filters('luna_zero_limit_zdjec_bajty', 12 * 1024 * 1024);
    if (in_array($rozszerzenie, $formaty, true) && $file['size'] > $limit) {
      $file['error'] = sprintf('Maksymalna waga pliku może wynosić %d MB.', (int) round($limit / 1048576));
    }
    return $file;
  }
}
add_filter('wp_handle_upload_prefilter', 'luna_zero_limit_zdjec');

// WooCommerce: wyłącza Customer Effort Score (ankiety i okienka w panelu).
add_filter('woocommerce_admin_features', function ($features) {
  return is_array($features) ? array_values(array_diff($features, array('ces'))) : $features;
});

// WooCommerce: kolejka CES musi być tablicą, inaczej array_filter() w WooCommerce dostaje bool i sypie ostrzeżeniami.
add_action('admin_init', function () {
  if (!class_exists('WooCommerce')) {
    return;
  }
  $kolejka = get_option('woocommerce_ces_tracks_queue', array());
  if (!is_array($kolejka)) {
    update_option('woocommerce_ces_tracks_queue', array(), false);
  }
});
