<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $setId = $_POST['id'];
    try {
        $stmt = $db->prepare("DELETE FROM sets WHERE id = :id");
        $stmt->bindValue(':id', $setId, PDO::PARAM_INT);
        $stmt->execute();
        updateChart();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("PDOException occurred: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
