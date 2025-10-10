<?php

namespace App\Telegram\Webhook;

use App\Facades\Telegram;
use App\Services\UserService;
use App\Telegram\Webhook\Commands\BalanceCommand;
use App\Telegram\Webhook\Commands\DeleteCommand;
use App\Telegram\Webhook\Commands\DeleteLastCommand;
use App\Telegram\Webhook\Commands\EditCommand;
use App\Telegram\Webhook\Commands\ListCommand;
use App\Telegram\Webhook\Commands\ReportCommand;
use App\Telegram\Webhook\Commands\FullReportCommand;
use App\Telegram\Webhook\Commands\RefundCommand;
use App\Telegram\Webhook\Commands\RemindCommand;
use App\Telegram\Webhook\Commands\StartCommand;
use App\Telegram\Webhook\Commands\SubscribeCommand;
use App\Telegram\Webhook\Other\Other;
use App\Telegram\Webhook\Text\Text;
use App\Telegram\Webhook\Voice\VoiceMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Realization
{
    protected const Commands = [
        '/start' => StartCommand::class,
        '/list' => ListCommand::class,
        '/report' => ReportCommand::class,
        '/fullreport' => FullReportCommand::class,
        '/delete' => DeleteCommand::class,
        '/delete_last' => DeleteLastCommand::class,
        '/edit' => EditCommand::class,
        '/remind' => RemindCommand::class,
        '/balance' => BalanceCommand::class,
        '/subscribe' => SubscribeCommand::class,
        '/refund' => RefundCommand::class,
    ];

    protected const ButtonCommands = [
        '🪙 Баланс' => BalanceCommand::class,
        '📋 Список операций' => ListCommand::class,
        '📅 Недельный отчет' => ReportCommand::class,
        '📊 Полный отчет' => FullReportCommand::class,
        '🗑️ Удалить последнюю' => DeleteLastCommand::class,
        '✏️ Редактировать' => EditCommand::class,
        '🔔 Напоминания' => RemindCommand::class,
        '💰 Подписка' => SubscribeCommand::class,
        '🚀 Старт' => StartCommand::class,
    ];

    public function take(Request $request)
    {
        $user = null;

        $from = $request->input('message.from') ?? $request->input('callback_query.from') ?? $request->input('edited_message.from');

        if ($from && isset($from['is_bot']) && $from['is_bot'] === true) {
            Log::info("Игнорируем событие от ботов:", ['bot_id' => $from['id']]);
            return false;
        }

        if ($request->has('pre_checkout_query')) {
            return '\App\Telegram\Webhook\Payment\PreCheckoutHandler';
        }

        if ($request->has('message.successful_payment')) {
            return '\App\Telegram\Webhook\Payment\SuccessfulPaymentHandler';
        }

        if ($request->input('message.from')) {
            $user = UserService::registerOrUpdate($request->input('message.from'));
        } elseif ($request->input('callback_query.from')) {
            $user = UserService::registerOrUpdate($request->input('callback_query.from'));
        } elseif ($request->input('edited_message.from')) {
            $user = UserService::registerOrUpdate($request->input('edited_message.from'));
        }

        if (isset($request->input('message')['entities'][0]['type']))
        {
            if ($request->input('message')['entities'][0]['type'] == 'bot_command') {
                $command_name = explode(' ', $request->input('message')['text'])[0];
                return self::Commands[$command_name] ?? false;
            }
        }
        elseif ($request->input('message.text') && isset(self::ButtonCommands[$request->input('message.text')]))
        {
            $buttonText = $request->input('message.text');
            return self::ButtonCommands[$buttonText];
        }
        elseif ($request->input('callback_query'))
        {
            $data = json_decode($request->input('callback_query')['data']);
            return '\App\Telegram\Webhook\Actions\\' . $data->action;
        }
        elseif ($request->input('message.voice'))
        {
            if (!$user || !UserService::hasAccess($user)) {
                return '\App\Telegram\Webhook\Actions\EndTarif';
            }
            return VoiceMessage::class;
        }
        elseif ($request->input('message'))
        {
            if (!$user || !UserService::hasAccess($user)) {
                return '\App\Telegram\Webhook\Actions\EndTarif';
            }
            return Text::class;
        }
        else
        {
            if (!$user || !UserService::hasAccess($user)) {
                return '\App\Telegram\Webhook\Actions\EndTarif';
            }
            return Other::class;
        }

        return false;
    }
}
