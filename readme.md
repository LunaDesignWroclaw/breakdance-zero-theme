# Breakdance Zero Theme (Luna Design)

Motyw bazowy dla stron budowanych w Breakdance. Wydania: tag `vX.Y.Z` w tym repozytorium = nowa wersja w Appsero
(webhook), a strony dostają ją jako zwykłą aktualizację motywu.

## Kod konkretnej strony: `wp-content/luna-strona/` (nie w motywie!)
Nie dopisuj kodu strony do plików motywu: aktualizacja motywu podmienia cały jego katalog. Motyw od wersji 1.4.0 ładuje:
- `wp-content/luna-strona/functions.php` (na końcu, po kodzie motywu),
- akcje formularzy Breakdance z `wp-content/luna-strona/breakdance-form-actions/*.php` (nazwa pliku = nazwa klasy),
- `wp-content/luna-strona/assets/js/main.js` zamiast `main.js` motywu (jeśli istnieje) i moduły z `luna-strona/assets/js/modules/`.

Stałe: `LUNA_STRONA_DIR` (ścieżka) i `LUNA_STRONA_URI` (adres), w JS `themeData.luna_strona_url`.

## Strażnik aktualizacji
`manifest.json` zawiera sumy md5 plików wydania. Jeśli w katalogu motywu na stronie coś zmieniono albo dodano, aktualizacja
jest wstrzymana (komunikat w panelu), żeby nie skasować tego kodu. Przy wydaniu manifest buduje `zbuduj_manifest.py`.
Motyw sam przenosi paczkę z Appsero do swojego katalogu (np. `breakdance-zero-theme-main`), więc aktualizacja trafia na miejsce.

## Wspólne ustawienia w motywie
Limit wielkości przesyłanych zdjęć 12 MB (filtr `luna_zero_limit_zdjec_bajty`), wyłączone ankiety CES w WooCommerce,
akcja formularza „FluentCRM: zapis do newslettera” (lista, tag, double opt-in).

## Privacy Policy

Breakdance Zero Theme uses [Appsero](https://appsero.com) SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.

Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users.

Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

Learn more about how [Appsero collects and uses this data](https://appsero.com/privacy-policy/).