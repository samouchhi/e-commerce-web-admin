<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\ShippingStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All')
                ->badge(static fn(): int => Order::query()->count())
                ->deferBadge(),
            'pending' => Tab::make()
                ->badge(static fn(): int => Order::query()->where('shipping_status', ShippingStatus::Pending)->count())
                ->badgeColor(ShippingStatus::Pending->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('shipping_status', ShippingStatus::Pending)),
            'processing' => Tab::make()
                ->badge(static fn(): int => Order::query()->where('shipping_status', ShippingStatus::Processing)->count())
                ->badgeColor(ShippingStatus::Processing->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('shipping_status', ShippingStatus::Processing)),
            'shipped' => Tab::make()
                ->badge(static fn(): int => Order::query()->where('shipping_status', ShippingStatus::Shipped)->count())
                ->badgeColor(ShippingStatus::Shipped->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('shipping_status', ShippingStatus::Shipped)),
            'delivered' => Tab::make()
                ->badge(static fn(): int => Order::query()->where('shipping_status', ShippingStatus::Delivered)->count())
                ->badgeColor(ShippingStatus::Delivered->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('shipping_status', ShippingStatus::Delivered)),
        ];
    }
}
