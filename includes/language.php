<?php
function init_language() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en';
    }
    
    // Load English translations as fallback
    $en_file = __DIR__ . '/../languages/en.php';
    if (!file_exists($en_file)) {
        die('English language file not found');
    }
    $en_translations = require $en_file;
    
    // If language is not English, load and merge with English
    if ($_SESSION['language'] !== 'en') {
        $language_file = __DIR__ . '/../languages/' . $_SESSION['language'] . '.php';
        if (file_exists($language_file)) {
            $translations = require $language_file;
            // Merge recursively, keeping English as fallback
            return array_replace_recursive($en_translations, $translations);
        }
        // If language file doesn't exist, fallback to English
        $_SESSION['language'] = 'en';
    }
    
    return $en_translations;
}

function __($key, $section = null) {
    static $translations = null;
    if ($translations === null) {
        $translations = init_language();
    }
    
    if ($section) {
        return $translations[$section][$key] ?? $key;
    }
    
    return $translations[$key] ?? $key;
}