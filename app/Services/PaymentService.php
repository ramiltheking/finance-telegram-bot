<?php

namespace App\Services;

class PaymentService
{
    public static function makeSignature(string $login, string $outSum, string $invId, string $password1, ?string $recurringToken = null): string {
        if ($recurringToken) {
            return strtoupper(md5("{$login}:{$outSum}:{$invId}:{$recurringToken}:{$password1}"));
        }

        return strtoupper(md5("{$login}:{$outSum}:{$invId}:{$password1}"));
    }

    public static function buildRobokassaUrl(string $login, string $outSum, string $invId, string $invDesc, string $signature, bool $isRecurring = false): string {
        $params = [
            'MerchantLogin' => $login,
            'OutSum' => $outSum,
            'InvId' => $invId,
            'Description' => $invDesc,
            'SignatureValue' => $signature,
            'Culture' => 'ru',
            'IsTest' => env('ROBOKASSA_TEST_MODE', true) ? 1 : 0,
            'Encoding' => 'utf-8'
        ];

        if ($isRecurring) {
            $params['Recurring'] = 'true';
        }

        return "https://auth.robokassa.kz/Merchant/Index.aspx?" . http_build_query($params);
    }

    public static function makeResultSignature(string $outSum, string $invId, string $password2, ?string $recurringToken = null): string {
        if ($recurringToken) {
            return strtoupper(md5("{$outSum}:{$invId}:{$recurringToken}:{$password2}"));
        }

        return strtoupper(md5("{$outSum}:{$invId}:{$password2}"));
    }

    public static function initiateRecurringPayment(string $token, float $amount, string $description = ''): bool
    {
        $login = env('ROBOKASSA_MERCHANT_LOGIN');
        $password1 = env('ROBOKASSA_PASSWORD1');
        $invId = random_int(100000, 99999999);

        $signature = self::makeSignature($login, $amount, $invId, $password1, $token);

        $params = [
            'MerchantLogin' => $login,
            'OutSum' => $amount,
            'InvId' => $invId,
            'Description' => $description,
            'SignatureValue' => $signature,
            'PreviousInvoiceID' => $token,
        ];

        $url = "https://auth.robokassa.kz/Merchant/Recurring";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && strpos($response, 'OK') === 0;
    }
}
