<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Facades\Telegram;
use App\Telegram\Helpers\InlineButton;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring';
    protected $description = 'Проверка и уведомление пользователей об истекающих подписках и пробных периодах';

    public function handle()
    {
        $this->info('Проверка истекающих подписок и пробных периодов...');

        $notifiedCount = $this->checkExpiringSubscriptions();
        $notifiedTrialsCount = $this->checkExpiringTrials();

        $this->info("Уведомлено {$notifiedCount} пользователей об истекающих подписках");
        $this->info("Уведомлено {$notifiedTrialsCount} пользователей об истекающих пробных периодах");

        return Command::SUCCESS;
    }

    private function checkExpiringSubscriptions(): int
    {
        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

        $expiringUsers = User::where('subscription_status', 'active')
            ->whereDate('subscription_ends_at', $tomorrow)
            ->whereNotNull('telegram_id')
            ->get();

        $notifiedCount = 0;

        foreach ($expiringUsers as $user) {
            try {
                $this->sendSubscriptionExpirationWarning($user);
                $notifiedCount++;

                Log::info('Отправлено предупреждение об истечении подписки', [
                    'user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'expires_at' => $user->subscription_ends_at
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка отправки предупреждения об истечении подписки', [
                    'user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $notifiedCount;
    }

    private function checkExpiringTrials(): int
    {
        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

        $expiringTrials = User::where('subscription_status', 'trial')
            ->whereDate('trial_ends_at', $tomorrow)
            ->whereNotNull('telegram_id')
            ->get();

        $notifiedCount = 0;

        foreach ($expiringTrials as $user) {
            try {
                $this->sendTrialExpirationWarning($user);
                $notifiedCount++;

                Log::info('Отправлено предупреждение об истечении пробного периода', [
                    'user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'expires_at' => $user->trial_ends_at
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка отправки предупреждения об истечении пробного периода', [
                    'user_id' => $user->id,
                    'telegram_id' => $user->telegram_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $notifiedCount;
    }

    private function sendSubscriptionExpirationWarning(User $user)
    {
        $language = $user->settings->language ?? 'ru';
        $expirationDate = $user->subscription_ends_at->format('d.m.Y');

        $message = __('notifications.subscription_expiring.title', [], $language) . "\n\n";
        $message .= __('notifications.subscription_expiring.message', ['date' => $expirationDate], $language) . "\n\n";
        $message .= __('notifications.subscription_expiring.features_lost', [], $language) . ":\n";
        $message .= __('notifications.features.unlimited_operations', [], $language) . "\n";
        $message .= __('notifications.features.personal_categories', [], $language) . "\n";
        $message .= __('notifications.features.analytics', [], $language) . "\n";
        $message .= __('notifications.features.voice', [], $language) . "\n";
        $message .= __('notifications.features.reminders', [], $language) . "\n";
        $message .= __('notifications.features.export', [], $language) . "\n\n";
        $message .= "💎 <b>" . __('notifications.subscription_expiring.renew_prompt', [], $language) . "</b>\n\n";
        $message .= __('notifications.choose_plan_prompt', [], $language);

        $buttons = InlineButton::create()
            ->add(__('buttons.tariffs', [], $language), "Tarifs", [], 1)
            ->get();

        Telegram::inlineButtons($user->telegram_id, $message, $buttons)->send();
    }

    private function sendTrialExpirationWarning(User $user)
    {
        $language = $user->settings->language ?? 'ru';
        $expirationDate = $user->trial_ends_at->format('d.m.Y');

        $message = __('notifications.trial_expiring.title', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.message', ['date' => $expirationDate], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.feedback', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.motivation', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.user_feedback', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.call_to_action', [], $language);

        $buttons = InlineButton::create()
            ->add(__('buttons.tariffs', [], $language), "Tarifs", [], 1)
            ->get();

        Telegram::inlineButtons($user->telegram_id, $message, $buttons)->send();
    }
}
