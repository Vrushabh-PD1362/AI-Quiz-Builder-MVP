<?php
header("Content-Type: application/json");
require_once '../src/Database.php';

$db = (new Database())->getConnection();

try {
    $query = "SELECT q.id as quiz_id, q.topic, a.score_out_of_five, a.created_at 
              FROM attempts a 
              JOIN quizzes q ON a.quiz_id = q.id 
              ORDER BY a.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}