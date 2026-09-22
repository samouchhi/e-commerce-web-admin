<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveCartRequest;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __invoke(ResolveCartRequest $request): JsonResponse
    {
        $items = $request->validated('items');
        $variants = ProductVariant::query()
            ->with('product.discounts')
            ->whereIn('id', array_column($items, 'product_variant_id'))
            ->get()
            ->keyBy('id');

        $resolvedItems = collect($items)->map(function (array $item) use ($variants): array {
            $variant = $variants->get($item['product_variant_id']);

            if ($variant === null) {
                return [
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => 0,
                    'available' => false,
                    'message' => 'This product was removed.',
                ];
            }

            if (! $variant->is_active || ! $variant->product->is_active) {
                return [
                    'product_variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'quantity' => 0,
                    'available' => false,
                    'message' => 'This product is no longer available.',
                ];
            }

            $quantity = min($item['quantity'], $variant->stock_qty);
            $unitPrice = $variant->product->discountedPriceFor($variant->price);

            return [
                'product_variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'name' => $variant->product->name,
                'variant_name' => $variant->name,
                'quantity' => $quantity,
                'stock_qty' => $variant->stock_qty,
                'available' => $quantity > 0,
                'original_unit_price' => number_format((float) $variant->price, 2, '.', ''),
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'line_total' => number_format($unitPrice * $quantity, 2, '.', ''),
                'message' => $quantity < $item['quantity']
                    ? 'Quantity was updated because stock changed.'
                    : null,
            ];
        });

        return response()->json([
            'items' => $resolvedItems,
            'can_checkout' => $resolvedItems->every(
                fn (array $item): bool => $item['available']
            ),
            'total' => number_format((float) $resolvedItems->sum('line_total'), 2, '.', ''),
        ]);
    }
}
