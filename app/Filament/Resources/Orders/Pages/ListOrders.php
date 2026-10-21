<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderShippingStatus;
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

            // CreateAction::make(),
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
                ->badge(static fn(): int => Order::query()->where('payment_status', 'paid')->where('shipping_status', OrderShippingStatus::Pending)->count())
                ->badgeColor(OrderShippingStatus::Pending->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('payment_status', 'paid')->where('shipping_status', OrderShippingStatus::Pending)),
            'shipped' => Tab::make()
                ->badge(static fn(): int => Order::query()->where('shipping_status', OrderShippingStatus::Shipped)->count())
                ->badgeColor(OrderShippingStatus::Shipped->getColor())
                ->deferBadge()
                ->query(fn($query) => $query->where('shipping_status', OrderShippingStatus::Shipped)),
        ];
    }
}
