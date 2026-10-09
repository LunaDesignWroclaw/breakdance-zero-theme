<?php
/**
 * Akcja formularza Breakdance: zapis kontaktu do FluentCRM (lista + opcjonalnie tag, double opt-in).
 * Zastępuje kod „Breakdance → FluentCRM” dopisywany wcześniej do functions.php na stronach, który nie działał
 * (rejestrował się przez nieistniejący w Breakdance interfejs ActionInterface i filtry breakdance_form_actions_register).
 */

use function Breakdance\Elements\control;

if (!class_exists('\Breakdance\Forms\Actions\Action')) {
    return;
}

class FluentCrmNewsletter extends Breakdance\Forms\Actions\Action
{
    /**
     * @return string
     */
    public static function name()
    {
        return 'FluentCRM: zapis do newslettera';
    }

    /**
     * @return string
     */
    public static function slug()
    {
        return 'luna-fluentcrm-newsletter';
    }

    /**
     * @return array
     */
    public function controls()
    {
        $pola = [
            'enabled' => true,
            'populate' => [
                'path' => 'content.form.fields',
                'text' => 'label',
                'value' => 'advanced.id',
            ],
        ];
        return [
            control('email_field', 'ID pola z adresem e-mail (puste = pierwsze pole z adresem)', ['type' => 'text', 'layout' => 'vertical', 'variableOptions' => $pola]),
            control('name_field', 'ID pola z imieniem (opcjonalnie)', ['type' => 'text', 'layout' => 'vertical', 'variableOptions' => $pola]),
            control('list_name', 'Lista FluentCRM (nazwa, zostanie utworzona, jeśli nie istnieje)', ['type' => 'text', 'layout' => 'vertical']),
            control('tag_name', 'Tag FluentCRM (opcjonalnie)', ['type' => 'text', 'layout' => 'vertical']),
            control('double_optin', 'Double opt-in (FluentCRM wyśle mail z potwierdzeniem)', ['type' => 'toggle', 'layout' => 'inline']),
        ];
    }

    /**
     * @param array $form
     * @param array $settings
     * @param array $extra
     * @return array
     */
    public function run($form, $settings, $extra)
    {
        if (!function_exists('FluentCrmApi')) {
            return ['type' => 'error', 'message' => 'FluentCRM nie jest aktywny na tej stronie.'];
        }

        $ustawienia = $settings['actions'][self::slug()] ?? [];
        $pola = isset($extra['fields']) && is_array($extra['fields']) ? $extra['fields'] : [];

        $email = '';
        $poleEmail = $this->idPola($ustawienia['email_field'] ?? '');
        if ($poleEmail !== '' && isset($pola[$poleEmail])) {
            $email = $this->wartosc($pola[$poleEmail]);
        }
        if (!is_email($email)) {
            foreach ($pola as $wartosc) {
                $w = $this->wartosc($wartosc);
                if (is_email($w)) {
                    $email = $w;
                    break;
                }
            }
        }
        $email = sanitize_email($email);
        if (!is_email($email)) {
            return ['type' => 'error', 'message' => 'Podaj poprawny adres e-mail.'];
        }

        $dane = ['email' => $email];
        $poleImie = $this->idPola($ustawienia['name_field'] ?? '');
        if ($poleImie !== '' && !empty($pola[$poleImie])) {
            $dane['first_name'] = sanitize_text_field($this->wartosc($pola[$poleImie]));
        }

        try {
            $nazwaListy = trim((string) ($ustawienia['list_name'] ?? '')) ?: 'newsletter';
            $lista = \FluentCrm\App\Models\Lists::where('title', $nazwaListy)->first();
            if (!$lista) {
                $lista = \FluentCrm\App\Models\Lists::create(['title' => $nazwaListy, 'slug' => sanitize_title($nazwaListy)]);
            }
            $dane['lists'] = [(int) $lista->id];

            $nazwaTagu = trim((string) ($ustawienia['tag_name'] ?? ''));
            if ($nazwaTagu !== '') {
                $tag = \FluentCrm\App\Models\Tag::where('title', $nazwaTagu)->first();
                if (!$tag) {
                    $tag = \FluentCrm\App\Models\Tag::create(['title' => $nazwaTagu, 'slug' => sanitize_title($nazwaTagu)]);
                }
                $dane['tags'] = [(int) $tag->id];
            }

            $doubleOptin = !empty($ustawienia['double_optin']);
            $kontakty = FluentCrmApi('contacts');
            $istniejacy = $kontakty->getContact($email);
            if (!$istniejacy || $istniejacy->status !== 'subscribed') {
                $dane['status'] = $doubleOptin ? 'pending' : 'subscribed';
            }
            $kontakt = $kontakty->createOrUpdate($dane);
            if ($kontakt && $doubleOptin && $kontakt->status === 'pending' && method_exists($kontakt, 'sendDoubleOptinEmail')) {
                $kontakt->sendDoubleOptinEmail();
            }
        } catch (\Throwable $e) {
            error_log('[Zero Theme / FluentCRM] ' . $e->getMessage());
            return ['type' => 'error', 'message' => 'Nie udało się zapisać do newslettera. Spróbuj ponownie później.'];
        }

        return ['type' => 'success', 'message' => 'Zapisano do newslettera.'];
    }

    /**
     * Pole może być podane jako ID albo jako zmienna {ID}.
     * @param string $id
     * @return string
     */
    private function idPola($id)
    {
        return trim(str_replace(['{', '}'], '', (string) $id));
    }

    /**
     * @param mixed $wartosc
     * @return string
     */
    private function wartosc($wartosc)
    {
        if (is_array($wartosc)) {
            $wartosc = reset($wartosc);
        }
        return is_scalar($wartosc) ? trim((string) $wartosc) : '';
    }
}
