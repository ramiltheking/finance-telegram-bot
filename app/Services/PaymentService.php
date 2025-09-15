<?php

namespace App\Services;

class PaymentService
{
    public static function makeSignature(string $login, string $outSum, string $invId, string $password1): string {
        return strtoupper(md5("{$login}:{$outSum}:{$invId}:{$password1}"));
    }

    public static function buildRobokassaUrl(string $login, string $outSum, string $invId, string $invDesc, string $signature): string {
        $params = http_build_query([
            'MerchantLogin' => $login,
            'OutSum' => $outSum,
            'InvId' => $invId,
            'Description' => $invDesc,
            'SignatureValue' => $signature,
            'Culture' => 'ru',
            'IsTest' => '1',
            'Recurring' => 'true',
            'Encoding' => 'utf-8'
        ]);
        return "https://auth.robokassa.kz/Merchant/Index.aspx?{$params}";
    }
}
