<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|array|null $columns = ['default' => 2, '@3xl' => 4];

    protected function getStats(): array
    {
        $endDate = $this->pageFilters['end_date'] ?? now()->toDateString();
        $startDate = $this->pageFilters['start_date'] ?? Carbon::parse($endDate)->subDays(6)->toDateString();
        $allTime = ($this->pageFilters['date_range'] ?? null) === 'all_time';
        $orders = Order::query();

        if (! $allTime) {
            $orders->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $totalRevenue = (clone $orders)->where('payment_status', 'paid')->sum('total_amount');

        return [
            Stat::make('Total Revenue', '$'.number_format($totalRevenue, 2))
                ->icon('heroicon-o-banknotes')
                ->description($allTime ? 'Paid orders · All Time' : 'Paid orders in selected period'),
            Stat::make('Total Product', number_format(Product::query()->count()))
                ->icon('heroicon-o-cube')
                ->description('All products'),
            Stat::make('Total Orders', number_format($orders->count()))
                ->icon('heroicon-o-shopping-bag')
                ->description($allTime ? 'All statuses · All Time' : 'All statuses in selected period'),
            Stat::make('Total Customers', number_format(Customer::query()->count()))
                ->icon('heroicon-o-users')
                ->description('All customers'),
        ];
    }
}
