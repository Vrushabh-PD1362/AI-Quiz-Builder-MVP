<?php
class GeminiProxy implements QuizProviderInterface {
    private string $apiKey;
     private string $apiUrl = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent";

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function fetchQuiz(string $topic, string $context = ""): array {
        // INJECTION: We embed the $context directly into the instructions
        $prompt = "You are a factual quiz creator.
        
        SOURCE MATERIAL:
        $context

        TASK:
        Generate a 5-question multiple choice quiz about '$topic'.
        Use the SOURCE MATERIAL provided above as the primary reference for factual accuracy. 
        If the source material is missing or irrelevant, use general verified knowledge.

        REQUIREMENTS:
        - Exactly 5 questions with 4 options (A, B, C, D) each.
        - Provide a clear 'explanation' for the correct answer.
        - Return ONLY a valid JSON object:
        {
          \"questions\": [
            {
              \"q\": \"text\",
              \"options\": {\"A\": \"text\", \"B\": \"text\", \"C\": \"text\", \"D\": \"text\"},
              \"correct\": \"A\",
              \"explanation\": \"text\"
            }
          ]
        }";

        $payload = json_encode([
            "contents" => [["parts" => [["text" => $prompt]]]]
        ]);

        $ch = curl_init($this->apiUrl . "?key=" . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new Exception("CURL Error: " . curl_error($ch));
        }
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API Error ($httpCode): " . $response);
        }

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $cleanJson = preg_replace('/^```json\s*|```$/m', '', trim($text));
        
        return json_decode($cleanJson, true);
    }
}