<?php

namespace App\Console\Commands;

use App\Services\RecurringPaymentService;
use Illuminate\Console\Command;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'payments:recurring';
    protected $description = 'Обработка рекуррентных платежей для активных подписок';

    public function handle()
    {
        $this->info('Запуск обработки рекуррентных платежей...');

        $service = new RecurringPaymentService();
        $result = $service->processDuePayments();

        $this->info("Обработано: {$result['processed']}, Успешно: {$result['successful']}");

        return Command::SUCCESS;
    }
}
