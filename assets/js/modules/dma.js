function dmaForms (selector) {
    // Znajdź element za pomocą selektora
    const element = document.querySelector(selector);

    // Upewnij się, że element istnieje
    if (!element) {
        console.warn('No element found with the given selector.');
        return;
    }

    /* ---- Przycisk Zapisz zmiany ---- */

    // 1. Stwórz element button
    const button = document.createElement('button');

    // 2. Ustaw atrybuty
    button.setAttribute('type', 'button');
    button.setAttribute('class', 'button-atom button-atom--primary breakdance-form-button');
    button.style.marginLeft = 'auto';
    button.setAttribute('id', 'breakdance-form-save');
    button.textContent = 'Zapisz zmiany';

    // 3. Dodaj element do DOM 'breakdance-form-footer'
    element.querySelector('.breakdance-form-footer').appendChild(button);

    // 4. Dodaj event listener
    button.addEventListener('click', () => {
        // Znajdź elementy z klasą 'breakdance-form-field'
        const fields = element.querySelectorAll('.breakdance-form-field');

        // Stwórz obiekt, który będzie przechowywał dane formularza
        const data = {};

        // Iteruj po wszystkich elementach z klasą 'breakdance-form-field'
        fields.forEach(parent => {
            // Szukanie pierwszego elementu formularza (input lub select) wewnątrz elementu rodzica
            const formElement = parent.querySelector('input, select');

            if (formElement && formElement.name) {
                // Wyciągnięcie klucza z wartości atrybutu 'name'
                const key = formElement.name.match(/fields\[(.*?)\]/);
                
                if (key && key[1]) {
                    // Ustawienie wyciągniętego klucza jako ID elementu rodzica
                    parent.id = key[1];

                    // Ustawienie wartości pola jako wartość klucza w obiekcie 'data'
                    data[key[1]] = formElement.value;
                }
            }
        });

        // Wyślij dane do API
        fetch('/wp-json/breakdance/v1/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error(error);
        });
    });

    /* ---- Opcja Żadne z powyższych ---- */

    const noneOfTheAboves = document.querySelectorAll('input[value="Żadne z powyższych"]');
    console.log('noneOfTheAboves', noneOfTheAboves);
    noneOfTheAboves.forEach(noneOfTheAbove => 
        noneOfTheAbove.addEventListener('change', () => {
            let inputs = noneOfTheAbove.closest('fieldset').querySelectorAll('input:not([value="Żadne z powyższych"]), select');
            console.log('inputs', inputs);
            if(!inputs.length) {
                // Znajdź wszystkie inputy i selecty z id zaczynającym się od tego samego id co rodzic elementu noneOfTheAbove z klasą '.breakdance-form-field' bez ostatniego znaku
                inputs = document.querySelectorAll(`input[id^="${noneOfTheAbove.closest('.breakdance-form-field').id.slice(0, -1)}"]:not([value="Żadne z powyższych"]), select[id^="${noneOfTheAbove.closest('.breakdance-form-field').id.slice(0, -1)}"]`);
                console.log('inputs', inputs);
            }
            
            inputs.forEach(input => {
                input.disabled = noneOfTheAbove.checked;
                if (input.type === 'checkbox') {
                    input.parentElement.style.color = noneOfTheAbove.checked ? '#ccc' : '#000';
                } else if (input.tagName === 'SELECT') {
                    input.style.color = noneOfTheAbove.checked ? '#ccc' : '#000';
                }
            });
        })
    );


}

// Zarejestruj funkcję w obiekcie CustomFunctions
if (!window.CustomFunctions) {
    window.CustomFunctions = {};
}

window.CustomFunctions.dmaForms = dmaForms;