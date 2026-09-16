<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Enums\ShippingStatus;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Component;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    public function getWizardSubmitAction(): Action
    {
        return $this->getCreateFormAction();
    }

    public function getFormContentComponent(): Component
    {
        return parent::getFormContentComponent()->footer([]);
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    // $items = collect($data['items'] ?? []);
    // $firstVariantId = $items->first()['product_variant_id'] ?? null;
    // $firstVariant = $firstVariantId
    //     ? ProductVariant::query()->with('product:id,unit_id')->find($firstVariantId)
    //     : null;

    // $data['product_id'] = $firstVariant?->product_id;
    // $data['unit_id'] = $firstVariant?->product?->unit_id;
    // $data['quantity'] = (int) $items->sum(fn (array $item): int => (int) ($item['quantity'] ?? 0));
    // $data['total_price'] = $items->sum(fn (array $item): float => (float) ($item['subtotal'] ?? 0));
    // $data['purchase_status'] = $data['purchase_status'] ?? ($data['date'] ?? now()->toDateString());
    // $data['shipping_cost'] = (string) ($data['shipping_cost'] ?? '0');
    // $data['shipping_status'] = $data['shipping_status'] ?? 'pending';

    // return $data;
    // }

    protected function afterCreate(): void
    {
        if ($this->record->shipping_status === ShippingStatus::Delivered) {
            $this->increaseStock();
        }
    }

    protected function increaseStock(): void
    {
        $this->record->items->each(function ($item) {
            $item->variant?->increment('stock_qty', $item->quantity);
        });
    }
}
