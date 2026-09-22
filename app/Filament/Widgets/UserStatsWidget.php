<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItems;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class UserStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['start_date'] ?? null;
        $endDate = $this->pageFilters['end_date'] ?? null;
        $productId = $this->pageFilters['product_id'] ?? null;

        $customers = Customer::query()
            ->when(
                $startDate,
                fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate),
            )
            ->when(
                $endDate,
                fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate),
            )
            ->when(
                $productId,
                fn (Builder $query) => $query->whereHas(
                    'orders.items.productVariant',
                    fn (Builder $query) => $query->where('product_id', $productId),
                ),
            );

        $orders = Order::query()
            ->where('payment_status', 'paid')
            ->when(
                $startDate,
                fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate),
            )
            ->when(
                $endDate,
                fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate),
            )
            ->when(
                $productId,
                fn (Builder $query) => $query->whereHas(
                    'items.productVariant',
                    fn (Builder $query) => $query->where('product_id', $productId),
                ),
            );

        $paidOrders = (clone $orders)
            ->where('payment_status', 'paid');

        $paidOrderCount = (clone $paidOrders)->count();
        $totalRevenue = (clone $paidOrders)->sum('total_amount');
        $averageOrderValue = (clone $paidOrders)->avg('total_amount') ?? 0;

        if ($productId) {
            $totalRevenue = OrderItems::query()
                ->whereIn('order_id', (clone $paidOrders)->select('id'))
                ->whereHas(
                    'productVariant',
                    fn (Builder $query) => $query->where('product_id', $productId),
                )
                ->sum('subtotal_price');
            $averageOrderValue = $paidOrderCount > 0 ? $totalRevenue / $paidOrderCount : 0;
        }

        return [
            Stat::make('Customers', $customers->count())
                ->descriptionIcon('heroicon-s-users')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('success'),

            Stat::make('Orders', $orders->count())
                ->descriptionIcon('heroicon-s-shopping-cart')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('warning'),

            Stat::make(
                'Total Revenue',
                '$'.number_format($totalRevenue, 2),
            )
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('success'),

            Stat::make(
                'Average Order Value',
                '$'.number_format($averageOrderValue, 2),
            )
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->chart([10, 25, 15, 30, 12, 15])
                ->color('primary'),
        ];
    }
}
