<?php
header("Content-Type: application/json");
require_once '../src/Database.php';

$db = (new Database())->getConnection();
$quizId = $_GET['id'];

try {
    $stmt = $db->prepare("SELECT user_responses FROM attempts WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT question_text as q, option_a as A, option_b as B, option_c as C, option_d as D, correct_option as correct, explanation FROM questions WHERE quiz_id = ?");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($questions as &$q) {
        $q['options'] = ['A' => $q['A'], 'B' => $q['B'], 'C' => $q['C'], 'D' => $q['D']];
    }

    echo json_encode(["success" => true, "attempt" => $attempt, "questions" => $questions]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}