<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->description('Total Users')
                ->descriptionIcon('heroicon-s-users')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('success'),
            Stat::make('Products', Product::count())
                ->description('Total Products')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('primary'),

        ];
    }
}
