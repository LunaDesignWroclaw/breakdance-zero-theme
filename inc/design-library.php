<?php

use function Breakdance\DesignLibrary\setDesignSets;
use function Breakdance\DesignLibrary\getRegisteredDesignSets;

$currentSites = getRegisteredDesignSets();

$ehSites = [
    'https://library.lunadesign.com.pl'
];

// Sprawdzenie, czy element istnieje i uzyskanie jego klucza
$key = array_search($ehSites[0], $currentSites);

// Jeśli strona już istnieje, usuń ją
if ($key !== false) {
    unset($currentSites[$key]);
}

// Resetowanie indeksów, jeśli jest to potrzebne
$currentSites = array_values($currentSites);

// Dodaj element na trzecim miejscu
if(count($currentSites) >= 2){
    array_splice($currentSites, 2, 0, $ehSites); // Dodanie w trzecie miejsce
} else {
    // Jeśli mniej niż 2 elementy, po prostu dołącz na końcu
    array_push($currentSites, $ehSites[0]);
}

// Zaktualizuj design sets
setDesignSets($currentSites);