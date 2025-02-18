<?php
function init_language() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en';
    }
    
    // Load English translations for fallback
    $en_file = __DIR__ . '/../languages/en.php';
    if (!file_exists($en_file)) {
        die('English language file not found');
    }
    $en_translations = require $en_file;
    
    // If not English, load selected language
    if ($_SESSION['language'] !== 'en') {
        $language_file = __DIR__ . '/../languages/' . $_SESSION['language'] . '.php';
        if (file_exists($language_file)) {
            $selected_translations = require $language_file;
            return [
                'current' => $selected_translations,
                'fallback' => $en_translations,
                'current_language' => $_SESSION['language']
            ];
        }
    }
    
    return [
        'current' => $en_translations,
        'fallback' => $en_translations,
        'current_language' => 'en'
    ];
}

function __($key, $section = null) {
    static $translations = null;
    if ($translations === null || (isset($_SESSION['language']) && $translations['current_language'] !== $_SESSION['language'])) {
        $translations = init_language();
    }
    
    if ($section) {
        // Try current language first, then fallback to English
        return $translations['current'][$section][$key] 
            ?? $translations['fallback'][$section][$key] 
            ?? $key;
    }
    
    return $translations['current'][$key] 
        ?? $translations['fallback'][$key] 
        ?? $key;
}