<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\RecurringPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RobokassaController extends Controller
{
    public function result(Request $request)
    {
        $outSum = $request->input('OutSum');
        $invId = $request->input('InvId');
        $crc = strtoupper($request->input('SignatureValue'));
        $recurringToken = $request->input('RecurringToken');

        $password2 = env('ROBOKASSA_PASSWORD2');

        if ($recurringToken) {
            $myCrc = PaymentService::makeResultSignature($outSum, $invId, $password2, $recurringToken);
        } else {
            $myCrc = PaymentService::makeResultSignature($outSum, $invId, $password2);
        }

        if ($myCrc !== $crc) {
            Log::warning("Robokassa: invalid signature", $request->all());
            return response("bad sign", 400);
        }

        $payment = Payment::where('inv_id', $invId)->first();

        if (!$payment) {
            Log::warning("Robokassa: payment not found", $request->all());
            return response("payment not found", 400);
        }

        $paymentData = [
            'status' => 'completed',
        ];

        if ($recurringToken) {
            $paymentData['recurring_token'] = $recurringToken;
            $paymentData['is_recurring'] = true;
        }

        $payment->update($paymentData);

        if ($payment->user_id) {
            $user = User::where('telegram_id', $payment->user_id)->first();
            if ($user) {
                $this->processUserSubscription($user, $payment, $recurringToken);
            }
        }

        return response("OK$invId", 200);
    }

    private function processUserSubscription(User $user, Payment $payment, ?string $recurringToken)
    {
        $user->subscription_status = 'active';

        if ($user->subscription_ends_at && $user->subscription_ends_at->isFuture()) {
            $user->subscription_ends_at = $user->subscription_ends_at->addDays(30);
        } else {
            $user->subscription_started_at = now();
            $user->subscription_ends_at = now()->addDays(30);
        }

        $user->next_payment_date = $user->subscription_ends_at;

        if ($recurringToken) {
            $recurringService = new RecurringPaymentService();
            $recurringService->enableRecurringForUser($user, $recurringToken);
        }

        $user->save();
    }

    public function success(Request $request)
    {
        $invId = $request->input('InvId');
        $payment = Payment::where('inv_id', $invId)->first();
        $recurringToken = $request->input('RecurringToken');

        $showRecurringOffer = $recurringToken && !$payment->is_recurring;

        return view('miniapp.payment_success', [
            'payment' => $payment,
            'showRecurringOffer' => $showRecurringOffer,
            'recurringToken' => $recurringToken,
        ]);
    }

    public function enableRecurring(Request $request)
    {
        $user = Auth::user();
        $token = $request->input('token');

        $recurringService = new RecurringPaymentService();
        $result = $recurringService->enableRecurringForUser($user, $token);

        if ($result) {
            return response()->json(['success' => true, 'message' => 'Автопродление включено']);
        }

        return response()->json(['success' => false, 'message' => 'Ошибка включения автопродления'], 400);
    }

    public function fail(Request $request)
    {
        $invId = $request->input('InvId');
        $payment = Payment::where('inv_id', $invId)->first();

        if ($payment) {
            $payment->status = 'declined';
            $payment->save();
        }

        return view('miniapp.payment_fail', ['payment' => $payment]);
    }
}
