// Przykład odwołania się do funkcji z modułu

document.querySelectorAll('.breakdance-form-field').forEach(parent => {
    // Szukanie pierwszego elementu formularza (input lub select) wewnątrz elementu rodzica
    const formElement = parent.querySelector('input, select');

    if (formElement && formElement.name) {
        // Wyciągnięcie klucza z wartości atrybutu 'name'
        const key = formElement.name.match(/fields\[(.*?)\]/);
        
        if (key && key[1]) {
            // Ustawienie wyciągniętego klucza jako ID elementu rodzica
            parent.id = key[1];
        }
    }
});
