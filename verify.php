<?php
session_start();
require_once 'config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Check if the code is valid
    $stmt = $db->prepare("SELECT * FROM users WHERE verification_code = :code");
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mark the user as verified
        $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE id = :id");
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        // Display a success message
        $_SESSION['verify_success'] = "Your email address has been verified. You may now log in.";
        header("Location: welcome.php");
        exit();
    } else {
        // Display an error message
        $_SESSION['verify_error'] = "Invalid verification code.";
        header("Location: welcome.php");
        exit();
    }
} else {
    // Display an error message
    $_SESSION['verify_error'] = "No verification code specified.";
    header("Location: welcome.php");
    exit();
}
?>
