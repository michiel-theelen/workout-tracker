<?php
$servername = "localhost";
$username = "michieltheelen";
$password = "dimhyg-6fynci-cisxIb";
$dbname = "workout_tracker";

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$db->exec("CREATE TABLE IF NOT EXISTS exercises (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    name VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS sets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    exercise_id INT(11) NOT NULL,
    reps INT(11) NOT NULL,
    weight INT(11) NOT NULL,
    date DATE NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS users (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_code VARCHAR(255),
    PRIMARY KEY (user_id)
)");
?>
