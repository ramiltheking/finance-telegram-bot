<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $key;

    public function __construct()
    {
        $this->key = (string) env('OPENAI_API_KEY');
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

    public function parseOperationFromText(string $text): ?array
    {
        $systemPrompt = <<<PROMPT
            You are accountant parser. Parse user's phrase into JSON:

            {
                "type": "expense" | "income",
                "title": string,
                "amount": number,
                "currency": string,
                "category": string,
                "occurred_at": "YYYY-MM-DD"
            }

            Rules:
            1) For "category", you must always return only a **subcategory** (leaf-node) from the list (those inside groups like "Salary", "Rent / Mortgage", "Groceries", etc.). Never return parent groups (e.g. "Food & Leisure", "Housing & Utilities"). If none match, return "Other".
            2) Do not invent new categories.
            3) "type" must be "income" if the category is from INCOME list, or "expense" if from EXPENSE list.
            4) "title" return in Russian language.
            4) "occurred_at" must be today’s date if not explicitly mentioned.
            5) Determine the currency from the text or from the context:
            - If text contains "доллар", "бакс", "usd", "$" → "USD"
            - If text contains "рубл", "₽", "rub" → "RUB"
            - If text contains "евро", "eur", "€" → "EUR"
            - If text contains "тенге", "₸", "kzt" → "KZT"
            - Otherwise, if not specified → set "currency": "KZT".
            6) If the message is not a financial transaction, return null.
            7) "amount" must always be a valid number (float).

            Available categories:

            { "INCOME": { "Work & Business": [ "Salary", "Freelance", "Side Jobs", "Business Profit", "Selling Services" ], "Investments & Capital": [ "Investments", "Dividends", "Currency Exchange Profit", "Digital Assets Sale", "Royalties", "Intellectual Property Sale" ], "Real Estate & Rent": [ "Rent", "Rental Equipment/Transport" ], "Social & Personal": [ "Social Payments", "Pension", "Scholarship / Grant", "Gifts", "Prizes", "Inheritance" ], "Refunds & Donations": [ "Debt Return", "Refunds & Compensations", "Crowdfunding / Donations Received", "Affiliate & Advertising", "Online Projects", "Cashback" ], "Loans": [ "Loans Received" ] }, "EXPENSE": { "Housing & Utilities": [ "Housing", "Rent / Mortgage", "Utilities", "Internet & Mobile", "Household Goods", "Furniture & Appliances", "Home Renovation & Repairs", "Home Security" ], "Personal": [ "Clothes", "Beauty & Care", "Hairdresser", "Smartphones & Gadgets", "Computers", "Hobbies & Collections" ], "Family & Children": [ "Children & Education", "Tutors", "Courses", "Gifts to Others", "Pets" ], "Food & Leisure": [ "Groceries", "Restaurants", "Coffee & Snacks", "Food Delivery", "Bars & Clubs", "Cinema & Theatre", "Music & Concerts", "Games", "Travel", "Travel Tickets", "Books", "Streaming & Entertainment Subscriptions" ], "Transport": [ "Public Transport", "Taxi", "Fuel", "Car Maintenance", "Parking & Tolls" ], "Health": [ "Doctors", "Dentist", "Medicine", "Fitness & Yoga", "Sport & Fitness", "Health Insurance" ], "Finance & Obligations": [ "Credits & Debts", "Transfers", "Investments Purchase", "Insurance", "Currency Exchange", "Loans Given", "Taxes", "Bank Fees & Commissions", "Legal Services & Fines" ], "Other": [ "Subscriptions", "Online Services", "Business Expenses", "Charity & Donations", "Alcohol & Tobacco", "Gambling & Lottery" ] } }
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
