<?php
// start session
session_start();
// config database
require_once 'config.php';

if (isset($_POST['register'])) {
    // Validate CSRF token
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check password strength
        $strength = 0;
        if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) {
            $strength++;
        }
        if (preg_match('/[0-9]/', $password)) {
            $strength++;
        }
        if (preg_match('/[!@#\$%\^\&*\)\(+=._-]/', $password)) {
            $strength++;
        }
        if (strlen($password) >= 8) {
            $strength++;
        }
        if ($strength < 3) {
            echo "Password is not strong enough. Meet at least three requirements.";
            exit();
        }

        // Server-side validation (customize according to your requirements)
        if (filter_var($email, FILTER_VALIDATE_EMAIL) &&
            strlen($username) >= 3 &&
            strlen($password) >= 8) {
            try {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Insert user data into the database
                $stmt = $db->prepare("INSERT INTO users (email, username, password) VALUES (:email, :username, :password)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();
                echo "success";
                exit();
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage();
                exit();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                exit();
            }

        } else {
            // Echo an error message
            echo "Invalid email, username, or password.";
            exit();
        }
    } else {
        // CSRF token error
        echo "CSRF token validation failed.";
        exit();
    }
} else {
    // CSRF token error
    echo "Form incomplete.";
    exit();
}

// flush output buffer and send output to client
ob_end_flush();
?>