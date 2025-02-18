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
            // Deep merge translations, preserving both English and selected language
            foreach ($translations as $section => $values) {
                if (isset($en_translations[$section]) && is_array($en_translations[$section])) {
                    $en_translations[$section] = array_merge($en_translations[$section], $values);
                } else {
                    $en_translations[$section] = $values;
                }
            }
        }
    }
    
    return $en_translations;
} 

function __($key, $section = null) {
    static $translations = null;
    if ($translations === null || (isset($_SESSION['language']) && $translations['current_language'] !== $_SESSION['language'])) {
        $translations = init_language();
        $translations['current_language'] = $_SESSION['language'];
    }
    
    if ($section) {
        return $translations[$section][$key] ?? $translations['en'][$section][$key] ?? $key;
    }
    
    return $translations[$key] ?? $key;
}