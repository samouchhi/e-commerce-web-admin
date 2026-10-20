<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItems;
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
        $productId = $this->pageFilters['product_id'] ?? null;

        $orders = Order::query();

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
            ['label' => 'Paid', 'count' => (int) ($counts['paid'] ?? 0), 'color' => '#71886a', 'dot' => 'bg-[#71886a]'],
            ['label' => 'Pending', 'count' => (int) ($counts['pending'] ?? 0), 'color' => '#c09a58', 'dot' => 'bg-[#c09a58]'],
            ['label' => 'Expired', 'count' => (int) ($counts['expired'] ?? 0), 'color' => '#b87970', 'dot' => 'bg-[#b87970]'],
            ['label' => 'Partial', 'count' => (int) ($counts['partial'] ?? 0), 'color' => '#8993a7', 'dot' => 'bg-[#8993a7]'],
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
