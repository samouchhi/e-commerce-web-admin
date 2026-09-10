<?php

namespace App\Filament\Resources\Orders\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', \App\Filament\Resources\Orders\OrderResource::getModel()::count()),
            Stat::make('Pending Orders', \App\Filament\Resources\Orders\OrderResource::getModel()::where('shipping_status', 'pending')->count()),
            Stat::make('Total Revenue', '$' . number_format(\App\Filament\Resources\Orders\OrderResource::getModel()::sum('total_amount'), 2)),
        ];
    }
}
