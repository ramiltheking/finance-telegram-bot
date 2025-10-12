<?php

namespace App\Telegram\Webhook\Actions;

use App\Telegram\Webhook\Commands\FullReportCommand;
use App\Telegram\Webhook\Webhook;

class RedirectFullReportCommand extends Webhook
{
    public function run()
    {
        $command = new FullReportCommand($this->request, $this->chat_id, $this->message_id);
        return $command->run();
    }
}
