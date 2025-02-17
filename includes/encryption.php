<?php
function encrypt_file($source_file, $dest_file) {
    $key = getenv('ENCRYPTION_KEY');
    if (!$key) {
        log_error("Encryption key not found", "ERROR");
        return false;
    }

    $iv = random_bytes(16);
    $key = base64_decode($key);
    
    if ($fpOut = fopen($dest_file, 'wb')) {
        // Write the IV to the output file
        fwrite($fpOut, $iv);
        
        // Now encrypt the data
        if ($fpIn = fopen($source_file, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * 1024); // Read 16kb at a time
                $ciphertext = openssl_encrypt(
                    $plaintext,
                    'AES-256-CBC',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
                );
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
            fclose($fpOut);
            return true;
        }
        fclose($fpOut);
    }
    return false;
}

function decrypt_file($source_file, $dest_file) {
    $key = getenv('ENCRYPTION_KEY');
    if (!$key) {
        log_error("Encryption key not found", "ERROR");
        return false;
    }

    $key = base64_decode($key);
    
    if ($fpIn = fopen($source_file, 'rb')) {
        // Read the IV from the input file
        $iv = fread($fpIn, 16);
        
        if ($fpOut = fopen($dest_file, 'wb')) {
            while (!feof($fpIn)) {
                $ciphertext = fread($fpIn, 16 * 1024 + 16); // Read 16kb + block size
                $plaintext = openssl_decrypt(
                    $ciphertext,
                    'AES-256-CBC',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
                );
                fwrite($fpOut, $plaintext);
            }
            fclose($fpOut);
            fclose($fpIn);
            return true;
        }
        fclose($fpIn);
    }
    return false;
}