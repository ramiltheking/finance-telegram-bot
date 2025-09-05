<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Services\TelegramService;
use App\Services\OpenAIService;
use App\Services\PaymentService;
use App\Models\User;
use App\Models\Payment;
use App\Models\Operation;

class TelegramWebhookController extends Controller
{
    protected TelegramService $tg;
    protected OpenAIService $ai;
    public function __construct(TelegramService $tg, OpenAIService $ai)
    {
        $this->tg = $tg;
        $this->ai = $ai;
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('TG Update:', $update);

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        } elseif (isset($update['inline_query'])) {
            Log::info('Инлайн событие: ', $update['inline_query']);
        } else {
            Log::warning('Неизвестное событие: ', $update);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'];
        $fromId = $message['from']['id'] ?? $chatId;
        $text = $message['text'] ?? null;

        $user = User::firstOrCreate(
            ['telegram_id' => $fromId],
            ['username' => $message['from']['username'] ?? null, 'first_name' => $message['from']['first_name'] ?? null]
        );

        if (isset($message['voice'])) {
            $fileId = $message['voice']['file_id'];

            $fileResp = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/getFile", ['file_id' => $fileId]);
            if ($fileResp->ok() && $fileResp->json('result.file_path')) {
                $filePath = $fileResp->json('result.file_path');
                $fileContents = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}");
                if ($fileContents->ok()) {
                    $binary = $fileContents->body();
                    $transcript = $this->ai->transcribeAudio($binary);
                    Log::info('Транскрипция: ' . $transcript);
                    $this->processParsedOperation($chatId, $user, $transcript);
                    return;
                }
            }
            $this->tg->sendMessage($chatId, '❗ Ошибка при получении голосового файла.');
            return;
        }

        if ($text) {
            if (str_starts_with($text, '/start')) {
                $this->sendStartMessage($chatId, $fromId);
                return;
            }
            if (str_starts_with($text, '/report')) {
                $this->sendWeeklyReport($chatId, $user);
                return;
            }
            if (str_starts_with($text, '/list')) {
                $this->sendList($chatId, $user);
                return;
            }
            if (str_starts_with($text, '/fullreport')) {
                $this->sendFullReport($chatId, $user);
                return;
            }
            if (str_starts_with($text, '/remind')) {
                $this->tg->sendMessage($chatId, "🔔 Напоминание установлено (в prototype).");
                return;
            }

            $this->processParsedOperation($chatId, $user, $text);
        }
    }

    protected function handleCallback(array $callback)
    {
        $data = $callback['data'] ?? null;
        $chatId = $callback['message']['chat']['id'] ?? ($callback['from']['id'] ?? null);
        $callbackId = $callback['id'] ?? null;
        if ($callbackId) $this->tg->answerCallback($callbackId);

        if ($data === 'workInfo') {
            $this->sendWorkInfo($chatId);
            return;
        }
        if ($data === 'tarifs') {
            $this->sendTarifs($chatId);
            return;
        }
        if ($data === 'confirm') {
            $user = User::where('telegram_id', $callback['from']['id'])->first();
            if ($user && !empty($user->operations)) {
                $last = array_pop($user->operations);
                $user->operations = $user->operations;
                $user->save();

                Operation::create([
                    'user_id' => $user->id,
                    'type' => $last['operation']['type'],
                    'amount' => $last['operation']['amount'],
                    'currency' => $last['operation']['currency'] ?? 'KZT',
                    'category' => $last['operation']['category'] ?? null,
                    'description' => $last['operation']['title'] ?? null,
                    'occurred_at' => now(),
                    'meta' => $last['operation'],
                ]);
                $this->tg->sendMessage($chatId, '✅ Запись добавлена');
                return;
            }
            $this->tg->sendMessage($chatId, '❗ Нечего подтверждать');
            return;
        }
        if ($data === 'decline') {
            $this->tg->sendMessage($chatId, '❌ Запись отклонена');
            return;
        }

        $this->tg->sendMessage($chatId, "Нажата кнопка: {$data}");
    }

    protected function sendStartMessage($chatId, $userId)
    {
        $serverUrl = config('app.url');
        $authUrl = $serverUrl . '/api/auth?state=' . $userId;
        $keyboard = [
            'inline_keyboard' => [
                [['text' => 'Авторизоваться через Google', 'url' => $authUrl]],
                [['text' => 'Публичная оферта', 'url' => 'https://docs.google.com/document/d/1J3drUDOdKG2JgOuqDpYcFE8tdC1IPFO2wOtigitv40Y/edit?usp=sharing']],
                [['text' => 'Политика конфиденциальности', 'url' => 'https://docs.google.com/document/d/1H6EKhbYHNcoV7w5Yr6vcgtE2868MIHxujTNtbOrddUE/edit?usp=sharing']],
                [['text' => 'Информация о работе бота', 'callback_data' => 'workInfo']],
                [['text' => 'Доступные тарифы', 'callback_data' => 'tarifs']],
            ]
        ];
        $this->tg->sendMessage($chatId, "👋 Добро пожаловать! Для использования бота, пожалуйста, авторизуйтесь:", $keyboard);
    }

    protected function sendTarifs($chatId)
    {
        $price = '2500.00';
        $invId = random_int(100000, 99999999);
        $login = env('ROBOKASSA_MERCHANT_LOGIN');
        $pass1 = env('ROBOKASSA_PASSWORD1');
        $signature = PaymentService::makeSignature($login, $price, (string)$invId, $pass1);
        $url = PaymentService::buildRobokassaUrl($login, $price, (string)$invId, 'Тариф VoiceFinance', $signature);

        Payment::create(['chat_id' => $chatId, 'inv_id' => $invId, 'amount' => $price, 'status' => 'pending']);

        $keyboard = ['inline_keyboard' => [[['text' => "💰 Оплатить тариф {$price} ₸", 'url' => $url]]]];
        $this->tg->sendMessage($chatId, "🎁 Стартовый период — 3 недели.\n💼 Тариф — {$price} ₸/мес.", $keyboard);
    }

    protected function sendWorkInfo($chatId)
    {
        $text =
        `🤖 *Финансовый помощник* — это умный бот для учёта ваших доходов и расходов.

            📌 Как он работает:
            1️⃣ Нажмите *Старт*
            2️⃣ Войдите через *Google-аккаунт*
            3️⃣ Бот создаст *персональную таблицу* в Google Sheets
            4️⃣ Общайтесь с ботом — можно писать или отправлять голосовые (до 10 сек)
            5️⃣ Просто напишите: "Получил зарплату 100000 тенге" — бот сам внесёт данные
            6️⃣ AI обработает всё автоматически и сохранит в таблицу
            7️⃣ Вы получите *наглядную аналитику* своих финансов

            📊 *Доступные команды:*
            - /remind — создать напоминание
            - /report — получить отчёт
            - /balance - получение баланса
            - /delete_last - удаление последней записи

            🧠 Бот использует искусственный интеллект: помогает, подсказывает и ведёт учёт в диалоге с вами. \n
            🤖 Просто начините пользоваться и все станет легко и понятно
        `;
        $this->tg->sendMessage($chatId, $text);
    }

    protected function sendWeeklyReport($chatId, User $user)
    {
        $oneWeekAgo = now()->subDays(7);
        $ops = Operation::where('user_id', $user->id)->where('created_at', '>=', $oneWeekAgo)->get();
        if ($ops->isEmpty()) {
            $this->tg->sendMessage($chatId, '❗ Нет операций за неделю');
            return;
        }
        $totalSpent = 0;
        $totalIncome = 0;
        $byCategory = [];
        foreach ($ops as $op) {
            $amt = (float)$op->amount;
            if ($op->type === 'expense') $totalSpent += $amt;
            else $totalIncome += $amt;
            $byCategory[$op->category = $op->category ?? '—'] = ($byCategory[$op->category] ?? 0) + $amt;
        }
        $msg = "📊 Отчёт за неделю\nРасходы: {$totalSpent}\nДоходы: {$totalIncome}\n\nКатегории:\n";
        foreach ($byCategory as $cat => $sum) $msg .= "{$cat}: {$sum}\n";
        $this->tg->sendMessage($chatId, $msg);
    }

    protected function sendList($chatId, User $user)
    {
        $oneWeekAgo = now()->subDays(7);
        $ops = Operation::where('user_id', $user->id)->where('created_at', '>=', $oneWeekAgo)->get();
        if ($ops->isEmpty()) {
            $this->tg->sendMessage($chatId, '❗ Нет операций');
            return;
        }
        $msg = "📋 Операции за неделю:\n";
        foreach ($ops as $i => $op) {
            $idx = $i + 1;
            $msg .= "{$idx}. {$op->description} {$op->type} {$op->amount} ({$op->category})\n";
        }
        $this->tg->sendMessage($chatId, $msg);
    }

    protected function sendFullReport($chatId, User $user)
    {
        $ops = Operation::where('user_id', $user->id)->get();
        if ($ops->isEmpty()) {
            $this->tg->sendMessage($chatId, '❗ Нет операций');
            return;
        }
        $totalSpent = 0;
        $totalIncome = 0;
        $byCategory = [];
        foreach ($ops as $op) {
            $amt = (float)$op->amount;
            if ($op->type === 'expense') $totalSpent += $amt;
            else $totalIncome += $amt;
            $cat = $op->category ?? '—';
            $byCategory[$cat] = ($byCategory[$cat] ?? 0) + $amt;
        }
        $msg = "📊 Полный отчёт\nРасходы: {$totalSpent}\nДоходы: {$totalIncome}\n\nКатегории:\n";
        foreach ($byCategory as $cat => $sum) $msg .= "{$cat}: {$sum}\n";
        $this->tg->sendMessage($chatId, $msg);
    }

    protected function processParsedOperation($chatId, User $user, string $text)
    {
        $parsed = $this->ai->parseOperationFromText($text);
        if (!$parsed) {
            $this->tg->sendMessage($chatId, "❗ Не удалось распознать операцию: " . $text);
            return;
        }

        $currencyMap = ['рубли' => 'RUB', 'доллары' => 'USD', 'евро' => 'EUR'];
        if (isset($parsed['currency']) && isset($currencyMap[$parsed['currency']])) {
            $parsed['currency'] = $currencyMap[$parsed['currency']];
        }

        $keyboard = ['inline_keyboard' => [
            [['text' => '✅ Подтвердить', 'callback_data' => 'confirm']],
            [['text' => '❌ Отклонить', 'callback_data' => 'decline']]
        ]];

        $ops = $user->operations ?? [];
        $ops[] = ['operation' => $parsed, 'status' => 'pending', 'created_at' => now()->toISOString()];
        $user->operations = $ops;
        $user->save();

        $preview = ($parsed['type'] ?? '') . ' ' . ($parsed['title'] ?? '') . ' ' . ($parsed['amount'] ?? '') . ' ' . ($parsed['currency'] ?? 'KZT');
        $this->tg->sendMessage($chatId, "✅ Добавить запись: {$preview}", $keyboard);
    }
}
