<?php

namespace App\Jobs;

use App\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $text;

    public function __construct($userId, $text)
    {
        $this->userId = $userId;
        $this->text = $text;
    }

    public function handle()
    {
        Telegram::message($this->userId, $this->text)->send();
    }
}

