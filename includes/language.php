<?php
function init_language() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en';
    }
    
    $language_file = __DIR__ . '/../languages/' . $_SESSION['language'] . '.php';
    if (!file_exists($language_file)) {
        $_SESSION['language'] = 'en';
        $language_file = __DIR__ . '/../languages/en.php';
    }
    
    return require $language_file;
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