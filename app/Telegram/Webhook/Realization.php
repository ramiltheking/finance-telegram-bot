<?php

namespace App\Telegram\Webhook;

use App\Facades\Telegram;
use App\Services\UserService;
use App\Telegram\Helpers\InlineButton;
use App\Telegram\Webhook\Commands\BalanceCommand;
use App\Telegram\Webhook\Commands\DeleteCommand;
use App\Telegram\Webhook\Commands\DeleteLastCommand;
use App\Telegram\Webhook\Commands\EditCommand;
use App\Telegram\Webhook\Commands\ListCommand;
use App\Telegram\Webhook\Commands\ReportCommand;
use App\Telegram\Webhook\Commands\FullReportCommand;
use App\Telegram\Webhook\Commands\RemindCommand;
use App\Telegram\Webhook\Commands\StartCommand;
use App\Telegram\Webhook\Other\Other;
use App\Telegram\Webhook\Text\Text;
use App\Telegram\Webhook\Voice\VoiceMessage;
use Illuminate\Http\Request;

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
    ];

    public function take(Request $request)
    {
        $user = null;

        if ($request->input('message.from'))
        {
            $user = UserService::registerOrUpdate($request->input('message.from'));
        }
        elseif ($request->input('callback_query.from'))
        {
            $user = UserService::registerOrUpdate($request->input('callback_query.from'));
        }
        elseif ($request->input('edited_message.from'))
        {
            $user = UserService::registerOrUpdate($request->input('edited_message.from'));
        }

        if (!$user || !UserService::hasAccess($user))
        {
            return '\App\Telegram\Webhook\Actions\EndTarif';
        }

        if (isset($request->input('message')['entities'][0]['type']))
        {
            if ($request->input('message')['entities'][0]['type'] == 'bot_command')
            {
                $command_name = explode(' ', $request->input('message')['text'])[0];
                return self::Commands[$command_name] ?? false;
            }
        }
        elseif($request->input('callback_query'))
        {
            $data = json_decode($request->input('callback_query')['data']);
            return '\App\Telegram\Webhook\Actions\\' . $data->action;
        }
        elseif ($request->input('message.voice'))
        {
            return VoiceMessage::class;
        }
        elseif($request->input('message'))
        {
            return Text::class;
        }
        else
        {
            return Other::class;
        }

        return false;
    }
}
