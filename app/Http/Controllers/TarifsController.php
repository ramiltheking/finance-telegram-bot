<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class TarifsController extends Controller
{
    public function index(Request $request)
    {
        $priceInKZT = '2500.00';
        $tariffName = "Стандартный";
        $invId = random_int(100000, 99999999);
        $login = env('ROBOKASSA_MERCHANT_LOGIN');
        $pass1 = env('ROBOKASSA_PASSWORD1');

        $signature = PaymentService::makeSignature($login, $priceInKZT, (string)$invId, $pass1);
        $url = PaymentService::buildRobokassaUrl(
            $login,
            $priceInKZT,
            (string)$invId,
            'Тариф использования бота VoiceFinance',
            $signature
        );

        Payment::create([
            'user_id' => $request->query('telegram_id'),
            'inv_id'  => $invId,
            'amount'  => $priceInKZT,
            'status'  => 'pending',
        ]);

        return view('miniapp.tarif', [
            'tariffName' => $tariffName,
            'price'      => $priceInKZT,
            'url'        => $url,
        ]);
    }
}
