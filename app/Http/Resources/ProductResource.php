<?php

namespace App\Http\Resources;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_code' => $this->product_code,
            'description' => $this->description,
            // 'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'is_best_seller' => (bool) $this->is_best_seller,
            'category' => $this->whenLoaded(
                'category',
                fn (): array => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ]
            ),
            'unit' => $this->whenLoaded('unit', fn (): array => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
            ]),
            'variants' => $this->whenLoaded(
                'variants',
                fn (): array => $this->variants
                    ->map(fn ($variant): array => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => $variant->price,
                        'discounted_price' => number_format($this->discountedPriceFor($variant->price), 2, '.', ''),
                        // 'cost' => $variant->cost,
                        'stock_qty' => $variant->stock_qty,
                        'is_active' => (bool) $variant->is_active,
                    ])
                    ->all()
            ),
            'discounts' => $this->whenLoaded(
                'discounts',
                fn (): array => $this->discounts
                    ->map(fn ($discount): array => [
                        'name' => $discount->name,
                        'description' => $discount->description,
                        'value' => $discount->value,
                        'type' => $discount->type,
                        'start_date' => $discount->start_date,
                        'end_date' => $discount->end_date,
                        'is_active' => (bool) $discount->is_active,
                        'is_current' => $discount->isCurrentlyActive(),
                    ])
                    ->all()
            ),

            'images' => $this->whenLoaded('images', fn (): array => $this->images->map(fn ($image): array => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => Storage::disk('public')->url($image->image_path),
                'sort_order' => $image->sort_order,
            ])->toArray()),

        ];
    }

    /**
     * Resolve the currently-running discount that saves the customer the most.
     */
    private function activeDiscountFor(float $price): ?Discount
    {
        if (! $this->resource->relationLoaded('discounts')) {
            return null;
        }

        return $this->discounts
            ->filter(fn (Discount $discount): bool => $discount->isCurrentlyActive())
            ->sortBy(fn (Discount $discount): float => $discount->priceAfterDiscount($price))
            ->first();
    }

    /**
     * Apply the best currently-running discount to the given price.
     */
    private function discountedPriceFor(float|string $price): float
    {
        $price = (float) $price;

        return $this->activeDiscountFor($price)?->priceAfterDiscount($price) ?? $price;
    }
}
