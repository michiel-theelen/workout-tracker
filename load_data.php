<?php
session_start();
require_once 'config.php';

// Get the current user ID from the session
$user_id = $_SESSION['user_id'];
// Fetch the username for the logged-in user
$stmt = $db->prepare("SELECT username FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$username = $stmt->fetchColumn();

// Fetch all sets for the current user with the corresponding exercise name
$sets = $db->prepare("
    SELECT s.id, s.date, s.weight, s.reps, e.name AS exercise_name, s.exercise_id
    FROM sets s 
    JOIN exercises e ON s.exercise_id = e.id
    WHERE s.user_id = :user_id 
    ORDER BY s.date ASC
");
$sets->execute([':user_id' => $user_id]);
$sets = $sets->fetchAll(PDO::FETCH_ASSOC);

// Get unique exercises from the sets
$exercises = array_values(array_unique(array_map(function ($set) {
    return [
        'id' => $set['exercise_id'],
        'name' => $set['exercise_name']
    ];
}, $sets), SORT_REGULAR));

?>