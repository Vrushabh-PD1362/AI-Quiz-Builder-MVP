<?php
require_once '../src/config.php';
require_once '../src/QuizProviderInterface.php';
require_once '../src/GeminiProxy.php';

header('Content-Type: application/json');

$topic = trim($_POST['topic'] ?? '');

if (!$topic) {
    http_response_code(400);
    echo json_encode(['error' => 'Topic is required to generate a quiz.']);
    exit;
}

try {
    $context = fetchWikiContext($topic);

    if (GEMINI_API_KEY === 'ENTER_YOUR_KEY_HERE' || empty(GEMINI_API_KEY)) {
        throw new Exception("Gemini API Key is missing in src/config.php");
    }

    $gemini = new GeminiProxy(GEMINI_API_KEY);
    $quiz = $gemini->fetchQuiz($topic, $context);

    echo json_encode(array_merge($quiz, ['source_context' => $context]));

} catch (Exception $e) {
    error_log("Quiz Generation Error: " . $e->getMessage()); 
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate quiz. Please try again later.']);
}

function fetchWikiContext($topic) {
    $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($topic);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'QuizBuilderApp/1.0',
        CURLOPT_TIMEOUT        => 5 
    ]);
    
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    
    if (isset($data['extract']) && $data['type'] !== 'disambiguation') {
        return $data['extract'];
    }

    return "Using general knowledge for this topic.";
}