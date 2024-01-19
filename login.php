<?php

session_start();
require_once 'config.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the login form was submitted
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['csrf_token'])) {
    // Validate CSRF token
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        try {
            // Initialize the session variables for rate limiting if they don't exist
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            if (!isset($_SESSION['last_attempt_time'])) {
                $_SESSION['last_attempt_time'] = time();
            }

            $current_time = time();
            $time_since_last_attempt = $current_time - $_SESSION['last_attempt_time'];

            // Reset the login attempts counter if more than 1 minute has passed
            if ($time_since_last_attempt > 60) {
                $_SESSION['login_attempts'] = 0;
            }

            // Check if the rate limit has been exceeded
            if ($_SESSION['login_attempts'] >= 3) {
                echo "Too many login attempts. Please try again in 1 minute.";
                exit();
            }

            $username = $_POST['username'];
            $password = $_POST['password'];

            // Server-side validation (customize according to your requirements)
            if (strlen($username) >= 3 && strlen($password) >= 8) {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', strtolower($username));
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['user_id'];
                        echo 'success';
                        exit();
                    }
                } else {
                    // Generic error message
                    echo "Wrong username or password.";
                    exit();
                }
            } 

            // Increment the login attempts counter and update the last_attempt_time
            $_SESSION['login_attempts'] += 1;
            $_SESSION['last_attempt_time'] = $current_time;

            // Generic error message
            echo "Wrong username or password.";
            exit();
        } catch (PDOException $e) {
            // Handle the exception
            echo "Server error. Please try again later.";
            exit();
        }
    } else {
        echo "Invalid CSRF token.";
        exit();
    }
}

?>