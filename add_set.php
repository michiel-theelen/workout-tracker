<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['exerciseId'], $_POST['date'], $_POST['reps'], $_POST['weight'])) {
        $exercise_id = $_POST['exerciseId'];
        $date = $_POST['date'];
        $reps = $_POST['reps'];
        $weight = $_POST['weight'];
        $user_id = $_SESSION['user_id'];

        try {
            // Debug: Log the received data
            error_log("Received data - exercise_id: $exercise_id, date: $date, reps: $reps, weight: $weight, user_id: $user_id");

            // check if the exercise belongs to the logged-in user
            $stmt = $db->prepare("SELECT user_id FROM exercises WHERE id = :exercise_id");
            $stmt->bindParam(':exercise_id', $exercise_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $exercise_user_id = $result['user_id'];

            if ($exercise_user_id != $user_id) {
                throw new Exception('Exercise does not belong to the current user.');
            }

            // insert the new set into the database
            $stmt = $db->prepare("INSERT INTO sets (exercise_id, date, reps, weight, user_id) VALUES (:exercise_id, :date, :reps, :weight, :user_id)");
            $stmt->bindParam(':exercise_id', $exercise_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':reps', $reps, PDO::PARAM_INT);
            $stmt->bindParam(':weight', $weight, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // get the id of the newly inserted set
            $setId = $db->lastInsertId();

            // Debug: Log the set ID
            error_log("Set added successfully. Set ID: $setId");

            // return the id as a JSON response
            header('Content-Type: application/json');
            echo json_encode(['id' => $setId]);

        } catch (PDOException $e) {
            // Debug: Log any errors
            error_log("Error adding set: " . $e->getMessage());

            http_response_code(500);
            echo json_encode(['error' => 'Error adding set: ' . $e->getMessage()]);
        }
    }
}
?>
