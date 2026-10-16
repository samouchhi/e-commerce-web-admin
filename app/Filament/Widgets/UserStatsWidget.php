<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItems;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class UserStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|array|null $columns = ['default' => 2, '@3xl' => 4];

    protected function getStats(): array
    {
        $endDate = $this->pageFilters['end_date'] ?? now()->toDateString();
        $startDate = $this->pageFilters['start_date'] ?? Carbon::parse($endDate)->subDays(6)->toDateString();
        $productId = $this->pageFilters['product_id'] ?? null;

        $orders = Order::query()
            ->where('payment_status', 'paid')
            ->when(
                $startDate,
                fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate),
            )
            ->when(
                $endDate,
                fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate),
            )
            ->when(
                $productId,
                fn(Builder $query) => $query->whereHas(
                    'items.productVariant',
                    fn(Builder $query) => $query->where('product_id', $productId),
                ),
            );

        $paidOrders = clone $orders;

        $productSold = OrderItems::query()
            ->whereIn('order_id', (clone $paidOrders)->select('orders.id'))
            ->when(
                $productId,
                fn(Builder $query) => $query->whereHas(
                    'productVariant',
                    fn(Builder $query) => $query->where('product_id', $productId),
                ),
            )
            ->sum('quantity');

        $paidOrderCount = (clone $paidOrders)->count();
        $totalRevenue = (clone $paidOrders)->sum('total_amount');

        if ($productId) {
            $totalRevenue = OrderItems::query()
                ->whereIn('order_id', (clone $paidOrders)->select('id'))
                ->whereHas(
                    'productVariant',
                    fn(Builder $query) => $query->where('product_id', $productId),
                )
                ->sum('subtotal_price');
        }

        $averageOrderValue = $paidOrderCount > 0 ? $totalRevenue / $paidOrderCount : 0;

        return [
            Stat::make(
                'Paid revenue',
                '$' . number_format($totalRevenue, 2),
            )
                ->icon('heroicon-o-banknotes')
                ->extraAttributes(['class' => 'dashboard-revenue-stat']),

            Stat::make('Paid orders', number_format($paidOrderCount))
                ->icon('heroicon-o-shopping-bag'),

            Stat::make(
                'Units sold',
                number_format($productSold)
            )
                ->icon('heroicon-o-cube'),

            Stat::make('Average order', '$' . number_format($averageOrderValue, 2))
                ->icon('heroicon-o-calculator'),

        ];
    }
}
