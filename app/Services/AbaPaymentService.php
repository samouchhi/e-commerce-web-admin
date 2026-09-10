<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payments;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AbaPaymentService
{
    private const GENERATE_ENDPOINT = 'https://pwapp.ababank.com/api/pw-app/v1/payment/gateway/list-payment-options';

    private const STATUS_ENDPOINT = 'https://pwapp.ababank.com/api/pw-app/v1/payment-link/check-payment-status';

    public function __construct(private OrderStockService $stockService) {}

    public function generatePayment(Order $order): Payments
    {
        return DB::transaction(function () use ($order): Payments {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            abort_unless($order->payment_status === PaymentStatus::Pending && $payment->status === 'pending', 422, 'Order is not pending payment.');
            abort_if($payment->md5_hash !== null, 409, 'This order already has a Bakong payment. Verify that payment first.');

            if ($payment->qr_string !== null) {
                abort_if($payment->qr_expiration_at->isPast(), 409, 'QR expired. Verify this payment before creating another order.');

                return $payment;
            }

            abort_if($payment->qr_expiration_at?->isPast(), 409, 'The checkout reservation has expired.');
            abort_unless($payment->currency === 'USD', 422, 'ABA payment links currently support USD orders only.');

            $result = $this->generateQr((string) $order->total_amount);

            $payment->update([
                'amount' => $order->total_amount,
                'qr_string' => $result['qr_string'],
                'aba_transaction_id' => $result['transaction_id'],
                'aba_context' => $result['context'],
                'qr_expiration_at' => now()->addSeconds($result['expire_in_sec']),
            ]);

            return $payment;
        });
    }

    /**
     * @return array{qr_string: string, transaction_id: ?string, expire_in_sec: int, context: array<string, string>}
     */
    public function generateQr(string $amount): array
    {
        abort_unless(preg_match('/^\d{1,6}(?:\.\d{1,2})?$/D', $amount) && (float) $amount >= 0.01 && (float) $amount <= 100000, 422, 'Payment amount must be between 0.01 and 100000 USD.');
        $amount = number_format((float) $amount, 2, '.', '');
        $link = (string) config('services.aba.payment_link');
        abort_unless(
            preg_match('~^https://link\.payway\.com\.kh/[A-Za-z0-9_-]+$~D', $link),
            503,
            'Configure a valid ABA_PAYMENT_LINK before accepting payments.',
        );

        $http = $this->http($link)->withOptions(['cookies' => new CookieJar]);

        try {
            $page = $http->get($link);
            abort_unless($page->successful(), 502, 'ABA payment page is unavailable. Please try again.');

            $marker = strpos($page->body(), 'window.__NUXT__=');
            abort_if($marker === false, 502, 'ABA payment page format has changed.');
            $state = explode('</script>', substr($page->body(), $marker), 2)[0];
            $abaData = $this->pageField($state, '\b\w+\.aba_data\s*=\s*');
            $requestTime = $this->pageField($state, '\brequest_time\s*:\s*');
            $currency = $this->pageField($state, '\bcurrency\s*:\s*');
            abort_unless($currency === 'USD' && preg_match('/^\d{14}$/D', $requestTime) && $abaData !== '', 502, 'ABA returned invalid payment page data.');

            $additional = json_encode(['amount' => $amount], JSON_THROW_ON_ERROR);
            $response = $http->post(self::GENERATE_ENDPOINT, [
                'additional_fields' => $additional,
                'request_time' => $requestTime,
                'aba_data' => $abaData,
                'hash' => hash('sha512', $requestTime.$abaData.$additional),
            ]);
        } catch (ConnectionException) {
            abort(502, 'ABA is unavailable. Payment has not been confirmed.');
        }

        $result = $response->json();
        abort_unless($response->successful() && is_array($result) && (string) data_get($result, 'status.code') === '00', 502, 'ABA could not generate a payment QR.');

        foreach (['qr_string', 'client_id', 'token'] as $field) {
            abort_unless(is_string($result[$field] ?? null) && $result[$field] !== '', 502, 'ABA returned incomplete payment details.');
        }

        $details = data_get($result, 'transaction_summary.order_details');
        $returnedAmount = data_get($details, 'amount');
        abort_unless(
            data_get($details, 'currency') === 'USD'
            && is_numeric($returnedAmount)
            && (float) $returnedAmount === (float) $amount,
            502,
            'ABA returned a different amount or currency.',
        );

        $expiry = filter_var($result['expire_in_sec'] ?? null, FILTER_VALIDATE_INT);
        abort_unless($expiry !== false && $expiry > 0 && $expiry <= 86400, 502, 'ABA returned an invalid QR expiry.');

        return [
            'qr_string' => $result['qr_string'],
            'transaction_id' => isset($result['status']['tran_id']) ? (string) $result['status']['tran_id'] : null,
            'expire_in_sec' => $expiry,
            'context' => [
                'client_id' => $result['client_id'],
                'token' => $result['token'],
                'request_time' => $requestTime,
                'device_id' => Str::random(10),
                'payment_link' => $link,
            ],
        ];
    }

    /**
     * @param  array<string, string>  $context
     */
    public function checkPayment(array $context): string
    {
        foreach (['client_id', 'device_id', 'request_time', 'token', 'payment_link'] as $key) {
            abort_unless(is_string($context[$key] ?? null) && $context[$key] !== '', 502, 'ABA payment verification context is missing.');
        }

        try {
            $response = $this->http($context['payment_link'])
                ->timeout(15)
                ->withHeaders(['token' => $context['token']])
                ->post(self::STATUS_ENDPOINT, [
                    'client_id' => $context['client_id'],
                    'device_id' => $context['device_id'],
                    'request_time' => $context['request_time'],
                    'hash' => hash('sha512', $context['client_id'].$context['device_id'].$context['request_time']),
                ]);
        } catch (ConnectionException) {
            abort(502, 'ABA verification is unavailable. Payment remains unconfirmed.');
        }

        $result = $response->json();
        $action = data_get($result, 'data.action');
        abort_unless(
            $response->successful()
            && (string) data_get($result, 'status.code') === '00'
            && is_string($action),
            502,
            'ABA verification is unavailable. Payment remains unconfirmed.',
        );

        $action = strtolower($action);
        abort_unless(in_array($action, ['approved', 'request_qr', 'scanned', 'rqpay', 'processing-payment'], true), 502, 'ABA returned an unrecognized status. Payment remains unconfirmed.');

        return $action;
    }

    public function verifyPayment(Payments $payment): Payments
    {
        return DB::transaction(function () use ($payment): Payments {
            $payment = Payments::query()->lockForUpdate()->findOrFail($payment->id);
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            if ($payment->status !== 'pending' || $order->payment_status !== PaymentStatus::Pending) {
                return $payment;
            }

            abort_unless($payment->aba_context !== null, 409, 'An ABA payment QR has not been generated.');
            $action = $this->checkPayment($payment->aba_context);
            $payment->aba_last_action = $action;

            if ($action === 'approved') {
                $payment->status = 'paid';
                $payment->qr_paid_at = now();
                $order->update(['payment_status' => PaymentStatus::Paid]);
            } elseif (
                $action === 'request_qr'
                && $payment->qr_expiration_at?->copy()->addSeconds(120)->isPast()
            ) {
                // Allow a final server check and grace period before releasing reserved stock.
                $this->stockService->release($order->load('items'));
                $payment->status = 'expired';
                $order->update(['payment_status' => PaymentStatus::Expired]);
            }

            $payment->save();

            return $payment;
        });
    }

    private function http(string $link): PendingRequest
    {
        return Http::connectTimeout(10)
            ->timeout(30)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0',
                'Accept-Language' => 'en',
                'language' => 'en',
                'Origin' => 'https://link.payway.com.kh',
                'Referer' => $link,
            ])
            ->withOptions(['allow_redirects' => false]);
    }

    private function pageField(string $state, string $pattern): string
    {
        $matched = preg_match('~'.$pattern.'("(?:[^"\\\\]|\\\\.)*")~', $state, $matches);
        abort_unless($matched === 1, 502, 'ABA payment page format has changed.');
        $value = json_decode($matches[1]);
        abort_unless(is_string($value), 502, 'ABA payment page returned invalid data.');

        return $value;
    }
}
