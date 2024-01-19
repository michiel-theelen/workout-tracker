<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exerciseId = $_POST['id'];
    try {
        // delete all sets with the given exercise_id
        $stmt = $db->prepare("DELETE FROM sets WHERE exercise_id = :id");
        $stmt->bindValue(':id', $exerciseId, PDO::PARAM_INT);
        $stmt->execute();

        // delete the exercise with the given id
        $stmt = $db->prepare("DELETE FROM exercises WHERE id = :id");
        $stmt->bindValue(':id', $exerciseId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

?>