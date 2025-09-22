<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TarifsController extends Controller
{
    public function index(Request $request)
    {
        $priceInKZT = '2500.00';
        $tariffName = "Стандартный";
        $login = env('ROBOKASSA_MERCHANT_LOGIN');
        $pass1 = env('ROBOKASSA_PASSWORD1');

        $userId = Auth::user()->telegram_id;

        $existingPayment = Payment::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->latest()
            ->first();

        if ($existingPayment) {
            $invId = $existingPayment->inv_id;
            $signature = PaymentService::makeSignature($login, $priceInKZT, (string)$invId, $pass1);
            $url = PaymentService::buildRobokassaUrl(
                $login,
                $priceInKZT,
                (string)$invId,
                'Тариф использования бота VoiceFinance',
                $signature
            );
        } else {
            $invId = random_int(100000, 99999999);
            $signature = PaymentService::makeSignature($login, $priceInKZT, (string)$invId, $pass1);
            $url = PaymentService::buildRobokassaUrl(
                $login,
                $priceInKZT,
                (string)$invId,
                'Тариф использования бота VoiceFinance',
                $signature
            );

            Payment::create([
                'user_id' => $userId,
                'inv_id'  => $invId,
                'amount'  => $priceInKZT,
                'status'  => 'pending',
            ]);
        }

        return view('miniapp.tarif', [
            'tariffName' => $tariffName,
            'price'      => $priceInKZT,
            'url'        => $url,
        ]);
    }
}
