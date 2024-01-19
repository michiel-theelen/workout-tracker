<?php
session_start();
require_once 'config.php';

// Get the current user ID from the session
$user_id = $_SESSION['user_id'];

// Select the data for the specified user_id
$stmt = $db->prepare("SELECT sets.id, exercises.name AS exercise_name, exercises.id AS exercise_id, sets.date, sets.reps, sets.weight FROM sets INNER JOIN exercises ON sets.exercise_id = exercises.id WHERE sets.user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert the data to the format expected by ECharts
$formattedData = [];
foreach ($data as $row) {
    $formatted_date = date('Y-m-d H:i:s', strtotime($row['date'] . ' 00:00:00'));

    $formattedData[] = [
        'id' => $row['id'],
        'exercise_name' => $row['exercise_name'],
        'exercise_id' => $row['exercise_id'],
        'date' => $formatted_date,
        'reps' => $row['reps'],
        'weight' => $row['weight']
    ];
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($formattedData);
?>
