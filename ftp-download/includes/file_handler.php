<?php
function serve_file($filepath, $filename) {
    $temp_file = tempnam(sys_get_temp_dir(), 'download_');
    if (decrypt_file($filepath, $temp_file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($temp_file));
        readfile($temp_file);
        unlink($temp_file);
        exit();
    }
    return false;
}