<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payments;
use App\Services\OrderStockService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('expire-payments')]
#[Description('Expire unpaid QR payments and release reserved stock')]
class ExpirePayments extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OrderStockService $stockService): int
    {
        $payments = Payments::query()
            ->where('status', 'pending')
            ->whereNotNull('qr_expiration_at')
            ->where('qr_expiration_at', '<=', now())
            ->pluck('id');

        foreach ($payments as $paymentId) {
            DB::transaction(function () use ($paymentId, $stockService): void {
                $payment = Payments::query()
                    ->with('order.items')
                    ->lockForUpdate()
                    ->find($paymentId);

                if ($payment === null || $payment->status !== 'pending') {
                    return;
                }

                $order = $payment->order;
                $stockService->release($order);

                Payments::query()
                    ->whereKey($paymentId)
                    ->update([
                        'status' => 'expired',
                    ]);

                $order->update([
                    'payment_status' => PaymentStatus::Expired,
                ]);
            });
        }

        return self::SUCCESS;
    }
}
