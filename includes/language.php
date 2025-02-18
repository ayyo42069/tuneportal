<?php
function init_language() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en';
    }
    
    // Try to load selected language
    $language_file = __DIR__ . '/../languages/' . $_SESSION['language'] . '.php';
    if (file_exists($language_file)) {
        return require $language_file;
    }
    
    // Fallback to English if selected language file doesn't exist
    $en_file = __DIR__ . '/../languages/en.php';
    if (!file_exists($en_file)) {
        die('English language file not found');
    }
    
    $_SESSION['language'] = 'en';
    return require $en_file;
}


function __($key, $section = null) {
    static $translations = null;
    if ($translations === null || (isset($_SESSION['language']) && isset($translations['current_language']) && $translations['current_language'] !== $_SESSION['language'])) {
        $translations = init_language();
        $translations['current_language'] = $_SESSION['language'];
    }
    
    if ($section) {
        return $translations[$section][$key] ?? $key;
    }
    
    return $translations[$key] ?? $key;
}