<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Enums\ShippingStatus;
use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected ?ShippingStatus $originalShippingStatus = null;

    protected array $originalQuantities = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->originalShippingStatus = $this->record->shipping_status;
        $this->originalQuantities = $this->record->items->pluck('quantity', 'id')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->originalShippingStatus !== ShippingStatus::Delivered && $this->record->shipping_status === ShippingStatus::Delivered) {
            $this->increaseStock();
        } elseif ($this->originalShippingStatus === ShippingStatus::Delivered && $this->record->shipping_status !== ShippingStatus::Delivered) {
            $this->decreaseStock();
        }
    }

    protected function increaseStock(): void
    {
        $this->record->items->each(function ($item) {
            $item->variant?->increment('stock_qty', $item->quantity);
        });
    }

    protected function decreaseStock(): void
    {
        $this->record->items->each(function ($item) {
            $item->variant?->decrement('stock_qty', $item->quantity);
        });
    }

      

    // in EditPurchase.php
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->map(function ($item) {
            return [
                'product_variant_id' => $item->product_variant_id,
                'unit_id' => $item->unit_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'sub_total' => $item->sub_total,
                'shipping_cost' => $item->shipping_cost,
            ];
        })->toArray();

        return $data;

    }
}
