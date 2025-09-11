<?php

namespace App\Telegram\Webhook;

use App\Services\UserService;
use App\Telegram\Webhook\Commands\ReportCommand;
use App\Telegram\Webhook\Commands\StartCommand;
use App\Telegram\Webhook\Text\Text;
use App\Telegram\Webhook\Voice\VoiceMessage;
use Illuminate\Http\Request;

class Realization
{
    protected const Commands = [
        '/start' => StartCommand::class,
        '/report' => ReportCommand::class,
    ];

    public function take(Request $request)
    {
        if ($request->input('message.from'))
        {
            UserService::registerOrUpdate($request->input('message.from'));
        } elseif ($request->input('callback_query.from'))
        {
            UserService::registerOrUpdate($request->input('callback_query.from'));
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

        return false;
    }
}
