<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RobokassaController extends Controller
{
    public function result(Request $request)
    {
        $outSum  = $request->input('OutSum');
        $invId   = $request->input('InvId');
        $crc     = strtoupper($request->input('SignatureValue'));

        $password2 = env('ROBOKASSA_PASSWORD2');
        $myCrc = strtoupper(md5("$outSum:$invId:$password2"));

        if ($myCrc !== $crc) {
            Log::warning("Robokassa: invalid signature", $request->all());
            return response("bad sign", 400);
        }

        $payment = Payment::where('inv_id', $invId)->first();
        if ($payment->user_id) {
            $user = User::where('telegram_id', $payment->user_id)->first();
            if ($user) {
                $user->subscription_status = 'active';

                if ($user->subscription_ends_at && $user->subscription_ends_at->isFuture()) {
                    $user->subscription_ends_at = $user->subscription_ends_at->addDays(30);
                } else {
                    $user->subscription_started_at = now();
                    $user->subscription_ends_at = now()->addDays(30);
                }

                $user->save();
            }
        }

        return response("OK$invId", 200);
    }

    public function success(Request $request)
    {
        $invId = $request->input('InvId');
        $payment = Payment::where('inv_id', $invId)->first();

        return view('miniapp.payment_success', ['payment' => $payment]);
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
