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
            Stat::make(__('Total Orders'), OrderResource::getModel()::count()),
            Stat::make(__('Paid Orders'), OrderResource::getModel()::where('payment_status', 'paid')->count()),
            Stat::make(__('Total Revenue'), '$'.number_format(OrderResource::getModel()::where('payment_status', 'paid')->sum('total_amount'), 2)),
        ];
    }
}
