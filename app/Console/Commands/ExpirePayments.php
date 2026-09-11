<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payments;
use App\Services\AbaPaymentService;
use App\Services\OrderStockService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Signature('expire-payments')]
#[Description('Verify expired ABA QR payments and release unpaid checkout reservations')]
class ExpirePayments extends Command
{
    public function handle(OrderStockService $stockService, AbaPaymentService $aba): int
    {
        $paymentIds = Payments::query()
            ->where('status', 'pending')
            ->whereNotNull('qr_expiration_at')
            ->where('qr_expiration_at', '<=', now())
            ->pluck('id');

        foreach ($paymentIds as $paymentId) {
            try {
                DB::transaction(function () use ($paymentId, $stockService, $aba): void {
                    $payment = Payments::query()->with('order.items')->lockForUpdate()->find($paymentId);

                    if ($payment === null || $payment->status !== 'pending' || $payment->qr_expiration_at->isFuture()) {
                        return;
                    }

                    if ($payment->aba_context !== null) {
                        $aba->verifyPayment($payment);

                        return;
                    }

                    $order = $payment->order()->lockForUpdate()->firstOrFail();

                    if ($order->payment_status !== PaymentStatus::Pending) {
                        return;
                    }

                    $stockService->release($order->load('items'));
                    $payment->update(['status' => 'expired']);
                    $order->update(['payment_status' => PaymentStatus::Expired]);
                });
            } catch (HttpException) {
                $this->warn("Payment {$paymentId} could not be confirmed with ABA; reserved stock was retained.");
            }
        }

        return self::SUCCESS;
    }
}
