<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\AbaPaymentService;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbaPaymentController extends Controller
{
    public function __construct(private AbaPaymentService $aba) {}

    public function generatePayment(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user() instanceof Customer && (string) $order->customer_id === (string) $request->user()->getAuthIdentifier(), 403);
        $payment = $this->aba->generatePayment($order);
        $image = (new QRCode(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => true,
            'scale' => 8,
            'eccLevel' => EccLevel::H,
        ])))->render($payment->qr_string);

        return response()->json([
            'order_id' => $order->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'qr_image' => $image,
            'deeplink_url' => 'abamobilebank://ababank.com?'.http_build_query([
                'type' => 'payway',
                'qrcode' => $payment->qr_string,
            ], '', '&', PHP_QUERY_RFC3986),
            'expires_at' => $payment->qr_expiration_at->toIso8601String(),
            'status' => $payment->status,
        ], 200, [], JSON_UNESCAPED_SLASHES)->header('Cache-Control', 'no-store, private');
    }

    public function verifyPayment(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user() instanceof Customer && (string) $order->customer_id === (string) $request->user()->getAuthIdentifier(), 403);
        $payment = $this->aba->verifyPayment($order->payment()->firstOrFail());

        return response()->json([
            'order_id' => $order->id,
            'success' => $payment->status === 'paid',
            'status' => $payment->status,
            'action' => $payment->aba_last_action,
            'expires_at' => $payment->qr_expiration_at?->toIso8601String(),
            'paid_at' => $payment->qr_paid_at?->toIso8601String(),
            'message' => match ($payment->status) {
                'paid' => 'Payment is completed.',
                'expired' => 'Payment QR has expired.',
                default => 'Payment is not yet confirmed.',
            },
        ], 200, [], JSON_UNESCAPED_SLASHES)->header('Cache-Control', 'no-store, private');
    }

    private function merchantName(string $qr): ?string
    {
        for ($offset = 0; $offset + 4 <= strlen($qr);) {
            $tag = substr($qr, $offset, 2);
            $length = substr($qr, $offset + 2, 2);
            if (! ctype_digit($length) || $offset + 4 + (int) $length > strlen($qr)) {
                return null;
            }
            if ($tag === '59') {
                return substr($qr, $offset + 4, (int) $length);
            }
            $offset += 4 + (int) $length;
        }

        return null;
    }
}
