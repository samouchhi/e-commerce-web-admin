<?php

use App\Services\AbaPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Schema::create('payment_methods', function (Blueprint $table) {
        $table->id();
        $table->string('aba_payway_link');
    });
    DB::table('payment_methods')->insert(['id' => 1, 'aba_payway_link' => 'https://link.payway.com.kh/test-link']);
    Http::preventStrayRequests();
    Http::fake([
        'https://link.payway.com.kh/test-link' => Http::response(
            '<script>window.__NUXT__={state:{request_time:"20261022174418",currency:"USD"}};q.aba_data="secret-page-data";</script>',
        ),
    ]);
});

test('generates a QR using the current ABA gateway and signed order amount', function () {
    Http::fake([
        'https://link.payway.com.kh/api/pw-app/v1/payment/gateway/list-payment-options' => Http::response([
            'status' => ['code' => '00', 'tran_id' => 'test-transaction'],
            'qr_string' => 'test-qr',
            'client_id' => 'test-client',
            'token' => 'secret-token',
            'expire_in_sec' => 180,
            'transaction_summary' => ['order_details' => ['amount' => '12.50', 'currency' => 'USD']],
        ]),
    ]);

    $result = app(AbaPaymentService::class)->generateQr('12.50');

    expect($result['qr_string'])->toBe('test-qr')
        ->and($result['expire_in_sec'])->toBe(180);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://link.payway.com.kh/api/pw-app/v1/payment/gateway/list-payment-options'
        && $request['additional_fields'] === '{"amount":"12.50"}'
        && $request['hash'] === hash('sha512', '20261022174418secret-page-data{"amount":"12.50"}'));
});

test('logs only safe diagnostics when ABA rejects QR generation', function (mixed $body, int $status, ?string $code, string $contentType) {
    Log::spy();
    Http::fake([
        'https://link.payway.com.kh/api/pw-app/v1/payment/gateway/list-payment-options' => Http::response($body, $status, ['Content-Type' => $contentType]),
    ]);

    expect(fn () => app(AbaPaymentService::class)->generateQr('12.50'))
        ->toThrow(HttpException::class, 'ABA could not generate a payment QR.');

    Log::shouldHaveReceived('warning')->once()->with('ABA payment QR generation failed.', [
        'http_status' => $status,
        'content_type' => $contentType,
        'aba_status_code' => $code,
    ]);
})->with([
    'provider rejection' => [['status' => ['code' => '07', 'message' => 'Invalid Data'], 'token' => 'secret-token'], 403, '07', 'application/json'],
    'invalid response' => ['<html>secret-upstream-body</html>', 502, null, 'text/html'],
]);

test('keeps unpaid QR verification on the working ABA status gateway', function () {
    Http::fake([
        'https://pwapp.ababank.com/api/pw-app/v1/payment-link/check-payment-status' => Http::response([
            'status' => ['code' => '00'],
            'data' => ['action' => 'request_qr'],
        ]),
    ]);

    $action = app(AbaPaymentService::class)->checkPayment([
        'client_id' => 'test-client',
        'device_id' => 'test-device',
        'request_time' => '20261022174418',
        'token' => 'secret-token',
        'payment_link' => 'https://link.payway.com.kh/test-link',
    ]);

    expect($action)->toBe('request_qr');
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://pwapp.ababank.com/api/pw-app/v1/payment-link/check-payment-status'
        && $request->hasHeader('token', 'secret-token'));
});
