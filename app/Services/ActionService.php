<?php

namespace App\Services;

use App\Facades\Telegram;
use App\Telegram\Webhook\Actions\Possibilities;
use App\Telegram\Webhook\Commands\FullReportCommand;
use App\Telegram\Webhook\Commands\ListCommand;
use App\Telegram\Webhook\Commands\BalanceCommand;
use App\Telegram\Webhook\Commands\ReportCommand;
use Illuminate\Http\Request;

class ActionService
{
    protected Request $request;
    protected $chat_id;

    public function __construct(Request $request, $chat_id)
    {
        $this->request = $request;
        $this->chat_id = $chat_id;
    }

    public function handle(string $action, array $parameters = [])
    {
        $methodName = 'handle' . str_replace('_', '', ucwords($action, '_'));

        if (method_exists($this, $methodName)) {
            return $this->$methodName($parameters);
        }

        return $this->handleUnknownAction($action);
    }

    protected function handleHelp(array $parameters = [])
    {
        $command = new Possibilities($this->request);
        return $command->run();
    }

    protected function handleGetOperationsGuide(array $parameters = [])
    {
        Telegram::message($this->chat_id, __('messages.ai_actions.guide'))->send();

        return ['handled_action' => 'get_operations_guide'];
    }

    protected function handleGetFullReport(array $parameters = [])
    {
        Telegram::message($this->chat_id, __('messages.ai_actions.processing_full_report'))->send();
        $command = new FullReportCommand($this->request);
        return $command->run();
    }

    protected function handleGetOperationsList(array $parameters = [])
    {
        Telegram::message($this->chat_id, __('messages.ai_actions.processing_operations_list'))->send();
        $command = new ListCommand($this->request);
        return $command->run();
    }

    protected function handleGetBalance(array $parameters = [])
    {
        Telegram::message($this->chat_id, __('messages.ai_actions.processing_balance'))->send();
        $command = new BalanceCommand($this->request);
        return $command->run();
    }

    protected function handleGetWeeklyReport(array $parameters = [])
    {
        Telegram::message($this->chat_id, __('messages.ai_actions.processing_weekly_report'))->send();
        $command = new ReportCommand($this->request);
        return $command->run();
    }

    protected function handleUnknownAction(string $action)
    {
        Telegram::message($this->chat_id, __('messages.unknown_request'))->send();
        return [
            'handled_action' => $action,
            'error' => 'unknown_action',
            'available_actions' => $this->getAvailableActions()
        ];
    }

    public static function getAvailableActions(): array
    {
        return [
            'help' => 'Show bot capabilities and help',
            'get_full_report' => 'Generate complete financial report with analytics',
            'get_operations_list' => 'Show transactions history and operations list',
            'get_balance' => 'Display current financial balance',
            'get_weekly_report' => 'Generate weekly financial analytics',
            'get_operations_guide' => 'Show how to add/edit/delete operations',
        ];
    }

    public static function actionExists(string $action): bool
    {
        $service = new self(app(Request::class), 0);
        $methodName = 'handle' . str_replace('_', '', ucwords($action, '_'));
        return method_exists($service, $methodName);
    }
}
