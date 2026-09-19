<?php

namespace App\Filament\Resources\Purchases\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Purchases', \App\Filament\Resources\Purchases\PurchaseResource::getModel()::count()),
            Stat::make('Total Items', \App\Filament\Resources\Purchases\PurchaseResource::getModel()::with('items')->get()->sum(fn($purchase) => $purchase->items->sum('quantity'))),
            Stat::make('Total Cost', '$' . number_format(\App\Filament\Resources\Purchases\PurchaseResource::getModel()::with('items')->get()->sum(fn($purchase) => $purchase->items->sum('sub_total')), 2)),
        ];
    }
}
