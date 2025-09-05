<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $key;
    public function __construct() { $this->key = env('OPENAI_API_KEY'); }

    public function transcribeAudio(string $binary): string
    {
        $response = Http::withToken($this->key)
            ->attach('file', $binary, 'voice.ogg')
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'ru'
            ]);

        Log::info('Tранскрипция OpenAI: ', $response->json() ?? []);
        return $response->json('text', '');
    }

    public function parseOperationFromText(string $text): ?array
    {
        $system = "You are accountant parser. Parse user's phrase into JSON: {type: 'expense'|'income', title: string, amount: number, currency: string, category: string, occurred_at: 'YYYY-MM-DD' }. If unknown return null fields.";

        $response = Http::withToken($this->key)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role'=>'system','content'=>$system],
                ['role'=>'user','content'=>$text],
            ],
            'temperature' => 0.0,
            'max_tokens' => 300
        ]);

        $choices = $response->json('choices', []);
        if (!empty($choices) && isset($choices[0]['message']['content'])) {
            $content = $choices[0]['message']['content'];

            if (preg_match('/\{.*\}/s', $content, $m)) {
                $json = $m[0];
                $arr = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE) return $arr;
            }

            $arr = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) return $arr;
        }
        return null;
    }
}
