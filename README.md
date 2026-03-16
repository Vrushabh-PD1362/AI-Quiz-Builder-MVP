AI Quiz Builder MVP
An AI-powered quiz generator that creates factual multiple-choice questions based on any topic using Google Gemini and Wikipedia grounding.

1) System Architecture & Technical Decisions
Decoupled Logic (Proxy Pattern): I implemented a QuizProviderInterface to decouple the core application from the AI service. The GeminiProxy handles API communication, making it easy to swap Gemini for OpenAI or Anthropic without rewriting the business logic.

2) Retrieval-Augmented Generation (Factual Grounding): To prevent AI hallucinations, the system fetches factual context via the Wikipedia API before generation. This context is injected into the Gemini prompt to ensure questions are grounded in reality.

3) Data Persistence: Used a MySQL backend to store quiz attempts, enabling a "Review" feature where users can re-examine past mistakes without re-calling the AI.

4) Security: Implemented a config.php system (excluded via .gitignore) to protect sensitive API keys and database credentials.

5) AI Tool Selection & Reasoning
Tool: Google Gemini 1.5 Flash.

6) Reasoning: Chosen for its industry-leading speed and high token efficiency. The "Flash" model is ideal for real-time applications like a quiz builder where low latency is critical for a good user experience.

7) Setup
Import db/schema.sql into your MySQL server.

In src/config.php, add your Gemini API Key.

Serve via WAMP/Apache and navigate to index.html.