<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\GeneralSetting;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class OrderPrintController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Order $order): Response
    {
        Gate::authorize('view', $order);
        abort_unless(in_array($order->payment_status, [PaymentStatus::Paid, PaymentStatus::Pending, PaymentStatus::Expired], true), 404);

        $order->load(['customer', 'address', 'items.productVariant.product', 'payment', 'logistic']);
        $settings = GeneralSetting::query()->first();
        $logo = $settings?->site_logo;

        return response()->view('filament.orders.print', [
            'order' => $order,
            'settings' => $settings,
            'logoUrl' => $logo && Storage::disk('public')->exists($logo) ? Storage::disk('public')->url($logo) : null,
            'isReceipt' => $order->payment_status === PaymentStatus::Paid,
            'currency' => $order->payment?->currency ?: 'USD',
        ])->header('Cache-Control', 'private, no-store');
    }
}
