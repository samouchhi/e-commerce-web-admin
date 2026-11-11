<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 6];

    public function getHeading(): string
    {
        return __('Revenue over time');
    }

    protected string $view = 'filament.widgets.revenue-chart';

    public function getPeriodLabel(): string
    {
        if (($this->pageFilters['date_range'] ?? null) === 'all_time') {
            return __('All Time');
        }

        $end = Carbon::parse($this->pageFilters['end_date'] ?? now());
        $start = Carbon::parse($this->pageFilters['start_date'] ?? $end->copy()->subDays(6));

        return $start->translatedFormat('M j, Y').' – '.$end->translatedFormat('M j, Y');
    }

    public function getTotalRevenue(): string
    {
        return '$'.number_format(array_sum($this->getCachedData()['datasets'][0]['data']), 2);
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181b',
                        titleColor: '#d4d4d8',
                        bodyColor: '#ffffff',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (context) => new Intl.NumberFormat('en-US', {
                                style: 'currency', currency: 'USD'
                            }).format(context.parsed.y)
                        }
                    }
                },
                scales: {
                    x: {
                        border: { display: false },
                        grid: { display: false },
                        ticks: { maxRotation: 0, maxTicksLimit: 7, padding: 10, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false, dash: [4, 5] },
                        grid: { drawTicks: false },
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 12,
                            font: { size: 11 },
                            callback: (value) => '$' + new Intl.NumberFormat('en-US', {
                                notation: 'compact', maximumFractionDigits: 2
                            }).format(value)
                        }
                    }
                }
            }
            JS);
    }

    protected function getData(): array
    {
        $endDate = Carbon::parse($this->pageFilters['end_date'] ?? now())->toDateString();
        $startDate = Carbon::parse(
            $this->pageFilters['start_date'] ?? Carbon::parse($endDate)->subDays(6),
        )->toDateString();
        $allTime = ($this->pageFilters['date_range'] ?? null) === 'all_time';
        $orders = Order::query()->where('payment_status', 'paid');

        if (! $allTime) {
            $orders->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $revenueByDate = $orders
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('total', 'date');

        if ($allTime) {
            $startDate = $revenueByDate->keys()->first() ?? now()->toDateString();
            $endDate = max($revenueByDate->keys()->last() ?? now()->toDateString(), now()->toDateString());
        }

        $dates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn (Carbon $date): string => $date->toDateString());

        return [
            'datasets' => [[
                'label' => __('Paid revenue (USD)'),
                'backgroundColor' => '#7460f5',
                'hoverBackgroundColor' => '#5b43e8',
                'borderColor' => '#5b43e8',
                'borderWidth' => 1,
                'borderRadius' => 6,
                'borderSkipped' => false,
                'maxBarThickness' => 48,
                'categoryPercentage' => 0.72,
                'barPercentage' => 0.8,
                'data' => $dates->map(fn (string $date): float => (float) ($revenueByDate[$date] ?? 0))->all(),
            ]],
            'labels' => $dates->map(fn (string $date): string => Carbon::parse($date)->translatedFormat('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
