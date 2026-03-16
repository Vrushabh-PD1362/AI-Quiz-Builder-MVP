<?php
header("Content-Type: application/json");
require_once '../src/Database.php';

$db = (new Database())->getConnection();
$data = json_decode(file_get_contents("php://input"), true);

try {
    $db->beginTransaction();

    // 1. Save the Quiz Session
    $stmt = $db->prepare("INSERT INTO quizzes (topic) VALUES (?)");
    $stmt->execute([$data['topic']]);
    $quiz_id = $db->lastInsertId();

    // 2. Save Questions for future "Review" functionality
    foreach ($data['questions'] as $q) {
        $stmt = $db->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $quiz_id, $q['q'], $q['options']['A'], $q['options']['B'], $q['options']['C'], $q['options']['D'], $q['correct'], $q['explanation']
        ]);
    }

    // 3. Save User Attempt Score
    $stmt = $db->prepare("INSERT INTO attempts (quiz_id, score_out_of_five, user_responses) VALUES (?, ?, ?)");
    $stmt->execute([
        $quiz_id, 
        $data['score'], 
        json_encode($data['user_responses'])
    ]);

    $db->commit();
    echo json_encode(["status" => "success", "message" => "Quiz saved to database"]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}