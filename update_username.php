<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['new-username'];
    $currentPassword = $_POST['current-password'];
    $userId = $_SESSION['user_id'];

    // Check if the current password matches the user's password in the database
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['error' => 'Incorrect password']);
        exit();
    }

    // Update the user's username in the database
    $stmt = $db->prepare("UPDATE users SET username = :username WHERE id = :id");
    $stmt->bindValue(':username', $newUsername, PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => 'Username updated successfully']);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
