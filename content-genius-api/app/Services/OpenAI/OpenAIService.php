<?php

namespace App\Services\OpenAI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $maxTokens;
    protected float $temperature;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->model = config('openai.model');
        $this->baseUrl = config('openai.base_url');
        $this->maxTokens = config('openai.max_tokens');
        $this->temperature = config('openai.temperature');
        $this->timeout = config('openai.timeout');
    }

    /**
     * Create a configured HTTP client for OpenAI API.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->apiKey)
            ->timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * Send a chat completion request to OpenAI.
     */
    public function chat(string $systemPrompt, string $userMessage, array $options = []): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature,
        ];

        try {
            $response = $this->client()->post('/chat/completions', $payload);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'usage' => $data['usage'] ?? [],
                'model' => $data['model'] ?? $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate an article based on a topic.
     */
    public function generateArticle(string $topic, string $tone = 'professional', int $wordCount = 500): array
    {
        $systemPrompt = $this->buildArticleSystemPrompt($tone, $wordCount);
        $userMessage = "Write an article about: {$topic}";

        return $this->chat($systemPrompt, $userMessage);
    }

    /**
     * Rewrite text with a specific tone.
     */
    public function rewriteText(string $text, string $tone = 'professional'): array
    {
        $systemPrompt = $this->buildRewriteSystemPrompt($tone);

        return $this->chat($systemPrompt, $text);
    }

    /**
     * Summarize text.
     */
    public function summarizeText(string $text, string $format = 'bullets'): array
    {
        $systemPrompt = $this->buildSummarySystemPrompt($format);

        return $this->chat($systemPrompt, $text);
    }

    /**
     * Generate an email.
     */
    public function generateEmail(string $purpose, string $tone = 'professional', array $context = []): array
    {
        $systemPrompt = $this->buildEmailSystemPrompt($tone);
        $userMessage = $this->buildEmailUserMessage($purpose, $context);

        return $this->chat($systemPrompt, $userMessage);
    }

    /**
     * Translate text with cultural adaptation.
     */
    public function translateText(string $text, string $targetLanguage, string $sourceLanguage = 'auto'): array
    {
        $systemPrompt = $this->buildTranslationSystemPrompt($targetLanguage, $sourceLanguage);

        return $this->chat($systemPrompt, $text);
    }

    /**
     * Build the system prompt for article generation.
     */
    protected function buildArticleSystemPrompt(string $tone, int $wordCount): string
    {
        return <<<PROMPT
You are an expert content writer. Your task is to write engaging, well-structured articles.

Guidelines:
- Tone: {$tone}
- Target word count: approximately {$wordCount} words
- Include a compelling introduction
- Use clear headings and subheadings (markdown format)
- Provide valuable, actionable information
- End with a strong conclusion
- Write in a natural, human-like style
- Ensure content is original and informative

Output the article in markdown format.
PROMPT;
    }

    /**
     * Build the system prompt for text rewriting.
     */
    protected function buildRewriteSystemPrompt(string $tone): string
    {
        $toneDescriptions = [
            'professional' => 'formal, business-appropriate, clear and concise',
            'casual' => 'friendly, conversational, approachable',
            'academic' => 'scholarly, precise, well-researched',
            'creative' => 'engaging, imaginative, expressive',
            'persuasive' => 'compelling, convincing, action-oriented',
        ];

        $toneDesc = $toneDescriptions[$tone] ?? $toneDescriptions['professional'];

        return <<<PROMPT
You are an expert editor and rewriter. Your task is to rewrite the provided text while:

- Maintaining the original meaning and key information
- Adapting the tone to be: {$toneDesc}
- Improving clarity and readability
- Fixing any grammatical errors
- Enhancing the overall flow

Output only the rewritten text without any explanations or prefixes.
PROMPT;
    }

    /**
     * Build the system prompt for text summarization.
     */
    protected function buildSummarySystemPrompt(string $format): string
    {
        $formatInstructions = match ($format) {
            'bullets' => 'Present the summary as a bullet point list with key takeaways.',
            'paragraph' => 'Write a concise paragraph summarizing the main points.',
            'executive' => 'Write an executive summary suitable for business stakeholders.',
            default => 'Present the summary as a bullet point list with key takeaways.',
        };

        return <<<PROMPT
You are an expert at summarizing complex information. Your task is to create clear, accurate summaries.

Guidelines:
- Extract the most important information
- {$formatInstructions}
- Be concise but comprehensive
- Maintain accuracy to the source material
- Highlight key insights and conclusions

Output only the summary without any introductory phrases.
PROMPT;
    }

    /**
     * Build the system prompt for email generation.
     */
    protected function buildEmailSystemPrompt(string $tone): string
    {
        return <<<PROMPT
You are an expert business communication specialist. Your task is to write professional emails.

Guidelines:
- Tone: {$tone}
- Include appropriate greeting and closing
- Be clear and concise
- Structure content logically
- Use professional language
- Include a clear call-to-action when appropriate

Output the complete email including subject line (prefixed with "Subject: ").
PROMPT;
    }

    /**
     * Build the user message for email generation.
     */
    protected function buildEmailUserMessage(string $purpose, array $context): string
    {
        $message = "Write an email for the following purpose: {$purpose}";

        if (!empty($context['recipient_name'])) {
            $message .= "\nRecipient: {$context['recipient_name']}";
        }

        if (!empty($context['sender_name'])) {
            $message .= "\nSender: {$context['sender_name']}";
        }

        if (!empty($context['additional_info'])) {
            $message .= "\nAdditional context: {$context['additional_info']}";
        }

        return $message;
    }

    /**
     * Build the system prompt for translation.
     */
    protected function buildTranslationSystemPrompt(string $targetLanguage, string $sourceLanguage): string
    {
        $sourceInfo = $sourceLanguage === 'auto'
            ? 'Auto-detect the source language'
            : "Source language: {$sourceLanguage}";

        return <<<PROMPT
You are an expert translator with deep cultural knowledge. Your task is to translate text accurately.

Guidelines:
- {$sourceInfo}
- Target language: {$targetLanguage}
- Maintain the original meaning and tone
- Adapt cultural references appropriately
- Use natural expressions in the target language
- Preserve formatting when possible

Output only the translated text without any explanations.
PROMPT;
    }
}
