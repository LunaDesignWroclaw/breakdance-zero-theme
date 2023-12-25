window.removeAllEventListeners = (selector) => {
    // Znajdź element za pomocą selektora
    const element = document.querySelector(selector);

    // Upewnij się, że element istnieje
    if (!element) {
        console.warn('No element found with the given selector.');
        return;
    }

    // Klonuj element bez klonowania nasłuchiwaczy zdarzeń
    const clonedElement = element.cloneNode(true);

    // Zastąp stary element jego klonem
    element.parentNode.replaceChild(clonedElement, element);
}

alert('Hello World!');