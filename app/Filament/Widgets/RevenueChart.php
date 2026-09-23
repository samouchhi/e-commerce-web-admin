<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItems;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 3];

    protected string $color = 'success';

    protected ?string $heading = 'Revenue';

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
            ->whereDate('created_at', '<=', $endDate);

        $revenue = $productId
            ? OrderItems::query()
            ->whereIn('order_id', $orders->select('id'))
            ->whereHas(
                'productVariant',
                fn(Builder $query) => $query->where('product_id', $productId),
            )
            ->selectRaw('DATE(created_at) as date, SUM(subtotal_price) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            : $orders
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        $revenueByDate = $revenue->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $dates->map(fn(string $date): float => (float) ($revenueByDate[$date] ?? 0))->all(),
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
