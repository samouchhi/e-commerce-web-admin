<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Enums\ShippingStatus;
use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderWidgets(): array
    {
        return PurchaseResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All')
                ->badge(static fn (): int => Purchase::query()->count())
                ->deferBadge(),
            'pending' => Tab::make()
                ->badge(static fn (): int => Purchase::query()->where('shipping_status', ShippingStatus::Pending)->count())
                ->badgeColor(ShippingStatus::Pending->getColor())
                ->deferBadge()
                ->query(fn ($query) => $query->where('shipping_status', ShippingStatus::Pending)),
            'shipped' => Tab::make()
                ->badge(static fn (): int => Purchase::query()->where('shipping_status', ShippingStatus::Shipped)->count())
                ->badgeColor(ShippingStatus::Shipped->getColor())
                ->deferBadge()
                ->query(fn ($query) => $query->where('shipping_status', ShippingStatus::Shipped)),
            'delivered' => Tab::make()
                ->badge(static fn (): int => Purchase::query()->where('shipping_status', ShippingStatus::Delivered)->count())
                ->badgeColor(ShippingStatus::Delivered->getColor())
                ->deferBadge()
                ->query(fn ($query) => $query->where('shipping_status', ShippingStatus::Delivered)),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make(),
        ];
    }
}
