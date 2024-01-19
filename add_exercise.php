<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exerciseName = $_POST['name'];
    $userId = $_SESSION['user_id'];

    try {
        // Check if the exercise name already exists for the current user
        $checkStmt = $db->prepare("SELECT id FROM exercises WHERE name = :name AND user_id = :user_id");
        $checkStmt->bindValue(':name', $exerciseName, PDO::PARAM_STR);
        $checkStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $checkStmt->execute();

        $existingExercise = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingExercise) {
            // Exercise already exists, return its ID
            echo json_encode(['id' => $existingExercise['id']]);
        } else {
            // Insert the new exercise
            $insertStmt = $db->prepare("INSERT INTO exercises (name, user_id) VALUES (:name, :user_id)");
            $insertStmt->bindValue(':name', $exerciseName, PDO::PARAM_STR);
            $insertStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insertStmt->execute();

            $exerciseId = $db->lastInsertId();
            $exercise = ['id' => $exerciseId, 'name' => $exerciseName];
            echo json_encode($exercise);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

