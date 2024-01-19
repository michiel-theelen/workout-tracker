<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['exerciseId'], $_POST['date'], $_POST['reps'], $_POST['weight'])) {
        $exerciseId = $_POST['exerciseId'];
        $date = $_POST['date'];
        $reps = $_POST['reps'];
        $weight = $_POST['weight'];
        $userId = $_SESSION['user_id'];

        try {
            // Check if the exercise belongs to the logged-in user
            $stmt = $db->prepare("SELECT user_id FROM exercises WHERE id = :exercise_id");
            $stmt->bindParam(':exercise_id', $exerciseId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $exerciseUserId = $result['user_id'];

            if ($exerciseUserId !== $userId) {
                throw new Exception('Exercise does not belong to the current user.');
            }

            // Insert the new set into the database
            $stmt = $db->prepare("INSERT INTO sets (exercise_id, date, reps, weight, user_id) VALUES (:exercise_id, :date, :reps, :weight, :user_id)");
            $stmt->bindParam(':exercise_id', $exerciseId, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':reps', $reps, PDO::PARAM_INT);
            $stmt->bindParam(':weight', $weight, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // Get the id of the newly inserted set
            $setId = $db->lastInsertId();

            // Log the setId to the server's error log
            error_log('SetId: ' . $setId);

            // Return the id as a JSON response
            header('Content-Type: application/json');
            echo json_encode(['id' => $setId]);


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error adding set: ' . $e->getMessage()]);
        }
    }
}
?>
