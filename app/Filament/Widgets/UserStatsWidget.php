<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Customers', Customer::count())
                ->description('Total Customers')
                ->descriptionIcon('heroicon-s-users')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('success'),
            Stat::make('Products', Product::count())
                ->description('Total Products')
                ->descriptionIcon('heroicon-s-shopping-bag')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('primary'),
            Stat::make('Orders', Order::count())
                ->description('Total Orders')
                ->descriptionIcon('heroicon-s-shopping-cart')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('warning'),
            Stat::make('Total Revenue', '$' . number_format(Order::where('payment_status', 'paid')->sum('total_amount'), 2))
                ->description('Total Revenue')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('success'),


        ];
    }
}
