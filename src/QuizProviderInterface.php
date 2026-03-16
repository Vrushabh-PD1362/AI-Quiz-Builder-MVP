<?php
interface QuizProviderInterface {
    public function fetchQuiz(string $topic, string $context = ""): array;
}