<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the Telegram webhook for the bot';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = env('APP_URL') . '/api/webhook';

        if (!$token || !$url) {
            $this->error('TELEGRAM_BOT_TOKEN or APP_URL is not set in the .env file.');
            return Command::FAILURE;
        }

        $response = file_get_contents("https://api.telegram.org/bot{$token}/setWebhook?url={$url}");

        if ($response === false) {
            $this->error('Failed to connect to Telegram API.');
            return Command::FAILURE;
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['ok']) && $responseData['ok']) {
            $this->info('Webhook successfully registered.');
            return Command::SUCCESS;
        } else {
            $this->error('Failed to register webhook: ' . ($responseData['description'] ?? 'Unknown error'));
            return Command::FAILURE;
        }
    }
}
