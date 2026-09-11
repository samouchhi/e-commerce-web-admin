<?php

namespace App\Http\Resources;

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
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded(
                'category',
                fn(): array => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ]
            ),
            'unit' => $this->whenLoaded('unit', fn(): array => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
            ]),
            'variants' => $this->whenLoaded(
                'variants',
                fn(): array => $this->variants
                    ->map(fn($variant): array => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => $variant->price,
                        'cost' => $variant->cost,
                        'stock_qty' => $variant->stock_qty,
                        'is_active' => $variant->is_active,
                    ])
                    ->all()
            ),
            'images' => $this->whenLoaded('images', fn(): array => $this->images->map(fn($image): array => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => Storage::disk('public')->url($image->image_path),
                'sort_order' => $image->sort_order,
            ])->toArray()),

        ];
    }
}
