<?php

if (!function_exists('\Breakdance\Forms\Actions\registerAction') || !class_exists('\Breakdance\Forms\Actions\Action')) {
    die;
}

class ExampleAction extends Breakdance\Forms\Actions\Action
{

    /**
     * @return string
     */
    public static function name()
    {
        return 'Akcja przykładowa (wymaga pola "name")';
    }


    /**
     * @return string
     */
    public static function slug()
    {
        return 'example-action';
    }

    /**
     * @param array $form
     * @param array $settings
     * @param array $extra
     * @return array success or error message
     */
    public function run($form, $settings, $extra)
    {
        // Pobierz wartości pól z formularza
        [
            'name' => $name,
        ] = $extra['fields'];

        // Utwórz tablicę na błędy
        $errors = [];

        // Sprawdź pola i dodaj błędy do tablicy $errors
        if (empty($name)) {
            $errors[] = 'Nie uzupełniono pola "name".';
        }

       // Jeśli tablica $errors nie jest pusta to zwróć tablicę z błędami
        if (count($errors) > 0) {
            return ['type' => 'error', 'message' => implode('<br>', $errors)];
        }

        return ['type' => 'success', 'message' => 'Akcja przykładowa wykonana.'];
    }
}


?>