<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

class OrderStockService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function reserve(Order $order): void
    {
        $quantities = $order->items
            ->groupBy('product_variant_id')
            ->map(fn ($items): int => $items->sum('quantity'));

        foreach ($quantities->sortKeys() as $variantId => $quantity) {
            $variant = ProductVariant::query()->lockForUpdate()->findOrFail($variantId);

            if ($variant->stock_qty < $quantity) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for product variant {$variantId}.",
                ]);
            }

            $variant->decrement('stock_qty', $quantity);
        }
    }

    public function release(Order $order): void
    {
        foreach ($order->items->groupBy('product_variant_id')->sortKeys() as $variantId => $items) {
            $variant = ProductVariant::query()->lockForUpdate()->find($variantId);

            if ($variant !== null) {
                $variant->increment('stock_qty', $items->sum('quantity'));
            }
        }
    }
}
