<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'customer_name' => $this->customer->name,
            'logistic' => $this->logistic?->name,
            'total_amount' => $this->total_amount,
            'subtotal_amount' => $this->subtotal_amount,
            'shipping_cost' => $this->shipping_cost,
            'payment_status' => $this->payment_status,
            'shipping_status' => $this->shipping_status,
            'items' => $this->items->map(function ($item) {
                return [
                    // 'id' => $item->id,
                    'name' => $item->productVariant->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
