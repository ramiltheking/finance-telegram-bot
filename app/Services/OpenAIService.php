<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $key;
    protected $categoryService;

    public function __construct()
    {
        $this->key = (string) env('OPENAI_API_KEY');
        $this->categoryService = new CategoryService();
    }

    public function transcribeAudio(string $binary): string
    {
        try {
            $response = Http::withToken($this->key)
                ->attach('file', $binary, 'voice.ogg')
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'    => 'whisper-1',
                    'language' => 'ru',
                ]);

            $data = $response->json();

            Log::info('Транскрипция OpenAI', $data ?? []);

            return $data['text'] ?? '';
        } catch (\Throwable $e) {
            Log::error('Ошибка транскрипции OpenAI: ' . $e->getMessage());
            return '';
        }
    }

    public function parseOperationFromText(string $text, ?int $userId = null): ?array
    {
        $categories = $this->categoryService->getAvailableCategories($userId);

        $systemPrompt = <<<PROMPT
            You are accountant parser. Parse user's phrase into JSON:

            {
                "type": "expense" | "income",
                "title": string,
                "amount": number,
                "currency": string,
                "category": string,
            }

            Rules:
            1) For "category", you must always return only a **subcategory** (leaf-node) from the list (those inside groups like "Salary", "Rent / Mortgage", "Groceries", etc.). Never return parent groups (e.g. "Food & Leisure", "Housing & Utilities"). If none match, return "Other".
            2) Do not invent new categories.
            3) "type" must be "income" if the category is from INCOME list, or "expense" if from EXPENSE list.
            4) return of the “title” in the same language that was used by the user of the written financial transaction.
            5) Determine the currency from the text or from the context:
            - If text contains "доллар", "бакс", "usd", "$" → "USD"
            - If text contains "рубл", "₽", "rub" → "RUB"
            - If text contains "евро", "eur", "€" → "EUR"
            - If text contains "тенге", "₸", "kzt" → "KZT"
            - Otherwise, if not specified → set "currency": "KZT".
            6) If the message is not a financial transaction, return null.
            7) "amount" must always be a valid number (float).

            **IMPORTANT FOR CUSTOM CATEGORIES**: First, check whether the user has created categories that fit the context. When using custom categories, ALWAYS return the exact same name as specified in the "Custom Categories" list below. If there are no custom categories, use the system ones. Do not change or translate the names of custom categories.

            Available categories:

            {$categories}
        PROMPT;

        try {
            $response = Http::withToken($this->key)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'      => 'gpt-4o-mini',
                    'messages'   => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $text],
                    ],
                    'temperature' => 0.0,
                    'max_tokens'  => 300,
                ]);

            $choices = $response->json('choices', []);

            if (!empty($choices) && isset($choices[0]['message']['content'])) {
                $content = $choices[0]['message']['content'];

                if (preg_match('/\{.*\}/s', $content, $m)) {
                    $json = $m[0];
                    $arr  = json_decode($json, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $arr;
                    }
                }

                $arr = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $arr;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Ошибка парсинга операции: ' . $e->getMessage());
            return null;
        }
    }
}
