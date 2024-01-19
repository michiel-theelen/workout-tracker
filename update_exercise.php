<?php

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exerciseId = $_POST['id'];
    $newName = $_POST['name'];
    try {
        $stmt = $db->prepare("UPDATE exercises SET name = :name WHERE id = :id");
        $stmt->bindValue(':name', $newName, PDO::PARAM_STR);
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