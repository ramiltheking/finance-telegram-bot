<?php

namespace App\Telegram\Webhook\Actions;

use App\Telegram\Webhook\Commands\ReportCommand;
use App\Telegram\Webhook\Webhook;

class RedirectReportCommand extends Webhook
{
    public function run()
    {
        $command = new ReportCommand($this->request, $this->chat_id, $this->message_id);
        return $command->run();
    }
}
