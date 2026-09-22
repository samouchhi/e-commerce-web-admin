<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class OrdersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected string $color = 'warning';

    protected ?string $heading = 'Orders';

    protected ?string $maxHeight = '240px';

    protected function getData(): array
    {
        $endDate = Carbon::parse($this->pageFilters['end_date'] ?? now())->toDateString();
        $startDate = Carbon::parse(
            $this->pageFilters['start_date'] ?? Carbon::parse($endDate)->subDays(6),
        )->toDateString();
        $productId = $this->pageFilters['product_id'] ?? null;
        $dates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn(Carbon $date): string => $date->toDateString());

        $orders = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->when(
                $productId,
                fn(Builder $query) => $query->whereHas(
                    'items.productVariant',
                    fn(Builder $query) => $query->where('product_id', $productId),
                ),
            )
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $ordersByDate = $orders->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $dates->map(fn(string $date): int => (int) ($ordersByDate[$date] ?? 0))->all(),
                ],
            ],
            'labels' => $dates->map(fn(string $date): string => Carbon::parse($date)->format('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
