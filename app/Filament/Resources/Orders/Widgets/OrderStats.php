<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', OrderResource::getModel()::count()),
            Stat::make('Pending Orders', OrderResource::getModel()::where('shipping_status', 'pending')->count()),
            Stat::make('Total Revenue', '$' . number_format(OrderResource::getModel()::where('payment_status', 'paid')->sum('total_amount'), 2)),
        ];
    }
}
