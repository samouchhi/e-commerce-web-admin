<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItems;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class OrderStatus extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 3];

    protected string $view = 'filament.widgets.order-status';

    protected function getViewData(): array
    {
        $endDate = Carbon::parse($this->pageFilters['end_date'] ?? now())->toDateString();
        $startDate = Carbon::parse(
            $this->pageFilters['start_date'] ?? Carbon::parse($endDate)->subDays(6),
        )->toDateString();
        $productId = $this->pageFilters['product_id'] ?? null;

        $orders = Order::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($productId) {
            $orders->whereIn('id', OrderItems::query()
                ->whereHas('productVariant', fn (Builder $query) => $query->where('product_id', $productId))
                ->select('order_id'));
        }

        $counts = $orders
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $statuses = [
            ['label' => 'Completed', 'count' => (int) ($counts['paid'] ?? 0), 'color' => '#2eaf83', 'dot' => 'bg-[#2eaf83]'],
            ['label' => 'Pending', 'count' => (int) ($counts['pending'] ?? 0), 'color' => '#ffa62b', 'dot' => 'bg-[#ffa62b]'],
            ['label' => 'Cancelled', 'count' => (int) ($counts['expired'] ?? 0), 'color' => '#ef4444', 'dot' => 'bg-[#ef4444]'],
            ['label' => 'Processing', 'count' => (int) ($counts['partial'] ?? 0), 'color' => '#8993a7', 'dot' => 'bg-[#8993a7]'],
        ];

        $total = array_sum(array_column($statuses, 'count'));
        $offset = 0;

        foreach ($statuses as &$status) {
            $status['percentage'] = $total ? $status['count'] / $total * 100 : 0;
            $status['segment'] = max(0, $status['percentage'] - 0.6);
            $status['offset'] = -$offset;
            $offset += $status['percentage'];
        }

        return compact('statuses', 'total');
    }
}
