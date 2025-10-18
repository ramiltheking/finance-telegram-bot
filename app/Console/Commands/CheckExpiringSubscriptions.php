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

        $analysis = $this->analyzeUpcomingNotifications();
        $detailedAnalysis = $this->getDetailedAnalysis();

        $notifiedCount = $this->checkExpiringSubscriptions();
        $notifiedTrialsCount = $this->checkExpiringTrials();

        $this->info("Уведомлено {$notifiedCount} пользователей об истекающих подписках");
        $this->info("Уведомлено {$notifiedTrialsCount} пользователей об истекающих пробных периодах");

        $basicReport = $this->formatBasicReport($analysis, $notifiedCount, $notifiedTrialsCount);
        $detailedReport = $this->formatDetailedReport($detailedAnalysis, $notifiedCount, $notifiedTrialsCount);

        if (env('TELEGRAM_DEV_CHAT')) {
            Telegram::message(env('TELEGRAM_DEV_CHAT'), $basicReport)->send();
            if (Carbon::now()->dayOfWeek == 1) {
                Telegram::message(env('TELEGRAM_DEV_CHAT'), $detailedReport)->send();
            }
        }

        $this->outputConsoleAnalysis($analysis, $detailedAnalysis);

        Log::info('Анализ предстоящих уведомлений о подписках', [
            'basic_analysis' => $analysis,
            'detailed_analysis' => $detailedAnalysis,
            'notified_today' => [
                'subscriptions' => $notifiedCount,
                'trials' => $notifiedTrialsCount
            ]
        ]);

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

        $message = __('notifications.trial_expiring.title', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.motivation', [], $language) . "\n\n";
        $message .= __('notifications.trial_expiring.call_to_action', [], $language);

        $buttons = InlineButton::create()
            ->add(__('buttons.tariffs', [], $language), "Tarifs", [], 1)
            ->get();

        Telegram::inlineButtons($user->telegram_id, $message, $buttons)->send();
    }

    private function analyzeUpcomingNotifications(): array
    {
        $analysis = [
            'subscriptions' => [],
            'trials' => [],
            'summary' => []
        ];

        for ($i = 1; $i <= 7; $i++) {
            $date = Carbon::now()->addDays($i)->format('Y-m-d');

            $subscriptionCount = User::where('subscription_status', 'active')
                ->whereDate('subscription_ends_at', $date)
                ->whereNotNull('telegram_id')
                ->count();

            $trialCount = User::where('subscription_status', 'trial')
                ->whereDate('trial_ends_at', $date)
                ->whereNotNull('telegram_id')
                ->count();

            $analysis['subscriptions'][$date] = [
                'count' => $subscriptionCount,
                'period' => 'subscription',
                'days_until_expiry' => $i
            ];

            $analysis['trials'][$date] = [
                'count' => $trialCount,
                'period' => 'trial',
                'days_until_expiry' => $i
            ];
        }

        $analysis['summary'] = [
            'total_subscriptions_7days' => array_sum(array_column($analysis['subscriptions'], 'count')),
            'total_trials_7days' => array_sum(array_column($analysis['trials'], 'count')),
            'tomorrow_subscriptions' => $analysis['subscriptions'][Carbon::now()->addDay()->format('Y-m-d')]['count'] ?? 0,
            'tomorrow_trials' => $analysis['trials'][Carbon::now()->addDay()->format('Y-m-d')]['count'] ?? 0,
            'analysis_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'peak_subscription_day' => $this->findPeakDay($analysis['subscriptions']),
            'peak_trial_day' => $this->findPeakDay($analysis['trials'])
        ];

        return $analysis;
    }

    private function getDetailedAnalysis(): array
    {
        return [
            'subscriptions_30days' => $this->getSubscriptionAnalysis30Days(),
            'trials_30days' => $this->getTrialAnalysis30Days(),
            'user_segments' => $this->getUserSegmentsAnalysis(),
            'revenue_analysis' => $this->getRevenueAnalysis(),
            'retention_analysis' => $this->getRetentionAnalysis(),
            'analysis_period' => [
                'start_date' => Carbon::now()->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'generated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];
    }

    private function getSubscriptionAnalysis30Days(): array
    {
        $subscriptions = User::where('subscription_status', 'active')
            ->where('subscription_ends_at', '>', Carbon::now())
            ->where('subscription_ends_at', '<=', Carbon::now()->addDays(30))
            ->whereNotNull('telegram_id')
            ->selectRaw('DATE(subscription_ends_at) as expiry_date, COUNT(*) as user_count')
            ->groupBy('expiry_date')
            ->orderBy('expiry_date')
            ->get();

        $total = $subscriptions->sum('user_count');
        $averagePerDay = $total > 0 ? round($total / 30, 2) : 0;

        return [
            'data' => $subscriptions->toArray(),
            'stats' => [
                'total_expiring' => $total,
                'average_per_day' => $averagePerDay,
                'peak_day' => $subscriptions->sortByDesc('user_count')->first(),
                'days_with_expirations' => $subscriptions->count()
            ]
        ];
    }

    private function getTrialAnalysis30Days(): array
    {
        $trials = User::where('subscription_status', 'trial')
            ->where('trial_ends_at', '>', Carbon::now())
            ->where('trial_ends_at', '<=', Carbon::now()->addDays(30))
            ->whereNotNull('telegram_id')
            ->selectRaw('DATE(trial_ends_at) as expiry_date, COUNT(*) as user_count')
            ->groupBy('expiry_date')
            ->orderBy('expiry_date')
            ->get();

        $total = $trials->sum('user_count');
        $averagePerDay = $total > 0 ? round($total / 30, 2) : 0;

        return [
            'data' => $trials->toArray(),
            'stats' => [
                'total_expiring' => $total,
                'average_per_day' => $averagePerDay,
                'peak_day' => $trials->sortByDesc('user_count')->first(),
                'days_with_expirations' => $trials->count()
            ]
        ];
    }

    private function getUserSegmentsAnalysis(): array
    {
        $totalUsers = User::count();

        $segments = User::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN subscription_status = "active" THEN 1 ELSE 0 END) as active_subscriptions,
            SUM(CASE WHEN subscription_status = "trial" THEN 1 ELSE 0 END) as active_trials,
            SUM(CASE WHEN subscription_status = "expired" THEN 1 ELSE 0 END) as expired,
            SUM(CASE WHEN telegram_id IS NULL THEN 1 ELSE 0 END) as without_telegram
        ')->first();

        return [
            'total_users' => $totalUsers,
            'active_subscriptions' => $segments->active_subscriptions,
            'active_trials' => $segments->active_trials,
            'expired_subscriptions' => $segments->expired,
            'without_telegram' => $segments->without_telegram,
            'percentages' => [
                'active_subscriptions' => $totalUsers > 0 ? round(($segments->active_subscriptions / $totalUsers) * 100, 2) : 0,
                'active_trials' => $totalUsers > 0 ? round(($segments->active_trials / $totalUsers) * 100, 2) : 0,
                'expired' => $totalUsers > 0 ? round(($segments->expired / $totalUsers) * 100, 2) : 0,
                'engaged' => $totalUsers > 0 ? round((($segments->active_subscriptions + $segments->active_trials) / $totalUsers) * 100, 2) : 0
            ]
        ];
    }

    private function getRevenueAnalysis(): array
    {
        $subscriptionPrice = 250;

        $activeSubscriptions = User::where('subscription_status', 'active')->count();
        $potentialRevenue = $activeSubscriptions * $subscriptionPrice;

        $expiringSubscriptions = User::where('subscription_status', 'active')
            ->where('subscription_ends_at', '<=', Carbon::now()->addDays(7))
            ->count();

        $riskRevenue = $expiringSubscriptions * $subscriptionPrice;

        return [
            'current_mrr' => $potentialRevenue,
            'risk_revenue_7days' => $riskRevenue,
            'at_risk_percentage' => $activeSubscriptions > 0 ? round(($expiringSubscriptions / $activeSubscriptions) * 100, 2) : 0,
            'subscription_price' => $subscriptionPrice,
            'active_subscribers' => $activeSubscriptions
        ];
    }

    private function getRetentionAnalysis(): array
    {
        $recentlyExpired = User::where('subscription_status', 'expired')
            ->where('subscription_ends_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $convertedFromTrial = User::where('subscription_status', 'active')
            ->whereNotNull('trial_ends_at')
            ->count();

        $totalTrials = User::where('subscription_status', 'trial')->count();
        $conversionRate = $totalTrials > 0 ? round(($convertedFromTrial / $totalTrials) * 100, 2) : 0;

        return [
            'recently_expired' => $recentlyExpired,
            'converted_from_trial' => $convertedFromTrial,
            'conversion_rate' => $conversionRate,
            'churn_rate_30days' => $this->calculateChurnRate()
        ];
    }

    private function calculateChurnRate(): float
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();

        $subscribersStart = User::where('subscription_status', 'active')
            ->where('created_at', '<', $startOfMonth)
            ->count();

        $churned = User::where('subscription_status', 'expired')
            ->where('subscription_ends_at', '>=', $startOfMonth)
            ->count();

        return $subscribersStart > 0 ? round(($churned / $subscribersStart) * 100, 2) : 0;
    }

    private function formatBasicReport(array $analysis, int $notifiedSubscriptions, int $notifiedTrials): string
    {
        $summary = $analysis['summary'] ?? [];

        $report = "📊 Ежедневный отчет по подпискам\n";
        $report .= "Дата: " . ($summary['analysis_date'] ?? Carbon::now()->format('Y-m-d H:i:s')) . "\n\n";

        $report .= "✅ Сегодня отправлено:\n";
        $report .= "   • Подписки: {$notifiedSubscriptions}\n";
        $report .= "   • Пробные периоды: {$notifiedTrials}\n\n";

        $report .= "📅 Предстоящие уведомления (7 дней):\n";

        $hasSubscriptions = false;
        foreach (($analysis['subscriptions'] ?? []) as $date => $data) {
            if ($data['count'] > 0) {
                $hasSubscriptions = true;
                break;
            }
        }

        if ($hasSubscriptions) {
            $report .= "🔹 Подписки:\n";
            foreach (($analysis['subscriptions'] ?? []) as $date => $data) {
                if ($data['count'] > 0) {
                    $report .= "   • {$date} (+{$data['days_until_expiry']} дн.): {$data['count']} чел.\n";
                }
            }
        } else {
            $report .= "🔹 Подписки: нет предстоящих уведомлений\n";
        }

        $hasTrials = false;
        foreach (($analysis['trials'] ?? []) as $date => $data) {
            if ($data['count'] > 0) {
                $hasTrials = true;
                break;
            }
        }

        if ($hasTrials) {
            $report .= "🔸 Пробные периоды:\n";
            foreach (($analysis['trials'] ?? []) as $date => $data) {
                if ($data['count'] > 0) {
                    $report .= "   • {$date} (+{$data['days_until_expiry']} дн.): {$data['count']} чел.\n";
                }
            }
        } else {
            $report .= "🔸 Пробные периоды: нет предстоящих уведомлений\n";
        }

        $report .= "\n📈 Сводка на 7 дней:\n";
        $report .= "   • Всего подписок: " . ($summary['total_subscriptions_7days'] ?? 0) . "\n";
        $report .= "   • Всего пробных периодов: " . ($summary['total_trials_7days'] ?? 0) . "\n";
        $report .= "   • Завтра подписок: " . ($summary['tomorrow_subscriptions'] ?? 0) . "\n";
        $report .= "   • Завтра пробных периодов: " . ($summary['tomorrow_trials'] ?? 0) . "\n";

        return $report;
    }

    private function formatDetailedReport(array $detailedAnalysis, int $notifiedSubscriptions, int $notifiedTrials): string
    {
        $report = "📈 ДЕТАЛЬНЫЙ АНАЛИЗ ПОДПИСОК\n";
        $report .= "Период анализа: " . ($detailedAnalysis['analysis_period']['start_date'] ?? 'N/A') . " - " . ($detailedAnalysis['analysis_period']['end_date'] ?? 'N/A') . "\n\n";

        $segments = $detailedAnalysis['user_segments'] ?? [];
        $report .= "👥 СТАТИСТИКА ПОЛЬЗОВАТЕЛЕЙ:\n";
        $report .= "   • Всего пользователей: " . ($segments['total_users'] ?? 0) . "\n";
        $report .= "   • Активные подписки: " . ($segments['active_subscriptions'] ?? 0) . " (" . ($segments['percentages']['active_subscriptions'] ?? 0) . "%)\n";
        $report .= "   • Пробные периоды: " . ($segments['active_trials'] ?? 0) . " (" . ($segments['percentages']['active_trials'] ?? 0) . "%)\n";
        $report .= "   • Истекшие подписки: " . ($segments['expired_subscriptions'] ?? 0) . " (" . ($segments['percentages']['expired'] ?? 0) . "%)\n";
        $report .= "   • Вовлеченные пользователи: " . ($segments['percentages']['engaged'] ?? 0) . "%\n\n";

        $revenue = $detailedAnalysis['revenue_analysis'] ?? [];
        $report .= "💰 АНАЛИЗ ДОХОДОВ:\n";
        $report .= "   • Текущий MRR: " . ($revenue['current_mrr'] ?? 0) . " у.е.\n";
        $report .= "   • Риск потери (7 дней): " . ($revenue['risk_revenue_7days'] ?? 0) . " у.е.\n";
        $report .= "   • Подписчиков в зоне риска: " . ($revenue['at_risk_percentage'] ?? 0) . "%\n\n";

        $retention = $detailedAnalysis['retention_analysis'] ?? [];
        $report .= "📊 УДЕРЖАНИЕ И КОНВЕРСИЯ:\n";
        $report .= "   • Конверсия из триала: " . ($retention['conversion_rate'] ?? 0) . "%\n";
        $report .= "   • Отток за 30 дней: " . ($retention['churn_rate_30days'] ?? 0) . "%\n";
        $report .= "   • Недавно истекших: " . ($retention['recently_expired'] ?? 0) . "\n\n";

        $subs30d = $detailedAnalysis['subscriptions_30days'] ?? [];
        $trials30d = $detailedAnalysis['trials_30days'] ?? [];
        $report .= "📅 30-ДНЕВНЫЙ ПРОГНОЗ:\n";
        $report .= "   • Подписок истекает: " . ($subs30d['stats']['total_expiring'] ?? 0) . " (" . ($subs30d['stats']['average_per_day'] ?? 0) . "/день)\n";
        $report .= "   • Триалов истекает: " . ($trials30d['stats']['total_expiring'] ?? 0) . " (" . ($trials30d['stats']['average_per_day'] ?? 0) . "/день)\n";

        return $report;
    }

    private function outputConsoleAnalysis(array $analysis, array $detailedAnalysis): void
    {
        $this->info("\n📊 ОСНОВНОЙ АНАЛИЗ (7 дней):");
        $this->info("Дата анализа: " . ($analysis['summary']['analysis_date'] ?? Carbon::now()->format('Y-m-d H:i:s')));

        $this->info("\n🔹 Подписки:");
        foreach (($analysis['subscriptions'] ?? []) as $date => $data) {
            if ($data['count'] > 0) {
                $this->info("   {$date} (+{$data['days_until_expiry']} дн.): {$data['count']} чел.");
            }
        }

        $this->info("\n🔸 Пробные периоды:");
        foreach (($analysis['trials'] ?? []) as $date => $data) {
            if ($data['count'] > 0) {
                $this->info("   {$date} (+{$data['days_until_expiry']} дн.): {$data['count']} чел.");
            }
        }

        $segments = $detailedAnalysis['user_segments'] ?? [];
        $this->info("\n📈 ДЕТАЛЬНАЯ СТАТИСТИКА:");
        $this->info("   Всего пользователей: " . ($segments['total_users'] ?? 0));
        $this->info("   Активные подписки: " . ($segments['active_subscriptions'] ?? 0) . " (" . ($segments['percentages']['active_subscriptions'] ?? 0) . "%)");
        $this->info("   Пробные периоды: " . ($segments['active_trials'] ?? 0) . " (" . ($segments['percentages']['active_trials'] ?? 0) . "%)");

        $revenue = $detailedAnalysis['revenue_analysis'] ?? [];
        $this->info("   Текущий MRR: " . ($revenue['current_mrr'] ?? 0) . " у.е.");
        $this->info("   Риск потери (7 дней): " . ($revenue['risk_revenue_7days'] ?? 0) . " у.е.");
    }

    private function findPeakDay(array $data): array
    {
        if (empty($data)) {
            return ['date' => null, 'count' => 0];
        }

        $peakDate = null;
        $peakCount = 0;

        foreach ($data as $date => $item) {
            if ($item['count'] > $peakCount) {
                $peakCount = $item['count'];
                $peakDate = $date;
            }
        }

        return [
            'date' => $peakDate,
            'count' => $peakCount,
            'days_until' => $data[$peakDate]['days_until_expiry'] ?? 0
        ];
    }
}
