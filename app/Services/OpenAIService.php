<?php

namespace App\Services;

use App\Models\User;
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

        $today = now()->format('Y-m-d');

        $systemPrompt = <<<PROMPT
            You are a strict financial transaction parser. Your job is to **analyze user's text** and return **only a valid JSON** object that describes the transaction.

            Return result in JSON **only** (no explanations, no markdown, no comments).
            If message is not related to finance, return `null` (as plain JSON value, not as string).

            ### REQUIRED JSON STRUCTURE:
            {
                "type": "expense" | "income",
                "title": string,
                "amount": number,
                "currency": string,
                "category": string,
                "occurred_at": "YYYY-MM-DD"
            }

            ### STRICT CATEGORY RULES:
            1. **NEVER CREATE NEW CATEGORIES** - use ONLY from the provided lists below
            2. If no category matches exactly → use ONLY "Прочие расходы" or "Прочие доходы" for Russian or "Other Expenses" or "Other Income" for English
            3. For custom user categories - use ONLY if exact match exists in custom lists
            4. **ABSOLUTELY FORBIDDEN** to invent new category names

            ### GENERAL RULES:
            1. The user's input can be in Russian or English or Other language
            - If most of the text is in Russian → respond in Russian (`title` and `category` names stay in Russian).
            - If the text is in English → respond in English.
            2. Respond strictly with JSON that fits the schema above. Do not add comments, markdown, or text.
            3. Use only **leaf categories** (subcategories), never parent groups.
            4. If user provides **only amount** (e.g. "5000" or "+2000")
            - `"category"` = use ONLY system category "Прочие расходы" if amount without anything or minus only is start (e.g. "2000" or "-2000") or "Прочие доходы" if amount plus only in start (e.g. "+2000") (Russian) or "Other Expenses" if amount without anything or minus only is start (e.g. "2000" or "-2000") or "Other Income" if amount plus only in start (e.g. "+2000") (English)
            - `"title"` = "Прочие расходы" if amount without anything or minus only is start (e.g. "2000" or "-2000") or "Прочие доходы" if amount plus only in start (e.g. "+2000") (if Russian) or "Other Expenses" if amount without anything or minus only is start (e.g. "2000" or "-2000") or "Other Income" if amount plus only in start (e.g. "+2000") (if English)
            - `"type"` = "expense" if amount without anything or minus only is start (e.g. "2000" or "-2000") or "income" if amount plus only in start (e.g. "+2000")
            5. If user mentions words like "зарплата", "salary", "income", "received", etc. → `"type"` = "income"
            Otherwise → `"type"` = "expense"
            6. Amount (`"amount"`) must always be numeric (float or integer only). Extract it correctly even if written with spaces or symbols (e.g. "5 000₸", "$200", "10k" → 5000, 200, 10000).
            7. Currencies:
            - "доллар", "бакс", "usd", "$" → "USD"
            - "рубл", "₽", "rub" → "RUB"
            - "евро", "eur", "€" → "EUR"
            - "тенге", "₸", "kzt" → "KZT"
            - If not specified → "KZT"
            8. Dates:
            - If user says "вчера", "yesterday" → subtract 1 day from today.
            - If mentions a specific date ("15 октября", "2025-10-01") → use that.
            - Otherwise → use today's date "{$today}".
            9. `"title"`:
            - Must be descriptive but concise.
            - Must be in the same language as user's message.
            - If no specific item mentioned → default to "Прочие расходы" or "Прочие доходы"/"Other Expenses" or "Other Income".
            10. If the message is clearly **not a financial transaction**,
            you must **analyze it and return a JSON action descriptor** instead of a text response.
            The goal is to help the system route the user's intent to the correct function.

            Always respond strictly in JSON with this format:
            {
                "action": string,
                "parameters": object | null
            }

            ### Examples of possible actions:
            - Asking for help or how to use the bot → `{ "action": "help", "parameters": null }`

            When you detect a **financial transaction**, return a full JSON transaction object (as described above).
            Otherwise, return only the `"action"` object.

            Never mix both (transaction and action) in a single response.

            ### AVAILABLE CATEGORIES:
            {$categories}

            ### FINAL REMINDER:
            - NEVER INVENT NEW CATEGORY NAMES
            - USE ONLY FROM LISTS ABOVE
            - DEFAULT TO "Прочие расходы" or "Прочие доходы"/"Other Expenses" or "Other Income" IF NO MATCH
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
