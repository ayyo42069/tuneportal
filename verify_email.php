<?php
session_start();
include 'config.php';

if (!isset($_GET['token'])) {
    $_SESSION['error'] = "Invalid verification link.";
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Ellenőrzés: kiírjuk a token hosszát debugoláshoz
if (strlen($token) < 10) {
    die("Error: Token too short. Received: " . htmlspecialchars($token));
}

try {
    // Ellenőrzi, hogy a token létezik-e az adatbázisban
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Invalid verification link. The token may have expired or already been used.";
        header("Location: login.php");
        exit();
    }

    // Felhasználó adatainak lekérése
    $stmt->bind_result($user_id, $email);
    $stmt->fetch();
    $stmt->close();

    // Felhasználó ellenőrzötté tétele
    $update_stmt = $conn->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?");
    $update_stmt->bind_param("i", $user_id);
    $result = $update_stmt->execute();

    if ($result && $update_stmt->affected_rows > 0) {
        $_SESSION['success'] = "Your email has been verified successfully. You can now log in.";
    } else {
        $_SESSION['error'] = "Verification failed. The account may already be verified.";
    }

    $update_stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "An error occurred: " . htmlspecialchars($e->getMessage());
}

// Átirányítás a login oldalra
header("Location: login.php");
exit();
?>
