<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'];
    $newPassword1 = $_POST['new_password_1'];
    $newPassword2 = $_POST['new_password_2'];
    $userId = $_SESSION['user_id'];

    try {
        // check if old password is correct
        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $passwordHash = $result['password'];
        if (!password_verify($oldPassword, $passwordHash)) {
            throw new Exception('Incorrect old password');
        }

        // check if new passwords match
        if ($newPassword1 !== $newPassword2) {
            throw new Exception('New passwords do not match');
        }

        // update password
        $newPasswordHash = password_hash($newPassword1, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $stmt->bindValue(':password', $newPasswordHash, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // redirect to settings page
        header('Location: tracker.php');
        exit();

    } catch (Exception $e) {
        // handle errors
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: settings.php');
        exit();
    }
}
?>
