<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BakongService;
use chillerlan\QRCode\QRCode;

class BakongPaymentController extends Controller
{
    public function generatePayment(Order $order, BakongService $bakongService)
    {

        abort_unless(
            $order->customer_id === auth()->id(),
            403
        );
        abort_if(
            $order->payment()->value('md5_hash') !== null,
            422,
            'Payment QR code has already been generated.'
        );
        abort_if(
            $order->payment_status !== PaymentStatus::Pending,
            422,
            'Order is not pending payment.'
        );

        $amount = $order->total_amount;

        $qrData = $bakongService->generateQr($amount, 2);
        $order->payment()->update([
            'md5_hash' => $qrData['md5'],
        ]);

        $png = (new QRCode)->render($qrData['qr']);

        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, private');
    }

    public function verifyPayment(Order $order, BakongService $bakongService)
    {
        abort_unless(
            $order->customer_id === auth()->id(),
            403
        );

        abort_if(
            $order->payment_status !== PaymentStatus::Pending,
            422,
        );

        $md5 = $order->payment->md5_hash;

        $isPaid = $bakongService->checkPayment($md5);

        if ($isPaid['responseMessage'] == 'Success') {
            $order->update(['payment_status' => 'paid']);
            $order->payment()->update(['status' => 'paid']);
            $order->payment()->update(['qr_paid_at' => now()]);

            return response()->json(['message' => 'Payment is completed.']);
        } else {
            return response()->json(['message' => 'Payment is not completed.'], 422);
        }
    }
}
