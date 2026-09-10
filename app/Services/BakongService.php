<?php

namespace App\Services;

use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class BakongService
{
    protected BakongKHQR $bakong;

    public function __construct()
    {
        $this->bakong = new BakongKHQR(config('services.bakong.api_token'));
    }

    public function generateQr(float $amount, int $expirationMinutes): array
    {

        $individualInfo = new IndividualInfo(
            bakongAccountID: config('services.bakong.account_id'),
            merchantName: config('services.bakong.merchant_name'),
            merchantCity: config('services.bakong.merchant_city'),
            currency: KHQRData::CURRENCY_USD,
            amount: $amount,
            expirationTimestamp: strval(floor(microtime(true) * 1000) + $expirationMinutes * 60 * 1000),
            merchantCategoryCode: '5999' // optional, default value is 5999
        );

        $result = BakongKHQR::generateIndividual($individualInfo);

        return [
            'qr' => $result->data['qr'],
            'md5' => $result->data['md5'],
        ];
    }

    public function checkPayment(string $md5): mixed
    {
        return $this->bakong->checkTransactionByMD5($md5);
    }
}
