<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Payments;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AbaPaymentService
{
    private const GENERATE_ENDPOINT = 'https://pwapp.ababank.com/api/pw-app/v1/payment/gateway/list-payment-options';

    private const STATUS_ENDPOINT = 'https://pwapp.ababank.com/api/pw-app/v1/payment-link/check-payment-status';

    private const PAYMENT_METHOD_ID = 1;

    private const GRACE_PERIOD_SECONDS = 120;

    /**
     * Payment actions ABA may return while the QR is still awaiting payment.
     */
    private const ALLOWED_ACTIONS = ['approved', 'request_qr', 'scanned', 'rqpay', 'processing-payment'];

    public function __construct(private OrderStockService $stockService) {}

    public function generatePayment(Order $order): Payments
    {
        return DB::transaction(function () use ($order): Payments {
            $payment = $order->payment()->lockForUpdate()->firstOrFail();
            $order = $payment->order()->lockForUpdate()->firstOrFail();

            abort_unless($order->payment_status === PaymentStatus::Pending && $payment->status === 'pending', 422, 'Order is not pending payment.');

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
        $amount = $this->normalizedAmount($amount);
        $link = $this->paymentLink();
        $http = $this->http($link)->withOptions(['cookies' => new CookieJar]);

        $state = $this->paymentPageState($this->fetchPaymentPage($http, $link));
        $result = $this->requestPaymentOptions($http, $amount, $state);

        return [
            'qr_string' => $result['qr_string'],
            'transaction_id' => isset($result['status']['tran_id']) ? (string) $result['status']['tran_id'] : null,
            'expire_in_sec' => $this->qrLifetime($result),
            'context' => [
                'client_id' => $result['client_id'],
                'token' => $result['token'],
                'request_time' => $state['request_time'],
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

        abort_unless(in_array($action, self::ALLOWED_ACTIONS, true), 502, 'ABA returned an unrecognized status. Payment remains unconfirmed.');

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
            } elseif ($action === 'request_qr' && $this->gracePeriodElapsed($payment)) {
                // Allow a final server check and grace period before releasing reserved stock.
                $this->stockService->release($order->load('items'));
                $payment->status = 'expired';
                $order->update(['payment_status' => PaymentStatus::Expired]);
            }

            $payment->save();

            return $payment;
        });
    }

    private function normalizedAmount(string $amount): string
    {
        abort_unless(
            preg_match('/^\d{1,6}(?:\.\d{1,2})?$/D', $amount) === 1
                && (float) $amount >= 0.01
                && (float) $amount <= 100000,
            422,
            'Payment amount must be between 0.01 and 100000 USD.',
        );

        return number_format((float) $amount, 2, '.', '');
    }

    private function paymentLink(): string
    {
        $link = (string) PaymentMethod::query()->whereKey(self::PAYMENT_METHOD_ID)->value('aba_payway_link');

        abort_unless(
            preg_match('~^https://link\.payway\.com\.kh/[A-Za-z0-9_-]+$~D', $link) === 1,
            503,
            'ABA payment unavailable. Please contact the site administrator.',
        );

        return $link;
    }

    private function fetchPaymentPage(PendingRequest $http, string $link): Response
    {
        try {
            $page = $http->get($link);
        } catch (ConnectionException) {
            abort(502, 'ABA is unavailable. Payment has not been confirmed.');
        }

        abort_unless($page->successful(), 502, 'ABA payment page is unavailable. Please try again.');

        return $page;
    }

    /**
     * @return array{aba_data: string, request_time: string}
     */
    private function paymentPageState(Response $page): array
    {
        abort_if(strpos($page->body(), 'window.__NUXT__=') === false, 502, 'ABA payment page format has changed.');

        $state = (string) str($page->body())->after('window.__NUXT__=')->before('</script>');

        $abaData = $this->pageField($state, '\b\w+\.aba_data\s*=\s*');
        $requestTime = $this->pageField($state, '\brequest_time\s*:\s*');
        $currency = $this->pageField($state, '\bcurrency\s*:\s*');

        abort_unless(
            $currency === 'USD'
                && preg_match('/^\d{14}$/D', $requestTime) === 1
                && $abaData !== '',
            502,
            'ABA returned invalid payment page data.',
        );

        return ['aba_data' => $abaData, 'request_time' => $requestTime];
    }

    /**
     * @param  array{aba_data: string, request_time: string}  $state
     * @return array<string, mixed>
     */
    private function requestPaymentOptions(PendingRequest $http, string $amount, array $state): array
    {
        $additional = json_encode(['amount' => $amount], JSON_THROW_ON_ERROR);

        try {
            $response = $http->post(self::GENERATE_ENDPOINT, [
                'additional_fields' => $additional,
                'request_time' => $state['request_time'],
                'aba_data' => $state['aba_data'],
                'hash' => hash('sha512', $state['request_time'].$state['aba_data'].$additional),
            ]);
        } catch (ConnectionException) {
            abort(502, 'ABA is unavailable. Payment has not been confirmed.');
        }

        $result = $response->json();

        abort_unless(
            $response->successful() && is_array($result) && (string) data_get($result, 'status.code') === '00',
            502,
            'ABA could not generate a payment QR.',
        );

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

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function qrLifetime(array $result): int
    {
        $expiry = filter_var($result['expire_in_sec'] ?? null, FILTER_VALIDATE_INT);

        abort_unless($expiry !== false && $expiry > 0 && $expiry <= 86400, 502, 'ABA returned an invalid QR expiry.');

        return $expiry;
    }

    private function gracePeriodElapsed(Payments $payment): bool
    {
        return (bool) $payment->qr_expiration_at?->copy()->addSeconds(self::GRACE_PERIOD_SECONDS)->isPast();
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
